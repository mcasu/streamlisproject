<!DOCTYPE html>
<?PHP
$token = filter_input(INPUT_GET, 't');

if(!isset($token) || empty($token)) 
{
    // Access forbidden:
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Url non valida.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}
require_once(getenv("DOCUMENT_ROOT") . "/include/config.php");
$dbactions = $mainactions->GetDBActionsInstance();

if ($myhostname == "lnxstreamserver-dev")
{
    $ip_actual = $ip_private;
}
else
{
    $ip_actual = $ip_public;
}

$data = $dbactions->GetEventsLiveData($token);

if (!$data)
{
    // Access forbidden:
    header('HTTP/1.1 401 Unauthorized');
    // Set our response code
    http_response_code(401);
    echo "<h1>401 Unauthorized - Access denied.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}

$row = mysql_fetch_array($data);
$app_name = $row['app_name'];
$stream_name = $row['stream_name'];

?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<script type="text/javascript" src="jwplayer.js" ></script>
</head>
<body>
    
    <div class="container-fluid">
        <center>
            <h1>Live Video Streaming</h1>
        </center>
        <div class="container" style="margin-left: 30px;">
            <div id="player"></div>
        </div>
    </div>
		
    <?php

    echo '<script type="text/javascript">'.
            'jwplayer("player").setup({
            file: "rtmp://www.streamlis.it:1935/'.$app_name.'/'.$stream_name.'",
            autostart: true,
            controls: true,
            rtmp: {
                bufferlength: 0.1  
            },
            aspectratio: "16:9",
            width: "86%"
            });'.
            '</script>';

    ?>
</body>
</html>