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
$ondemand_basename = strtolower($path_parts['basename']);
$ondemand_filename = strtolower($path_parts['filename']);
$record_tmp_dir = $path_parts['dirname'];

$strtoremove_lenght = strlen($stream_name);
$ondemand_datetime = substr($ondemand_filename, $strtoremove_lenght + 1);

list($ondemand_onlydate, $ondemand_onlytime) = split("_", $ondemand_datetime);

$date_temp = strftime("%Y-%m-%d", strtotime($ondemand_onlydate));
//$time_temp = strftime("%H:%M:%S", strtotime(str_replace("-",":", $ondemand_onlytime)));

//error_log("INFO - ONDEMAND DATE: " . $date_temp);

$videoFullPathLowerCase = NULL;
if (file_exists($record_path))
{
    $videoFullPathLowerCase = $utils->RenameFileToLowerCase($record_path);
}

if (!$videoFullPathLowerCase)
{
    exit;
}


/*** SAVE FLV VIDEO TO DISK ***/
if (!$fsactions->SaveOnDemandVideoToDisk($nginx_id,
                                        $ondemand_path,
                                        $client_addr,
                                        $videoFullPathLowerCase, 
                                        strtolower($stream_name)))
{
	error_log("ERROR - SaveOnDemandVideoToDisk() [" .$videoFullPathLowerCase. "] FAILED!");
	exit;	
}

$movie = new ffmpeg_movie($ondemand_path.strtolower($stream_name)."/".$ondemand_basename, false);

/*** SAVE VIDEO INFO INTO DATABASE ***/
$ondemandId = $dbactions->OnRecordDone($app_name,
                            strtolower($stream_name),
                            $ondemand_path.strtolower($stream_name)."/",
                            $ondemand_basename,
                            $movie,
                            $date_temp);

if (!$ondemandId)
{
	error_log("ERROR - OnRecordDone() Recording the stream ".strtolower($stream_name)." FAILED! - ".$dbactions->GetErrorMessage());
	exit;
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
	error_log("WARNING - OnRecordDone.php - Stream [". strtolower($stream_name) ."/". $ondemand_filename ."] - Total frame [". $framecount."] : unable to create the thumbnail from 1000 second frame.");
	
	// Get video thumbnail from 5sec frame.
	$frame = $movie->getFrame($videorate * 5);
	
	if (!$frame)
	{
		error_log("ERROR - OnRecordDone.php - Stream  [". strtolower($stream_name) ."/". $ondemand_filename ."] - Total frame [". $framecount."] : failed to create the thumbnail from 5 second frame.");
		exit(0);
	}
}

//$frame->resize(320, 240);
$image = $frame->toGDImage();
// Save the image to disk
$img_filename = $ondemand_path.strtolower($stream_name)."/".$ondemand_filename.'.jpg';

if (imagejpeg($image, $img_filename, 100))
{
    if (!file_exists("/usr/local/nginx/html/images/thumbnails/")) 
    {
        mkdir("/usr/local/nginx/html/images/thumbnails/", 0755, true);
    }
    
    if (!symlink($img_filename, "/usr/local/nginx/html/images/thumbnails/".$ondemand_filename.'.jpg'))
    {
            error_log("ERROR - Creating thumbnail symbolic link FAILED. Phisical file: ".$img_filename);
    }
}
else
{
	error_log("ERROR - Unable to create video thumbnail ".$img_filename);
}


// AGGIUNGO OPERAZIONE PER CONVERTIRE IN MP4 IL VIDEO //
if (!$dbactions->MarkOndemandVideoToConvert($ondemandId))
{
    error_log("ERROR - onrecorddone.php MarkOndemandVideoToConvert() FAILED! " . $dbactions->GetErrorMessage());
}
