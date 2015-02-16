<?PHP

class FSActions
{


function FSActions()
{


}

/**
 * 
 * @param string $ondemand_path
 * @param string $ondemand_filename
 * @return boolean: TRUE on success or FALSE on failure.
 */
function DeleteOnDemandVideoFromDisk($ondemand_app_name, $ondemand_path, $ondemand_filename)
{
    $basename = basename($ondemand_filename, ".flv");
    
    $videoFlvFullPath = $ondemand_path.$ondemand_filename;
    $thumbFullPath = $ondemand_path.$basename."jpg";
    
    $ondemand_mp4_path = str_replace($ondemand_app_name, "mp4", $ondemand_path);
    $videoMp4FullPath = $ondemand_mp4_path.$basename."mp4";
    
    error_log("INFO - DeleteOnDemandVideoFromDisk() - videoFlvFullPath->[".$videoFlvFullPath."] - videoMp4FullPath->[".$videoMp4FullPath."]");
    
    try
    {
        // Delete flv video file
        if (file_exists($videoFlvFullPath))
        {
            unlink($videoFlvFullPath);
        }
        
        // Delete thumbnail image file
        if (file_exists($thumbFullPath))
        {
            unlink($thumbFullPath);
        }
        
        // Delete mp4 video file
        if (file_exists($videoMp4FullPath))
        {
            unlink($videoMp4FullPath);
        }
        
        return TRUE;
        
    }
    catch(Exception $e)
    {
        error_log("ERROR - FSActions method DeleteOnDemandVideoFromDisk() - [".$videoFlvFullPath."] - ". $e->getMessage());
        return FALSE;
    }
}

/**
 * 
 * @param type $nginx_id
 * @param type $ondemand_path
 * @param type $client_addr
 * @param type $record_path
 * @param type $stream_name
 * @return boolean: TRUE on success or FALSE on failure.
 */
function SaveOnDemandVideoToDisk($nginx_id,$ondemand_path,$client_addr,$record_path,$stream_name)
{
	if (!isset($ondemand_path))
	{
		error_log("ERROR - On-demand path [".$ondemand_path."] errato.");
		return false;
	}
	
	if (!isset($record_path))
	{
		error_log("ERROR - Recorded file path [".$record_path."] errato.");
		return false;
	}

	$ondemand_fullpath = $ondemand_path.$stream_name."/";
	
	if (!file_exists($ondemand_fullpath))
        {
            mkdir($ondemand_fullpath, 0755, true);
        }

	$path_parts = pathinfo($record_path);
	$filename = $path_parts['basename'];
	
	if (rename($record_path, $ondemand_fullpath."TEMP_".$filename))
	{
		$yamdi_commandline='/usr/bin/yamdi -i '.$ondemand_fullpath."TEMP_".$filename.' -o '.$ondemand_fullpath.$filename;
		$last_line = system($yamdi_commandline, $retval);
	
		unlink($ondemand_fullpath."TEMP_".$filename);
	
		if (symlink($ondemand_fullpath.$filename, $ondemand_path.$filename))
		{
			return true;	
		}
		error_log('EEROR - Creazione del link simbolico ['.$ondemand_path.$filename.'] fallita!');
		return false;
	}

	error_log('ERROR - Copia del file registrato in ['.$ondemand_fullpath.'TEMP_'.$filename.'] fallita!');
	return false;	
}

}

