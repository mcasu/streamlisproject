<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");

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

$filename = filter_input(INPUT_GET, 'filename');

if(!isset($filename) || empty($filename)) 
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
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="../../style/bootstrap.min.css">
	
    <script src="jwplayer-7.12.0/jwplayer.js"></script>
    <script>jwplayer.key="UUfnO2lEZWsYYjNDJs/j4GbGBTQcA93zo6s0tw==";</script>
    <script src="../../js/bootstrap.min.js"></script>
</head>
<body>
    
    <div class="container-fluid">
        <center>
            <h1>On-demand Video Streaming</h1>
        </center>
        <video id="player"/>
    </div>
    
    <?php

    echo '<script type="text/javascript">'.
            'jwplayer("player").setup({
            file: "https://www.streamlis.it/flash/'.$filename.'",
            autostart: true,
            controls: true,
            primary: "html5",
            playbackRateControls: true,
            rtmp: {
                bufferlength: 0.1  
            },
            aspectratio: "16:9"
            });'.
            '</script>';
            

    ?>
</body>
</html>
