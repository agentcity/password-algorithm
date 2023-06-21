<?
    if(empty($_SERVER["DOCUMENT_ROOT"]))
        $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../..");

    include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    include("install/index.php");
    
    connect_pass::UnInstallFiles();
    connect_pass::InstallFiles();
?>
