<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();
$fsactions = $fgmembersite->GetFSActionsInstance();

$app_name = $_POST['app'];
$nginx_id = $_POST['clientid'];
$stream_name = $_POST['name'];
$client_addr = $_POST['addr'];
$record_path = $_POST['path'];

$ondemand_path="/var/stream/".$stream_name."/";

$path_parts = pathinfo($record_path);
$ondemand_filename = $path_parts['basename'];


if ($fsactions->OnRecordDone($nginx_id,$ondemand_path,$client_addr,$record_path))
{
	$movie = new ffmpeg_movie($ondemand_path.$ondemand_filename, false);
	$dbactions->OnRecordDone($app_name,$stream_name,$ondemand_path,$ondemand_filename,$movie);
}

?>
