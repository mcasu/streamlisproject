<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$nginx_id = $_POST['clientid'];
$event_call = $_POST['call'];
$app_name = null;
$stream_name = null;
$client_addr = $_POST['addr'];
$flash_ver = null;
$page_url = null;


if(isset($_POST['app']))
{
	$app_name = $_POST['app'];
}

if(isset($_POST['name']))
{
	$stream_name = $_POST['name'];
}

if(isset($_POST['flashver']))
{
	$flash_ver = $_POST['flashver'];
}

if(isset($_POST['pageurl']))
{
	$page_url = $_POST['pageurl'];
}

$mysqldate = date("Y-m-d"); 
$mysqltime = date("H:i:s");

$username = null;
if ($mainactions->CheckLogin())
{
	$username = $mainactions->UserName();	
}

/*** Save play event into database ***/
if (!$dbactions->SaveEventoDb($nginx_id,$mysqldate,$mysqltime,$event_call,$app_name,strtolower($stream_name),$client_addr,$flash_ver,$page_url,$username))
{
	error_log("Saving PLAY event to the database ".strtolower($stream_name)." FAILED! ".$dbactions->GetErrorMessage());
	exit;
}

?>
