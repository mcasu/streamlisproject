<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$app_name = $_POST['app'];
$nginx_id = $_POST['clientid'];
$stream_name = $_POST['name'];
$client_addr = $_POST['addr'];

if (!$dbactions->OnPublishDone($nginx_id,$app_name,$stream_name,$client_addr))
{
    error_log("ERROR OnPublishDone failed: ".$dbactions->GetErrorMessage());    
}

?>
