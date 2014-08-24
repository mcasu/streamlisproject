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

$mysqldate = date("Y-m-d");

// HLS path
//$ondemand_path=$ondemand_hls_record_filepath;

// Flash path
$ondemand_path=$ondemand_flash_record_filepath;

$path_parts = pathinfo($record_path);
$ondemand_basename = $path_parts['basename'];
$ondemand_filename = $path_parts['filename'];
$record_tmp_dir = $path_parts['dirname'];

$strtoremove_lenght = strlen($stream_name);
$ondemand_datetime = substr($ondemand_filename, $strtoremove_lenght + 1);

list($ondemand_onlydate, $ondemand_onlytime) = split("_", $ondemand_datetime);

$date_temp = strftime("%Y-%m-%d", strtotime($ondemand_onlydate));
//$time_temp = strftime("%H:%M:%S", strtotime(str_replace("-",":", $ondemand_onlytime)));

//error_log("INFO - ONDEMAND DATE: " . $date_temp);

/*** SAVE FLV VIDEO TO DISK ***/
if (!$fsactions->SaveOnDemandVideoToDisk($nginx_id,$ondemand_path,$client_addr,$record_path,$stream_name))
{
	error_log("ERROR - Save video ".$record_path. " FAILED!");
	exit;	
}

$movie = new ffmpeg_movie($ondemand_path.$stream_name."/".$ondemand_basename, false);

/*** SAVE VIDEO INFO INTO DATABASE ***/
if (!$dbactions->OnRecordDone($app_name,$stream_name,$ondemand_path.$stream_name."/",$ondemand_basename,$movie,$date_temp))
{
	error_log("ERROR - Recording the stream ".$stream_name." FAILED! ".$dbactions->GetErrorMessage());
	exit;
}

/*** CONVERT VIDEO TO .MP4 AND SAVE TO DISK ***/
if (!file_exists($ondemand_mp4_record_filepath.$stream_name))
{
	mkdir($ondemand_mp4_record_filepath.$stream_name, 0755, true);
	error_log("WARNING - Created folder ".$ondemand_mp4_record_filepath.$stream_name);
}

$output = shell_exec($_SERVER['DOCUMENT_ROOT'].'/scripts/convert_video.bash '.$ondemand_path.$stream_name."/".$ondemand_basename.' '.$ondemand_mp4_record_filepath.$stream_name.'/'.$ondemand_filename.'.mp4 '.$ondemand_basename);

$ondemand_mp4_fullpath = $ondemand_mp4_record_filepath.$stream_name."/";
if (!symlink($ondemand_mp4_fullpath.$ondemand_filename.".mp4", $ondemand_mp4_record_filepath.$ondemand_filename.".mp4"))
{
	error_log('ERROR - Creazione del link simbolico ['.$ondemand_mp4_record_filepath.$ondemand_filename.'.mp4] fallita!');
}

/*** CREATE VIDEO THUMBNAIL ***/
//Get video frame rate
$videorate = $movie->getFrameRate();
//Get video frame number
$framecount = $movie->getFrameCount();

// Get video thumbnail from 1000sec frame.
$frame = $movie->getFrame($videorate * 1000);

if (!$frame)
{
	error_log("WARNING - Stream [". $stream_name ."/". $ondemand_filename ."] - Total frame [". $framecount."] : unable to create the thumbnail from 1800 second frame.");
	
	// Get video thumbnail from 5sec frame.
	$frame = $movie->getFrame($videorate * 5);
	
	if (!$frame)
	{
		error_log("ERROR - Stream  [". $stream_name ."/". $ondemand_filename ."] - Total frame [". $framecount."] : failed to create the thumbnail from 5 second frame.");
		exit(0);
	}
}

//$frame->resize(320, 240);
$image = $frame->toGDImage();
// Save the image to disk
$img_filename = $ondemand_path.$stream_name."/".$ondemand_filename.'.jpg';

if (imagejpeg($image, $img_filename, 100))
{
	if (!symlink($img_filename, "/usr/local/nginx/html/images/thumbnails/".$ondemand_filename.'.jpg'))
	{
		error_log("ERROR - Creating thumbnail symbolic link FAILED. Phisical file: ".$img_filename);
	}
}
else
{
	error_log("ERROR - Unable to create video thumbnail ".$img_filename);
}

?>
