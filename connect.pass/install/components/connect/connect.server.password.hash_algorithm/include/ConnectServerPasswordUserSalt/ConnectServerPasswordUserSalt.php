<?php

class ConnectServerPasswordUserSaltException extends Exception {}

class ConnectServerPasswordUserSalt
{

    private $aplication = "";

    private $iblockId = "";

    private $mainUsersSectionID = "";

    /**
     * Массив с перечнем секции для исключения поиска по ним элементов
     * 6 - Секция для пользоватей старого клуба
     * @param array $arDefaultErrors
     */
    private $exludeSection = array(
        6,
    );

    private $alfa = "avab5g7T3m1ddhI12u2B";

    private $arErrors = array(
        3001 => "Не удалось подключить модуль для работы с инфоблоком",
        3002 => "Учетная запись с указанным логином не найдена",
        2003 => "По технической причине не удалось сохранить Хеш в таблице",
    );

    public function __construct(){
        global $APLICATION;
        if (!CModule::IncludeModule("iblock")){
            throw new ConnectServerPasswordUserSaltException(null, 3001);
        }
        $this->aplication = $APLICATION;
        $this->iblickId = IBLOCK_ID_USER;
        $this->mainUsersSectionID = SECTION_GENERAL;

    }



    public function setSalt($login, $randomPasswordSalt){
        if(!$userId = $this->checkLoginExistIB($login)){
            throw new ConnectServerPasswordUserSaltException(null, 3002);
        }

        $salt = $this->generateSalt($login, $randomPasswordSalt);

        if($this->saveSalt($salt, $userId)){
            return $salt;
        }
        return false;

    }

    public function getSalt($login){
        if($salt = $this->getLoginSaltIB($login)){
            return $salt;
        }
        return false;
    }


    private function saveSalt($salt, $userID){
        CIBlockElement::SetPropertyValues($userID, $this->iblickId, $salt, "PASSWORD");
        return true;
    }

    private function generateSalt($login, $randomPasswordSalt){
        $salt = $randomPasswordSalt.md5($randomPasswordSalt.$login.$this->alfa);
        return $salt;
    }

    private function checkLoginExistIB($login){
        $arSelect = Array("ID", "NAME");
        $arFilterLogin = Array("IBLOCK_ID"=> $this->iblickId, "=NAME" =>$login, "!SECTION_ID"=>$this->exludeSection);
        $rsUserLogin = CIBlockElement::GetList(Array("CREATED"=>"DESC"), $arFilterLogin, false, Array("nTopCount" => 1), $arSelect);
        if ($arUserLogin = $rsUserLogin->GetNext()){
            return $arUserLogin["ID"];
        }
        return false;
   }

    private function getLoginSaltIB($login){
        $arSelect = Array("ID", "NAME", "PROPERTY_PASSWORD");
        $arFilterLogin = Array("IBLOCK_ID"=> $this->iblickId, "=NAME" =>$login, "!SECTION_ID"=>$this->exludeSection);
        $rsUserLogin = CIBlockElement::GetList(Array("CREATED"=>"DESC"), $arFilterLogin, false, Array("nTopCount" => 1), $arSelect);
        if ($arUserLogin = $rsUserLogin->GetNext()){
            return $arUserLogin["PROPERTY_PASSWORD_VALUE"];
        }
        return false;
   }

    private function pp($array){
        echo"<pre>";
        print_r($array);
        echo"<pre>";
    }

}

?>
