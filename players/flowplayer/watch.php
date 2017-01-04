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

if ($groupData)
{
    $data = $dbactions->GetLiveEventsByPublisher($groupData['publish_code']);
}
else
{
    $data = $dbactions->GetEventsLiveData($token);
}

if (!$data || empty($data))
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
    <link rel="stylesheet" href="skin/functional.css">
    
</head>
<body>

<script src="../../js/jquery-1.11.0.min.js"></script>    
<script src="flowplayer.min.js"></script>

    <div class="container-fluid">
        <center>
            <h1>Live Video Streaming</h1>
        </center>
        <?php echo '<div class="flowplayer fixed-controls play-button no-volume no-mute" style="text-align:center" '.
            'data-rtmp="rtmp://www.streamlis.it:1935/'.$app_name.'">'; ?>
            <video autoplay>
                    <?php echo '<source type="video/flash" src="'.$stream_name.'">'; ?>
            </video>
        <?php echo '</div>' ?>
    </div>
	
<?php
	
echo '<script>';

    'flowplayer.conf = {'.
	'live: true,'.
	'rtmp: "rtmp://'.$ip_actual.':1935/'.$app_name.'/'.$stream_name.'",'.
	'ratio: 9/16,'.
	'width: 340px,'.
	'swf: "flowplayer.swf"'.
	 '};';

echo '</script>';

?>

</body>
</html>
