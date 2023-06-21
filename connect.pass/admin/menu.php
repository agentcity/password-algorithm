<?
IncludeModuleLangFile(__FILE__); // в menu.php точно так же можно использовать языковые файлы
if($APPLICATION->GetGroupRight("connect.pass")>"D") // проверка уровня доступа к модулю веб-форм
{
    // сформируем верхний пункт меню
    $aMenu = array(
        "parent_menu" => "global_menu_services", // поместим в раздел "Сервис"
        "sort"        => 50,                    // вес пункта меню
        "text"        => "Connect",       // текст пункта меню
        "title"       => "Connect", // текст всплывающей подсказки
        "icon"        => "form_menu_icon", // малая иконка
        "page_icon"   => "form_page_icon", // большая иконка
        "items_id"    => "menu_connect",  // идентификатор ветви
        "items"       => array(),          // остальные уровни меню сформируем ниже.
    );

    // далее выберем список веб-форм и добавим для каждой соответствующий пункт меню
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/connect.pass/include.php");

    // массив каждого пункта формируется аналогично
    $aMenu["items"] =  array(
        array(
            "text" => "Cмена типа авторизации",
            "url"  => "ChangeAuthToOpenID.php?lang=".LANGUAGE_ID,
            "title" => "Cмена типа авторизации"
        ),
    );

    // если нам нужно добавить ещё пункты - точно так же добавляем элементы в массив $aMenu["items"]
    // ............

    // вернем полученный список
    return $aMenu;
}
// если нет доступа, вернем false
return false;
?>
