<!DOCTYPE html>
<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");

$app_name = filter_input(INPUT_GET, 'app_name');

$stream_name = filter_input(INPUT_GET, 'stream_name');

if(!isset($stream_name) || empty($stream_name)) 
{
    // Access forbidden:
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Url non valida.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}

if ($myhostname == "lnxstreamserver-dev")
{
    $ip_actual = $ip_private;
}
else
{
    $ip_actual = $ip_public;
}

?>


<html> 
<head> 
    <title>HTTP MPEG-DASH Live Streaming</title> 
    <script src="http://cdn.dashjs.org/latest/dash.all.min.js"></script>

    <style>
        video {
        width: 640px;
        height: 360px;
        }
    </style>
</head> 

<body>
    <center>
    <div>
        <?php
            echo '<video data-dashjs-player autoplay src="https://www.streamlis.it/dash/'.$stream_name.'/manifest.mpd" controls></video>';
        ?>
    </div>
    </center>
</body> 
</html>