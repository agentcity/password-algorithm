<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 *
 */
class ConnectServerPasswordHashAlgorithmException extends ConnectErrorException {}

class ConnectServerPasswordHashAlgorithm extends CBitrixComponent {


    /**
     * Массив с существующими методами в компоненте
     * @param array $methods
     */
    private $methods = array(
                    "checkHash",
                    "setHash",
                );

    private $arErrors = Array();

    private $saltUsersSalt = "tleGvKAUEXPHnXQn6ajR";

    /**
     * Массив с перечнем ошибок внутри данного класса
     * @param array $arDefaultErrors
     */
    private $arDefaultErrors = Array(
                    100 => "NET_ZAPRASHIVAEMOGO_METODA",
                    102 => "NE_ZADAN_METOD",
                    103 => "NE_ZADANY_ARGUMENTY_METODA",
                    110 => "Входящий параметр login не должен быть пустым",
                    111 => "Входящий параметр password не должен быть пустым",
                    112 => "Неверно введен пароль",
                    113 => "Ошибка при создании соли ",
               );

    /**
     * Метод executeComponent()
     *
     */
    public function executeComponent() {
        try{
            if(!$this->arParams["METHOD"]){
                throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[102], 102);
            }
            if(!$this->arParams["ARGS"]){
                throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[103], 103);
            }
            if(in_array($this->arParams["METHOD"], $this->methods)){
                require_once(__DIR__ . "/include/include.php");

                if(empty($this->arParams["ARGS"]["login"])){
                    throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[110], 110);
                }
                if(empty($this->arParams["ARGS"]["password"])){
                    throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[111], 111);
                }
                return $this->{$this->arParams["METHOD"]}($this->arParams["ARGS"]);
            }else{
                throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[100], 100);
            }
        }catch(Exception $e){

        }
    }


    private function createHash($login, $password){
        $hash = $this->generateHash($password, $login);
        $obTableHash = new ConnectServerPasswordTableHash();
        if($obTableHash->setPasswordHash($hash)){
            return true;
        }
        return false;
    }

    private function generateHash($password, $login){

        $randomPasswordSalt = randString(8);
        $encodingPassword = md5($randomPasswordSalt.$password);

        $obUserSalt = new ConnectServerPasswordUserSalt();
        if(!$usersSalt = $obUserSalt->setSalt($login, $randomPasswordSalt)){
            throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[113], 113);
        }
        $hashUsersSalt = sha1($this->saltUsersSalt.md5($login).$usersSalt);
        $hash = hash('sha256', $randomPasswordSalt.$encodingPassword.$hashUsersSalt);
        return $hash;
    }


    private function checkHash($arParams){

        $login = $arParams["login"];
        $password =  $arParams["password"];

        $obUserSalt = new ConnectServerPasswordUserSalt();
        if(!$usersSalt = $obUserSalt->getSalt($login)){
            throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[113], 113);
        }

        if(strlen($usersSalt) > 32){
            $randomPasswordSalt = substr($usersSalt, 0, strlen($usersSalt) - 32);
        }else{
            $randomPasswordSalt = "";
        }

        $hashUsersSalt = sha1($this->saltUsersSalt.md5($login).$usersSalt);
        $encodingPassword = md5($randomPasswordSalt.$password);
        $hash = hash('sha256', $randomPasswordSalt.$encodingPassword.$hashUsersSalt);

        $obTableHash = new ConnectServerPasswordTableHash();
        if($obTableHash->getPasswordHash($hash)){
            return true;
        }else{
            throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[112], 112);
        }

        return false;
    }


    /**
     * setHash($arParams)
     * Метод для
     * @param  array $arParams
     * @return array $response
     *
     */

    private function setHash($arParams){
        if(empty($arParams["login"])){
            throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[110], 110);
        }
        if(empty($arParams["password"])){
            throw new ConnectServerPasswordHashAlgorithmException($this->arDefaultErrors[111], 111);
        }
        if($this->createHash($arParams["login"], $arParams["password"])){

            return true;
        }

        return false;

    }

    private function pp($array){
        echo"<pre>";
        print_r($array);
        echo"<pre>";
    }

}
