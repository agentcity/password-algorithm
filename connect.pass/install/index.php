<?
Class eset_connect extends CModule
{
    var $MODULE_ID = "connect.pass";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function eset_connect()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = GetMessage("ESET_CONNECT_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("ESET_CONNECT_MODULE_DESC");

        $this->PARTNER_NAME = GetMessage("ESET_CONNECT_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("ESET_CONNECT_PARTNER_URI");
    }


    function InstallFiles()
    {
        //Создаем симлинки
        if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/connect/"))
            mkdir($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/connect");

        $strComponentsDir = dirname(__FILE__)."/components/connect/";
        $arList = scandir($strComponentsDir);
        if(is_array($arList))
        {
            $arList = array_diff($arList, array('.', '..'));
            foreach($arList as $strFile)
                if(is_dir($strComponentsDir.$strFile))
                {
                    symlink($strComponentsDir.$strFile, $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/connect/".$strFile);
                }

        }

        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/connect.pass/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
        return true;
    }

    function UnInstallFiles($arParams = array())
    {
        //Удаляем симлинки
        $strComponentsDir = dirname(__FILE__)."/components/connect/";
        $arList = scandir($strComponentsDir);
        if(is_array($arList))
        {
            $arList = array_diff($arList, array('.', '..'));
            foreach($arList as $strFile)
                if(is_dir($strComponentsDir.$strFile))
                {
                    unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/connect/".$strFile);
                }
        }

        // Delete files
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/form/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        //DeleteDirFilesEx("/bitrix/themes/.default/icons/form/");//icons
        return true;
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        // Install events
        RegisterModule($this->MODULE_ID);
        RegisterModuleDependences("main", "OnProlog", "connect.pass", "cMainConnect", "OnPrologHandler");
        RegisterModuleDependences("main", "OnAfterUserAuthorize", "connect.pass", "cMainConnect", "OnAfterUserAuthorizeHandler");
        RegisterModuleDependences("main", "OnUserLogout", "connect.pass", "cMainConnect", "OnUserLogoutHandler");
        RegisterModuleDependences("main", "OnEpilog", "connect.pass", "cMainConnect", "OnEpilogHandler");
        $this->InstallFiles();
        $APPLICATION->IncludeAdminFile(GetMessage("CONNECT_INSTALL"), $DOCUMENT_ROOT."/local/modules/connect.pass/install/step.php");
        return true;
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        UnRegisterModuleDependences("main", "OnProlog", "connect.pass", "cMainEsetConnect", "OnPrologHandler");
        UnRegisterModuleDependences("main", "OnAfterUserAuthorize", "connect.pass", "cMainEsetConnect", "OnAfterUserAuthorizeHandler");
        UnRegisterModuleDependences("main", "OnUserLogout", "connect.pass", "cMainEsetConnect", "OnUserLogoutHandler");
        UnRegisterModuleDependences("main", "OnEpilog", "connect.pass", "cMainEsetConnect", "OnEpilogHandler");
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
        $APPLICATION->IncludeAdminFile(GetMessage("CONNECT_UN_INSTALL"), $DOCUMENT_ROOT."/local/modules/connect.pass/install/unstep.php");
        return true;
    }
}
