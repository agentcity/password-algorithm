<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/eset.connect/include.php"); // инициализация модуля

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("eset.connect");

// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

// установим заголовок страницы
$APPLICATION->SetTitle(("Смена типа авторизации на ESET CONNECT"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
?>
<?
// конфигурация административного меню
$aMenu = array(
    array(
    "TEXT"=>"Изменить тип авторизации пользователей на ESET CONNECT",
    "TITLE"=>"Изменить тип авторизации пользователей на ESET CONNECT",
    "LINK"=>"esetChangeAuthToOpenID.php?changeAuthToOpeonId=true&lang=".LANG,
    "ICON"=>"btn_list",
    ),
    array(
    "TEXT"=>"Изменить тип авторизации пользователей на Bitrix",
    "TITLE"=>"Изменить тип авторизации пользователей на Bitrix",
    "LINK"=>"esetChangeAuthToOpenID.php?changeAuthToBitrix=true&lang=".LANG,
    "ICON"=>"btn",
    )
);
// создание экземпляра класса административного меню
$context = new CAdminContextMenu($aMenu);
// вывод административного меню
$context->Show();
?>
<?
if ($_REQUEST["changeAuthToOpeonId"] == true) {
    $APPLICATION->IncludeComponent("eset:connect.client.users.change_type_authorize", "", Array(), false, false);
}
if ($_REQUEST["changeAuthToBitrix"] == true) {
    $APPLICATION->IncludeComponent("eset:connect.client.users.change_type_authorize", "", Array("changeAuthToBitrix"=>true), false, false);
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
