<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("/login.php");
    exit;
}

//$user_role = $mainactions->GetSessionUserRole();
//if (empty($user_role) || $user_role != "1" || $user_role != "2" || $user_role != "3")
//{
//    $utils->RedirectToURL("/login.php");
//}
