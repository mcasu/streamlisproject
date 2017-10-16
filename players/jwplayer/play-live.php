<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");

$app_name = filter_input(INPUT_GET, 'app_name');

if(!isset($app_name) || empty($app_name)) 
{
    // Access forbidden:
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Url non valida.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}

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

$stream_type = filter_input(INPUT_GET, 'stream_type');

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
