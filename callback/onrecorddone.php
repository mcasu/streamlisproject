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
$record_tmp_dir = $path_parts['dirname'];

/*** SAVE FLV VIDEO TO DISK ***/
if (!$fsactions->SaveOnDemandVideoToDisk($nginx_id,$ondemand_path,$client_addr,$record_path,$stream_name))
{
	error_log("ERROR - Save video ".$record_path. " FAILED!");
	exit;	
}

/*** SAVE VIDEO INFO INTO DATABASE ***/
if (!$dbactions->OnRecordDone($app_name,$stream_name,$ondemand_path.$stream_name."/",$ondemand_basename,$movie))
{
	error_log("ERROR - Recording the stream ".$stream_name." FAILED! ".$dbactions->GetErrorMessage());
	exit;
}

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
		error_log("WARNING - Unable to create video thumbnail ".$img_filename);
	}
}

/*** CONVERT VIDEO TO .MP4 AND SAVE TO DISK ***/
if (!file_exists($ondemand_mp4_record_filepath.$stream_name))
{
	mkdir($ondemand_mp4_record_filepath.$stream_name, 0755, true);
	error_log("WARNING - Created folder ".$ondemand_mp4_record_filepath.$stream_name);
}

$output = shell_exec($_SERVER['DOCUMENT_ROOT'].'/scripts/convert_video.bash '.$ondemand_mp4_record_filepath.$stream_name.' '.$ondemand_filename.' '.$ondemand_path.$stream_name."/".$ondemand_basename);

$ondemand_mp4_fullpath = $ondemand_mp4_record_filepath.$stream_name."/";
if (!symlink($ondemand_mp4_fullpath.$ondemand_filename.".mp4", $ondemand_mp4_record_filepath.$ondemand_filename.".mp4"))
{
	error_log('ERROR - Creazione del link simbolico ['.$ondemand_mp4_record_filepath.$ondemand_filename.'.mp4] fallita!');
}

?>
