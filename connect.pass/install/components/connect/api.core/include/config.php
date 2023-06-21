<?php
define("REGEXP_EMAIL_RAW", "[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[a-z]{2,15})");
define("REGEXP_EMAIL", "/^" . REGEXP_EMAIL_RAW . "$/i");
define("REGEXP_REGKEY_DEXTER_RAW", "^([R|K|Z][U|S|R|I|E|Z][0-9]{2}|Z[0-9]{2}R)-[A-Z0-9]{8,9}$|^[a-z0-9]{2}([a-z0-9]{2})?\-[a-z0-9]{4}\-[a-z0-9]{4}\-[a-z0-9]{4}\-[a-z0-9]{4}$");
define("REGEXP_REGKEY_DEXTER", "/" . REGEXP_REGKEY_DEXTER_RAW . "/i");
define("REGEXP_REGKEY_MANDARK_RAW", "^[A-Z]{3}(t|[0-9]{1})[0-9]{1}-[0-9a-z]{10}$");
define("REGEXP_REGKEY_MANDARK", "/" . REGEXP_REGKEY_MANDARK_RAW . "/i");
define("REGEXP_REGKEY_RAW", "(" . REGEXP_REGKEY_DEXTER_RAW . ")|(" . REGEXP_REGKEY_MANDARK_RAW . ")");
define("REGEXP_REGKEY", "/^" . REGEXP_REGKEY_RAW . "$/i");
define("REGEXP_DATE_RAW", "\d{1,2}\.\d{1,2}\.\d{4}");
define("REGEXP_DATE", "/^" . REGEXP_DATE_RAW . "$/i");
define("REGEXP_DEXTER_LICENCE_USERNAME_RAW", "^EAV-[0-9A-Za-z]{8,10}$");
define("REGEXP_DEXTER_LICENCE_USERNAME", "/" . REGEXP_DEXTER_LICENCE_USERNAME_RAW . "/i");
define("REGEXP_DEXTER_LICENCE_PASSWORD_RAW", "^[0-9A-Za-z]{10}$");
define("REGEXP_DEXTER_LICENCE_PASSWORD", "/" . REGEXP_DEXTER_LICENCE_PASSWORD_RAW . "/i");
define("REGEXP_USER_NAME_RAW", "[A-Za-zА-Яа-я ]+");
define("REGEXP_USER_NAME", "/" . REGEXP_USER_NAME_RAW . "/i");
define("REGEXP_MOBILE_RU_RAW", "((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}");
define("REGEXP_MOBILE_RU", "/^" . REGEXP_MOBILE_RU_RAW . "$/i");
define("REGEXP_MOBILE_BE_RAW", "(([\+]?375)[\- ]?)?(\(?\d{2}\)?[\- ]?)?[\d\- ]{7,9}");
define("REGEXP_MOBILE_BE", "/^" . REGEXP_MOBILE_BE_RAW . "$/i");
define("REGEXP_MOBILE", "/^" . REGEXP_MOBILE_RU_RAW . "|" . REGEXP_MOBILE_BE_RAW . "$/i");

define("TMP_DIR", $_SERVER["DOCUMENT_ROOT"]."/upload");

define("API_LOG_PATH", dirname($_SERVER["DOCUMENT_ROOT"]) . "/data/log/api");
