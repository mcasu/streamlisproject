<?PHP

class FSActions
{


function FSActions()
{


}

function OnRecordDone($nginx_id,$ondemand_path,$client_addr,$record_path,$stream_name)
{
	if (!isset($ondemand_path))
	{
		error_log("On-demand path [".$ondemand_path."] errato.");
		exit;
	}
	
	if (!isset($record_path))
	{
		error_log("Recorded file path [".$record_path."] errato.");
		exit;
	}

	$ondemand_fullpath = $ondemand_path.$stream_name."/";
	
	if (!file_exists($ondemand_fullpath)) mkdir($ondemand_fullpath, 0755, true);

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
		error_log('Creazione del link simbolico ['.$ondemand_path.$filename.'] fallita!');
		return false;
	}

	error_log('Copia del file registrato in ['.$ondemand_fullpath.'TEMP_'.$filename.'] fallita!');
	return false;	
}

}

?>
