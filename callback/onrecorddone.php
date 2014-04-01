<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();
$fsactions = $mainactions->GetFSActionsInstance();

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
$ondemand_basename = $path_parts['basename'];
$ondemand_filename = $path_parts['filename'];


if ($fsactions->SaveOnDemandVideoToDisk($nginx_id,$ondemand_path,$client_addr,$record_path,$stream_name))
{
	$movie = new ffmpeg_movie($ondemand_path.$stream_name."/".$ondemand_basename, false);
	
	/*** CREATE VIDEO THUMBNAIL ***/
	// Get video thumbnail from 20000sec frame.
	$frame = $movie->getFrame($movie->getFrameRate() * 20000);
	
	if (!$frame)
	{
		// Get video thumbnail from 5sec frame.
		$frame = $movie->getFrame($movie->getFrameRate() * 5);	
	}
	
	if ($frame)
	{
		//$frame->resize(320, 240);
		$image = $frame->toGDImage();
		// Save the image to disk
		$img_filename = $ondemand_path.$stream_name."/".$ondemand_filename.'.jpg';
		
		if (imagejpeg($image, $img_filename, 100))
		{
			if (!symlink($img_filename, "/usr/local/nginx/html/images/thumbnails/".$ondemand_filename.'.jpg'))
			{
				error_log("Creating thumbnail symbolic link FAILED. Phisical file: ".$img_filename);
			}
		}
		else
		{
			
		}
	}
	
	if (!$dbactions->OnRecordDone($app_name,$stream_name,$ondemand_path.$stream_name."/",$ondemand_basename,$movie))
	{
		error_log("Recording the stream ".$stream_name." FAILED! ".$dbactions->GetErrorMessage());
	}
}

?>
