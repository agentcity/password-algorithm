<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
include($_SERVER["DOCUMENT_ROOT"]."/config/constants.php");
CBitrixComponent::IncludeComponentClass("eset:connect.server.api.core");
class CEsetApiProfileCheckPassword extends CEsetApiCore {
    protected $serviceName = "profile-check-password";
    private $arReqFields = Array("login", "password");
    private $arServiceError = Array(
                         1001 => "TEKHNICHESKAYA_OSHIBKA_NA_SERVERE",
                         1002 => "NET_TAKOGO_POLZOVATELYA",
                         1003 => "NEVERNYY_PAROL",
                    );
    public function execute() {
        $this->setServiceErrors($this->arServiceError, __DIR__);
        $this->checkFields($this->arReqFields);
        $this->checkAuth();
        $this->arResult = false;
        //получаем данные по запросу
        if (!CModule::IncludeModule("iblock")){
            throw new ApiException($this->arServiceError[1001], 1001);
        }
        $arSelect = Array("IBLOCK_ID", "ID", "NAME", "PROPERTY_PASSWORD");
        $arFilterLogin = Array("IBLOCK_ID"=>IBLOCK_ID_USER, "SECTION_ID"=>SECTION_GENERAL, "=NAME" => $this->arInput["login"], "ACTIVE"=>"Y");
        $rsUserLogin = CIBlockElement::GetList(Array(), $arFilterLogin, false, Array("nTopCount" => 1), $arSelect);
        if (!$arUser = $rsUserLogin->GetNext()){
            throw new ApiException($this->arServiceError[1002], 1002);
        }
        global $APPLICATION;
        if(!$APPLICATION->IncludeComponent("eset:connect.server.password.hash_algorithm", "", Array(
            "METHOD" => "checkHash",
            "ARGS" => Array(
                "login" => $this->arInput["login"],
                "password" => $this->arInput["password"],
            ),
        ), false, false)){
           throw new ApiException($this->arServiceError[1003], 1003);
        }
        // если регистрация через социальные сервисы
        if(!empty($this->arInput["external"]) && $this->arInput["external"] == true){
            $salt = sha1($this->arInput["login"].time());
            $hash = sha1($salt.$this->arInput["remote_addr"].$this->arInput["http_user_agent"]);
            $arEventFields["salt"] = $salt;
            $expirationInterval = 7; // 7 дней
            $expirationDate = 60*60*24*$expirationInterval+time();
            $expirationDateTimestamp = date("Y-m-d H:i:s", $expirationDate);
            global $DB;
            // добавляем информацию в базу
            $DB->Query("
                insert into `eset_shop_auth_users`
                (
                    `login`,
                    `hash`,
                    `expiration_date`
                ) values
                (
                    '".$DB->ForSql($this->arInput["login"])."',
                    '".$DB->ForSql($hash)."',
                    '".$DB->ForSql($expirationDateTimestamp)."'
                )
            ", true);
            $id = intval($DB->LastID());
            $this->arResult = array(
                "success" =>true,
                "salt" => $salt,
            );
        }else{
            $this->arResult = true;
        }
    }
    // ------

}
