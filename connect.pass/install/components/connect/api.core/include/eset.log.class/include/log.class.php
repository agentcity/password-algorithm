<?php

abstract class LogCore {
    protected static $payload = false;

    protected static function toString($varValue, $varName) {
        $output = array();
        $varType = gettype($varValue);
        $varName = ($varName) ? $varName : "anonymous variable";


        switch($varType) {
            case "boolean":
                $varValue = ($varValue) ? "True" : "False";
                break;

            case "array":
            case "object":
                if (is_object($varValue)) {
                    $varValue = (array)$varValue;
                }
                $output = array();
                foreach ($varValue as $key => $value) {
                    $output[] = self::toString($value, $key);
                }
                $varValue = "[ " . implode(", ", $output) . " ]";
                break;

            case "integer":
            case "double":
            case "integer":
            case "string":
            case "resource":
            case "NULL":
            case "unknown type":
            default:
                $varValue = "\"" . (string)$varValue . "\"";
                break;
        }
        return $varName . ": (" . $varType. ") " . $varValue;
    }


    protected static function parsePayload($args) {
        self::$payload = array();
        $varName = false;
        for ($i = 1; $i < count($args); $i++) {
            if (is_string($args[$i]) && "~" == $args[$i][0]) {
                $varName = substr($args[$i], 1);
            } else {
                self::$payload[] = self::toString($args[$i], $varName);
                $varName = false;
            }
        }
    }

    abstract static function Write($arParams);

}



class TextLog extends LogCore {
    static $logFilename = false;

    private static function writeLog() {
        $ts = date("d.m.Y H:i:s ");
        $indent = str_repeat(" ", strlen($ts));
        foreach (self::$payload as $key => $value) {
            if ($key == 0) {
                self::$payload[$key] = $ts . $value;
            } else {
                self::$payload[$key] = $indent . $value;
            }
        }
        @file_put_contents(self::$logFilename, implode(chr(10), self::$payload) . chr(10), FILE_APPEND | LOCK_EX);
    }

    public static function Write($arParams) {
        self::$logFilename = $arParams["filename"];
        self::parsePayload(func_get_args());
        self::writeLog();
    }
}



class DBLog extends LogCore {
    private static $table_name = "eset_api_log";

    private static function writeLog($arParams) {
        // ограничение на длину результата для записи в лог DB
        $data = implode("<br />\r\n", self::$payload);
        if(strlen($data) > 3000)
            $data = substr($data, 0, 3000) . "...";
        global $DB;
        $DB->Query("
            insert into ".self::$table_name."
            (`service`, `ip`, `params`, `result`, `execution_time`, `request_uri`, `error_code`)
            values
            (
                '".$DB->ForSQL($arParams["service"])."',
                '".$DB->ForSQL($arParams["ip"])."',
                '".$DB->ForSQL($arParams["params"])."',
                '".$DB->ForSQL($data)."',
                '".$DB->ForSQL($arParams["execution_time"])."',
                '".$DB->ForSQL($arParams["request_uri"])."',
                '".$DB->ForSQL($arParams["error_code"])."'
            )
        ", true);
        // CEventLog::Log(false, "api (".self::$bResult.")", self::$moduleName, self::$serviceName, $data);
    }

    public static function Write($arParams) {
        self::parsePayload(func_get_args());
        self::writeLog($arParams);
    }
}





?>
