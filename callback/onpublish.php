<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$app_name = $_POST['app'];
$nginx_id = $_POST['clientid'];
$stream_name = $_POST['name'];
$client_addr = $_POST['addr'];
if(isset($_POST['code'])) 
{
	$publish_code = $_POST['code'];
}
else
{
	$publish_code = "0000";
}

$mysqldate = date("Y-m-d"); 
$mysqltime = date("H:i:s"); 


/***  If publish stream name already exists then reload nginx to clean connection status ***/
/*
$num_rows = $dbactions->PublishNameAlreadyExists($app_name,$stream_name);
if ($num_rows > 0)
{
	
	// Delete record with publish name duplicated
	if ($dbactions->DeletePublishNameDuplicated($app_name,$stream_name))
	{
		//Reload Nginx service
		$last_line = system('echo "numero record: '.$num_rows.'" > /tmp/'.$stream_name.'.txt', $retval);
		$last_line = system('sudo /etc/init.d/nginx force-reload', $retval);
		
		if ($retval != '0')
		{
			error_log("BadName publishing error has occurred but nginx reloading has FAILED!");
		}
	}
	else
	{
		$last_line = system('echo "numero record: '.$num_rows.' - delete fallita!!" > /tmp/'.$stream_name.'.txt', $retval);
	}
	
}
*/
	
/*** Save live publish info into database ***/
if (!$dbactions->OnPublish($nginx_id,$app_name,$stream_name,$client_addr,$stream_name,$mysqldate,$mysqltime))
{
	error_log("Publishing the stream ".$stream_name." FAILED! ".$dbactions->GetErrorMessage());
	exit;
}

/*** Exec live transcode to HLS  ***/
/*
$stream_name_base = basename($stream_name,".flv");
///$cmd = 'nohup nice -n 10 /usr/bin/avconv -loglevel info -i "rtmp://localhost/'.$app_name.'/'.$stream_name_base.'" -vcodec libx264 -f flv "rtmp://localhost/hls/'.$stream_name_base.'" >/var/log/nginx/avconv-'.$app_name.'-'.$stream_name_base.'.log 2>&1';
$cmd = 'nohup /usr/bin/avconv -loglevel info -i "rtmp://localhost/'.$app_name.'/'.$stream_name_base.'" -vcodec libx264 -f flv "rtmp://localhost/hls/'.$stream_name_base.'" >/var/log/nginx/avconv-'.$app_name.'-'.$stream_name_base.'.log 2>&1';
$output = shell_exec($cmd);

$cmd_verify = 'nohup echo "Sono passato oltre..." > /var/log/nginx/async.log';
$output = shell_exec($cmd_verify);
*/
?>
