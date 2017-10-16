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

$groupData = $dbactions->GetGroupByToken($token);

if ($groupData && !empty($groupData['publish_code']))
{
    $data = $dbactions->GetLiveEventsByPublisher($groupData['publish_code']);
}
else
{
    $data = $dbactions->GetEventsLiveData($token);
}

if (!$data || empty($data) || mysql_num_rows($data) !== 1)
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
	
    <script src="jwplayer-7.12.0/jwplayer.js"></script>
    <script>jwplayer.key="UUfnO2lEZWsYYjNDJs/j4GbGBTQcA93zo6s0tw==";</script>
</head>
<body>
    
    <div class="container-fluid">
          <video id="player"/>
    </div>
		
    <?php

    echo '<script type="text/javascript">'.
            'jwplayer("player").setup({
            file: "rtmp://www.streamlis.it:1935/'.$app_name.'/'.$stream_name.'",
            autostart: true,
            controls: true,
            primary: "html5",
            playbackRateControls: true,            
            rtmp: {
                bufferlength: 0.1  
            },
            stretching: "fill",
            width: "100%",
            aspectratio: "16:9"
            });'.
            '</script>';

    ?>
</body>
</html>