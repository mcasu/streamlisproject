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

$movie = null;
$framecount = null;
$videorate = null;
try
{
    $movie = new ffmpeg_movie($ondemand_path.strtolower($stream_name)."/".$ondemand_basename, false);

    // Get video infos
    $video_duration = $movie->getDuration();
    // Get video bitrate in Kbps
    $video_bitrate = $movie->getVideoBitRate()/1024;
    $video_codec = $movie->getVideoCodec();
    $videorate = $movie->getFrameRate();
    $framecount = $movie->getFrameCount();
    
    error_log("DEBUG - framecount: [" . $movie->getFrameCount() . "] - framerate: [" . $movie->getFrameRate() ."]");
}
catch (Exception $ex) 
{
    error_log("ERROR - OnRecordDone() Unable to get infos from video [".$ondemand_basename."] \n". $ex->getMessage());
    exit;
}

/*** SAVE VIDEO INFO INTO DATABASE ***/
$ondemandId = $dbactions->OnRecordDone(
        $app_name,
        strtolower($stream_name),
        $ondemand_path.strtolower($stream_name)."/",
        $ondemand_basename,
        $video_duration,
        $video_bitrate,
        $video_codec,   
        $date_temp);

if (!$ondemandId)
{
	error_log("ERROR - OnRecordDone() Recording the stream ".strtolower($stream_name)." FAILED! - ".$dbactions->GetErrorMessage());
	exit;
}



/*** CREATE VIDEO THUMBNAIL ***/

// Provo a recuperare la thumbnail dai frame tra 1000-1050 secondi.
for($i = 1000; $i <=1050; $i++)
{
    $frame = $movie->getFrame($videorate * (int)$i);    
    
    if ($frame != null)
    {
        break;
    }
}

if ($frame == null)
{
	error_log("WARNING - OnRecordDone.php - Stream [". strtolower($stream_name) ."/". $ondemand_filename ."] - Total frame [". $framecount."] : unable to create the thumbnail from 1000-1050 second frame.");
	
        // Provo a recuperare la thumbnail dai frame tra 5-10 secondi.
        for($i = 5; $i <=10; $i++)
        {
            $frame = $movie->getFrame($videorate * (int)$i);    

            if ($frame != null)
            {
                break;
            }
        }
        
        if ($frame == null)
        {
            error_log("ERROR - OnRecordDone.php - Stream  [". strtolower($stream_name) ."/". $ondemand_filename ."] - Total frame [". $framecount."] : failed to create also the thumbnail from 5-10 second frame.");
        }
}

$img_filename = $ondemand_path.strtolower($stream_name)."/".$ondemand_filename.'.jpg';
    
try
{
    //$image = $frame->toGDImage();
    
    // Save the image to disk
    imagejpeg($frame, $img_filename, 100);
            
    // Creo la directory generale per le immagini thumbnail
    if (!file_exists("/usr/local/nginx/html/images/thumbnails/")) 
    {
        mkdir("/usr/local/nginx/html/images/thumbnails/", 0755, true);
    }

    // Creo il link all'immagine
    if (!symlink($img_filename, "/usr/local/nginx/html/images/thumbnails/".$ondemand_filename.'.jpg'))
    {
            error_log("ERROR - Creating thumbnail symbolic link FAILED. Phisical file: ".$img_filename);
    }
} 
catch (Exception $ex) 
{
    error_log("ERROR - Unable to create video thumbnail ".$img_filename);
}

// AGGIUNGO OPERAZIONE PER CONVERTIRE IN MP4 IL VIDEO //
if (!$dbactions->MarkOndemandVideoToConvert($ondemandId))
{
    error_log("ERROR - onrecorddone.php MarkOndemandVideoToConvert() FAILED! " . $dbactions->GetErrorMessage());
}
