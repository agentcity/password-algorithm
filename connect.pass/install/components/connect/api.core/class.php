<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class ApiException extends Exception {}

abstract class CEsetApiCore extends CBitrixComponent {

    protected $arInput = Array();
    protected $serviceName = false;
    protected $internalCall = false;

	protected $returnCode = 0;
    protected $returnDescription = "";
	protected $returnData = false;
    private $executeTime = 0;

    protected $arErrors = Array(
                        1 => "Не заполнены обязательные поля",
                        20 => "Не указан тип авторизации",
                        21 => "Неверный тип авторизации",
                        500 => "Доступ запрещен",
                        999 => "Непредвиденная ошибка"
                    );

    /**
     * execute
     * Метод, реализующий сервис в потомках
     *
     */
    abstract function execute();

    /**
     * __construct
     * Подключает константы и служебные классы
     *
     */
    public function __construct() {
        require_once(__DIR__ . "/include/include.php");
    }


    /**
     * onPrepareComponentParams
     * Подготавливает и возвращает массив параметров компонента
     * Записывает входные параметры в переменную класса $arInput
     * Запоминает код сервиса
     * Определяет и обрабатывает внутренний вызов апи
     *
     * @param array $arParams массив параметров компонента
     * @return array $arParams измененный массив параметров компонента
     */
    public final function onPrepareComponentParams($arParams) {
        $this->arInput = $_GET + $_POST;

        if (!empty($arParams["PARENT_SERVICE"]) && is_object($arParams["PARENT_SERVICE"]) && ($arParams["PARENT_SERVICE"] instanceof CEsetApiCore)) {
            $this->internalCall = true;
            $this->arInput = $arParams["PARAMS"];
        }

        return parent::onPrepareComponentParams($arParams);
    }


    /**
     * executeComponent
     * Переопределение стандартного component.php
     * Обертка над функцией execute потомка
     * При необходмости в будущем метода в потомках, убрать final
     *
     * @return string JSON-массив с результатом $this->arResult
     */
    public final function executeComponent() {
        $this->executeTime = -microtime(true);
        $this->arResult = array();

        try {
            if(!($this->serviceName))
                throw new ApiException(null, 999);
            $this->execute();
        } catch (ApiException $e) {
            $this->returnCode = $e->getCode();
        } catch (Exception $e) {
            $this->returnCode = 999;
        }

        $this->executeTime += microtime(true);

        return $this->response();
    }


    /**
     * checkFields
     * Проверяет наличие и заполненность обязательных полей запроса
     *
     * @param array $arReqFileds массив, содержащий список индексов, которые должен содержать массив входных параметров $this->arInput
     * @return bool true в случае успешной проверки
     * @throws ApiException
     */
    protected function checkFields($arReqFileds) {
        foreach($arReqFileds as $field)
            if(!isset($this->arInput[$field]) || empty($this->arInput[$field]))
                throw new ApiException(null, 1);
        return true;
    }


    /**
     * checkAuth
     * Проверка авторизации на основе значения параметра запроса $this->arInput["auth"]
     * Использует CheckAuthManager для создания экземпляра класса проверки - потомка от CheckAuth.
     *
     * @return bool результат проверки
     */
    protected function checkAuth() {
        if($this->internalCall)
            return true;
        $obChecker = new CheckAuthManager($this->arInput["auth"]);
        return $obChecker->
                    getChecker()->
                    setParams($this->arInput, $this->serviceName, $this->arInput["auth"])->
                    checkFields()->
                    check();
    }


    /**
     * response
     * формирование json-ответа на основе кода возврата ($this->returnCode) и данных $this->arResult
     *
     * @return str json-ответ
     */
    protected function response() {
        $data = $this->arResult;

        $arJson = array();
        if (intval($this->returnCode) == 0) {
            $arJson = Array("success" => true, "code" => $this->returnCode, "description" => "", "data" => $data);
        } else {
            $arJson = Array("success" => false, "error" => Array("code" => $this->returnCode, "description" => $this->getErrorDesc($this->returnCode)), "data" => $this->returnData);
        }

        // hardcore log
        if(!is_dir(constant("API_LOG_PATH")))
            @mkdir(constant("API_LOG_PATH"), 0775, true);
        TextLog::Write(array("filename"=>constant("API_LOG_PATH")."/" . date("Y-m-d") . ".log"), "~Method", $this->serviceName, "~Input", $this->arInput, "~Result", $arJson, "~Execution time", $this->executeTime, "~Internal call", $this->internalCall);

        $error_code = $arJson["success"] ? 0 : intval($arJson["error"]["code"]);
        DBLog::Write(
            array(
                "service" => $this->serviceName,
                "ip" => $_SERVER["REMOTE_ADDR"],
                "params" => json_encode($this->arInput),
                "execution_time" => $this->executeTime,
                "request_uri" => \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestedPage(),
                "error_code" => $error_code
            ),
            "~Input", $this->arInput,
            "~Result", $arJson,
            "~Execution time", $this->executeTime,
            "~Internal call", $this->internalCall
        );
        // log

        return json_encode($arJson);
    }


    /**
     * getErrorDesc
     * Возвращает текст ошибки по коду или из переменной объекта $this->returnDescription
     *
     * @param int $errCode код ошибки
     * @return str текст ошибки
     */
    private function getErrorDesc($errCode) {
        if(strlen($this->returnDescription))
            return $this->returnDescription;
        elseif(!empty($this->arErrors[$errCode]))
            return $this->arErrors[$errCode];
        else
            return "";
    }


    /**
     * addErrors
     * Добавляет описание ошибок сервиса к массиву общих ошибок api
     *
     * @param array $arServiceErrors ошибки сервиса
     */
    protected function addErrors($arServiceErrors) {
        $this->arErrors = $this->arErrors + $arServiceErrors;
    }

}
