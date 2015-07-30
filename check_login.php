<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$userIsLogged = $mainactions->CheckLogin();

if(!$userIsLogged)
{
    $utils->RedirectToURL("/login.php");
}
