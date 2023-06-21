<?php
class ConnectServerPasswordTableHashException extends Exception {}

class ConnectServerPasswordTableHash
{

    protected $DB = "";

    protected $tableName = "connect_hash_password";


    protected $arErrors = array(
        2001 => "Нет такой таблицы соответствия хешей",
        2002 => "Хэш не найден",
        2003 => "По технической причине не удалось сохранить Хеш в таблице",
    );

    public function __construct(){
        $this->connectDB();
    }

    public function setPasswordHash($hash){
        try{
            $this->savePasswordHashinDB($hash);
            return true;
        }catch(ConnectServerPasswordTableHashException $e){

        }
        return false;
    }

    public function getPasswordHash($hash){
        try{
            $this->selectHashinDB($hash);
            return true;
        }catch(ConnectServerPasswordTableHashException $e){
            return false;
        }
        return false;
    }

    private function savePasswordHashinDB($hash){
       $this->DB->Query("
            insert into {$this->tableName}
            (`hash`) values
            ('{$this->DB->ForSql($hash)}')
        ", true);
        return true;
    }

    private function selectHashinDB($hash){

        $arHash = $this->DB->Query("SELECT * FROM {$this->tableName} WHERE `hash` = '{$this->DB->ForSql($hash)}' LIMIT 1", true)->Fetch();
        if(!$arHash) {
            throw new ConnectServerPasswordTableHashException(null, 2002);
        }
        return $arHash["hash"];
    }

    private function connectDB(){
        global $DB;
        $this->DB = $DB;
    }

    private function pp($array){
        echo"<pre>";
        print_r($array);
        echo"<pre>";
    }
}

?>
