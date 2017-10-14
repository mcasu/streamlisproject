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

$filetype = filter_input(INPUT_GET, 'filetype');

if(!isset($filetype) || empty($filetype)) 
{
    // Access forbidden:
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Url non valida.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}
if($filetype != "flv" && $filetype != "mp4") 
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
	
    <script type="text/javascript" src="../../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../../js/bootstrap.min.js"></script>
    <script type="text/javascript" src="jwplayer-7.12.0/jwplayer.js"></script>
    <script>jwplayer.key="UUfnO2lEZWsYYjNDJs/j4GbGBTQcA93zo6s0tw==";</script>
</head>
<body>
    
    <div class="container-fluid">
          <video id="player"/>
    </div>
    
    <?php

    $filenameWithoutExt = basename($filename, ".flv");
    
    if ($filetype == "flv")
    {
        echo '<script type="text/javascript">'.
                'jwplayer("player").setup({
                file: "https://www.streamlis.it/flash/'.$filenameWithoutExt.'.flv",
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
    }       
    
    if ($filetype == "mp4")
    {
        echo '<script type="text/javascript">'.
                'jwplayer("player").setup({
                file: "https://www.streamlis.it/mp4/'.$filenameWithoutExt.'.mp4",
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
    }

    ?>
</body>
</html>
