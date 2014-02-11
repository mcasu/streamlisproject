<?PHP

class FSActions
{


function FSActions()
{


}

function OnRecordDone($nginx_id,$ondemand_path,$client_addr,$record_path)
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

	if (!file_exists($ondemand_path)) mkdir($ondemand_path, 0755, true);

	$path_parts = pathinfo($record_path);
	$filename = $path_parts['basename'];
		
	if (rename($record_path, $ondemand_path.$filename))
	{
		return true;
	}

	error_log("Copia del file registrato fallita!");
	return false;	
}

}

?>
