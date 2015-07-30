<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$userIsLogged = $mainactions->CheckLogin();

if(!$userIsLogged)
{
    $utils->RedirectToURL("/login.php");
}
else
{      
     $user_role = $mainactions->GetSessionUserRole();
     error_log("INFO - User logged->[" . $mainactions->UserName() . "] ROLE->[" . $user_role . "]");
     if (!empty($user_role))
     {
         switch ($user_role) 
         {
             case "1": // admin
                 $utils->RedirectToURL("admin/dashboard.php");
                 break;
             case "2": // normal
                 $utils->RedirectToURL("viewer/live-normal.php");
                 break;
             case "3": // publisher
                 $utils->RedirectToURL("publisher/dashboard.php");
                 break;
             default:
                 break;
         }
     }
}
