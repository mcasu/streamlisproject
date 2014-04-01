<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$app_name = $_POST['app'];
$nginx_id = $_POST['clientid'];
$stream_name = $_POST['name'];
$client_addr = $_POST['addr'];
if(isset($_POST['code'])) 
{
	$publish_code = $_POST['code'];
}
else
{
	$publish_code = "0000";
}

$mysqldate = date("Y-m-d"); 
$mysqltime = date("H:i:s"); 

if (!$dbactions->OnPublish($nginx_id,$app_name,$stream_name,$client_addr,$stream_name,$mysqldate,$mysqltime))
{
	error_log("Publishing the stream ".$stream_name." FAILED! ".$dbactions->GetErrorMessage());
}

?>
