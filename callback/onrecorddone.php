<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();
$fsactions = $fgmembersite->GetFSActionsInstance();

$app_name = $_POST['app'];
$nginx_id = $_POST['clientid'];
$stream_name = $_POST['name'];
$client_addr = $_POST['addr'];
$record_path = $_POST['path'];

// HLS path
//$ondemand_path=$ondemand_hls_record_filepath;

// Flash path
$ondemand_path=$ondemand_flash_record_filepath;

$path_parts = pathinfo($record_path);
$ondemand_filename = $path_parts['basename'];


if ($fsactions->OnRecordDone($nginx_id,$ondemand_path,$client_addr,$record_path,$stream_name))
{
	$movie = new ffmpeg_movie($ondemand_path.$stream_name."/".$ondemand_filename, false);
	$dbactions->OnRecordDone($app_name,$stream_name,$ondemand_path.$stream_name."/",$ondemand_filename,$movie);
}

?>
