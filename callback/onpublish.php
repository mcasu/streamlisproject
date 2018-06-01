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

if(isset($_POST['code'])) 
{
	$publish_code = $_POST['code'];
}
else
{
	$publish_code = "0000";
}

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


if (!$dbactions->PublishCodeExists($stream_name))
{
    error_log("PUBLISHING DENIED! Publish code [".strtolower($stream_name)."] not exists.\n" . $dbactions->GetErrorMessage());
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Stream name non valido.</h1><br/>";
    exit;
}

/*** Save live publish info into database ***/
if (!$dbactions->OnPublish($nginx_id,$app_name,$stream_name,$client_addr,$stream_name,$mysqldate,$mysqltime))
{
	error_log("Publishing the stream ".$stream_name." FAILED! ".$dbactions->GetErrorMessage());
	exit;
}

/*** Save publish event into database ***/
if (!$dbactions->SaveEventoDb($nginx_id,$mysqldate,$mysqltime,$event_call,$app_name,strtolower($stream_name),$client_addr,$flash_ver,$page_url))
{
	error_log("Saving PUBLISH event to the database ".strtolower($stream_name)." FAILED! ".$dbactions->GetErrorMessage());
	exit;
}
