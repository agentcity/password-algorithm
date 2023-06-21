<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !==  true) die();
$data = array(
    "auth"      => "hashopenid",
    "login"     => $arParams["LOGIN"],
    "password"     => $arParams["PASSWORD"],
);

\Bitrix\Main\Loader::IncludeModule("connect.general");
$query = new \Eset\General\ApiQuery($data, COption::GetOptionString("connect", "OPENID_SERVER_SECRET_KEY"), false, "connect");

$result = $query->getPost("/api/profile/check_password/");

if($result["success"]) {
    return true;
}else{
    return false;
}
?>
