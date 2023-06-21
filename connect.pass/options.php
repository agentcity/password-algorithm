<h1>Настройка модуля CONNECT</h1>
<?
$module_id = "connect.pass";
CModule::IncludeModule('connect.pass');

require_once($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$module_id.'/include.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$module_id.'/options.php');

$showRightsTab = true;

$arTabs = array(
   array(
      'DIV' => 'edit1',
      'TAB' => 'Настройки',
      'ICON' => '',
      'TITLE' => 'Настройки'
   ),
);


$arGroups = array(
   'MAIN' => array('TITLE' => 'Системные настройки', 'TAB' => 0)
);

$arOptions = array(
   'OPENID_SERVER_SECRET_KEY' => array(
      'GROUP' => 'MAIN',
      'TITLE' => 'Секретная фраза',
      'TYPE' => 'STRING',
      'DEFAULT' => '',
      'SORT' => '0',
      'NOTES' => 'Для работы с API CONNECT нужно получить секретную фразу'
   ),
   'SITE_ENCODING' => array(
      'GROUP' => 'MAIN',
      'TITLE' => 'Кодировка сайта',
      'TYPE' => 'SELECT',
      'VALUES' => array('REFERENCE_ID' => array("UTF-8", "CP-1251"), 'REFERENCE' => array("UTF-8", "CP-1251")),
      'SORT' => '1',
      'NOTES' => 'Для правильной работы CONNECT, с симовалами кириллицы, нужно указать кодировку сайта'
   )
);


/*
Конструктор класса CModuleOptions
$module_id - ID модуля
$arTabs - массив вкладок с параметрами
$arGroups - массив групп параметров
$arOptions - собственно сам массив, содержащий параметры
$showRightsTab - определяет надо ли показывать вкладку с настройками прав доступа к модулю ( true / false )
*/

$opt = new CModuleOptions($module_id, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();

?>
