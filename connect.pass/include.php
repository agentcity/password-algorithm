<?php
CModule::IncludeModule("connect.pass");
global $DBType;

$arClasses=array(
    'cMainEsetConnect'=>'classes/general/cMainConnect.php',
    'CModuleOptions'=>'classes/general/CModuleOptions.php'
);

CModule::AddAutoloadClasses("connect.pass",$arClasses);
