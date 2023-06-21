<?
class CheckAuthManager {
    private $authType = "";

    /**
     * __construct
     * Запоминает тип авторизации в переменной объекта $this->authType
     *
     * @param str $authType тип авторизации
     */
    public function __construct($authType) {
        $this->authType = $authType;
    }


    /**
     * getChecker
     * Проверяет тип авторизации и возвращает объект типа CheckAuth
     *
     * @return object потомок CheckAuth
     * @throws ApiException
     */
    public function getChecker() {
        if(!strlen($this->authType))
            throw new ApiException(null, 20);
        switch ($this->authType) {
            case "hash":
                return new CheckAuthIBHash();
            case "password":
                return new CheckAuthIBPassword();
            case "hashopenid":
                return new CheckAuthHashOpenID();
            default:
                throw new ApiException(null, 21);
        }
        throw new ApiException(null, 21);
    }
}




abstract class CheckAuth {
    protected $arInput = Array();
    protected $serviceName = "";
    protected $authType = "";

    public function setParams($arInput, $serviceName, $authType) {
        $this->arInput = $arInput;
        $this->serviceName = $serviceName;
        $this->authType = $authType;
        return $this;
    }


    protected function getIBRightData() {
        if(strlen($this->serviceName)) {

            $arAuthElementProps = false;

            // кэш
            $obCache = new CPHPCache;
            $life_time = 86400;
            $cache_id = "CheckAuthIB".$this->serviceName.$this->authType;
            if($obCache->InitCache($life_time, $cache_id, "/")) {
                $arAuthElementProps = $obCache->GetVars();
            }
            elseif ($obCache->StartDataCache()) {
                CModule::IncludeModule("iblock");

                $rsH = CIBlockElement::GetList(false, Array("=IBLOCK_CODE"=>"rights", "ACTIVE"=>"Y", "=PROPERTY_SERVICE"=>$this->serviceName, "=PROPERTY_AUTH_TYPE_VALUE"=>$this->authType), false, false);
                if($obH = $rsH->GetNextElement())
                    $arAuthElementProps = $obH->GetProperties();
                else
                    return false;
                $obCache->EndDataCache($arAuthElementProps);
            }
            //

            return $arAuthElementProps;
        }
        return false;
    }


    protected function checkIP($ip = "") {
        // для девелоперских машин ip адрес не проверяется
        if (getenv("APPLICATION_ENV") == "development")
            return true;

        if ($ip=="*")
            return true;

        // строим массив ip-адресов
        $arIP = array();
        if (strpos($ip, "-") !== false) {
            if (preg_match("#(.*?\.)(\d{1,3})-(\d{1,3})#", $ip, $arMatches) && (intval($arMatches[2]) < intval($arMatches[3])))
                for ($i=intval($arMatches[2]); $i < intval($arMatches[3]); $i++)
                    $arIP[] = $arMatches[1] . $i;
        } else
            $arIP = array($ip);

        if (in_array($_SERVER["REMOTE_ADDR"], $arIP))
            return true;

        return false;
    }


    abstract function check();
    abstract function checkFields();
}


class CheckAuthIBHash extends CheckAuth {
    private $arRightsData;

    public function checkFields() {
        if (!isset($this->arInput["hash"]) || empty($this->arInput["hash"]))
            throw new ApiException(null, 1);
        return $this;
    }

    public function check() {
        $this->arRightsData = $this->getIBRightData();

        $arInputParams = $this->arInput;
        ksort($arInputParams);
        unset($arInputParams["hash"]);

        if(is_array($this->arRightsData)) {
            foreach($this->arRightsData["AUTH_DATA"]["VALUE"] as $rightStr) {
                list($ip,$salt) = array_pad(explode("|",$rightStr,2), 2, "");
                if($this->checkIP($ip) && strlen($salt)) {
                    if(sha1(implode("", $arInputParams).$salt)===$this->arInput["hash"])
                        return true;
                }
            }
        }
        throw new ApiException(null, 500);
    }
}


class CheckAuthIBPassword extends CheckAuth {
    private $arRightsData;

    public function checkFields() {
        if (!isset($this->arInput["login"]) || empty($this->arInput["login"]) || !isset($this->arInput["password"]) || empty($this->arInput["password"]))
            throw new ApiException(null, 1);
        return $this;
    }

    public function check() {
        $this->arRightsData = $this->getIBRightData();

        if(is_array($this->arRightsData)) {
            foreach($this->arRightsData["AUTH_DATA"]["VALUE"] as $rightStr) {
                list($ip,$login,$password) = array_pad(explode("|",$rightStr,3), 3, randString(10)); // если в инфоблоке нет пароля, задаем его случайной строкой
                if(
                    $this->checkIP($ip) && (
                        ($this->arInput["login"]===$login) &&
                        ($this->arInput["password"]===$password)
                    )
                )
                    return true;
            }
        }
        throw new ApiException(null, 500);
    }
}


/* сделам авторизацию под OpenID, ip адрес будем брать из инфоблока Доверенных сайтов*/

class CheckAuthHashOpenID extends CheckAuth {
    private $arRightsData;

    protected function getIBRightData() {
        if(strlen($this->serviceName)) {
            $arAuthElementProps = false;
            //запросов должно быть мало, поэтому незачем кешировать
            CModule::IncludeModule("iblock");
            $arSelect = Array("ID", "NAME", "PROPERTY_IP");
            $arFilter = Array("IBLOCK_ID"=>IBLOCK_ID_TRUST_URL, "NAME" => "http://".$_SERVER["HTTP_HOST"]."/",  "ACTIVE"=>"Y");
            $rsH = CIBlockElement::GetList(Array(), $arFilter, false, Array("nTopCount" => 1), $arSelect);
            if($obH = $rsH->GetNext()){
                $arAuthElementProps = $obH;
            } else {
                return false;
            }
            return $arAuthElementProps;
        }
        return false;
    }


    public function checkFields() {
        if (!isset($this->arInput["hash"]) || empty($this->arInput["hash"]))
            throw new ApiException(null, 1);

        return $this;
    }

    public function check() {
        if(strripos($_SERVER["HTTP_HOST"], "dev.eset.local"))
            return true;

        $this->arRightsData = $this->getIBRightData();
        $arInputParams = $this->arInput;
        ksort($this->arInputParams);
        unset($arInputParams["hash"]);
        if(is_array($this->arRightsData)) {
            $ip = $this->arRightsData["PROPERTY_IP_VALUE"];
            /*HASH объявляется в константах*/
            $salt = HASH;
            if($this->checkIP($ip) && strlen($salt)) {
                if(sha1(implode("", $arInputParams).$salt)===$this->arInput["hash"])
                    return true;
            }
        }
        throw new ApiException(null, 500);
    }
}
