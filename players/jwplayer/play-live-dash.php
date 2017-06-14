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
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
        <script type="text/javascript" src="jwplayer-7.11.3/jwplayer.js"></script>
        <script>jwplayer.key="UUfnO2lEZWsYYjNDJs/j4GbGBTQcA93zo6s0tw==";</script>
                
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
		

    <script>
        const player = jwplayer('player').setup({
        playlist:[{
            sources:'https://www.streamlis.it/dash/<?php echo $stream_name;?>/index.mpd',
            title:'DASH-MPEG Player',
            description:'This is a DASH stream!',
            type:'dash'
        }],
        dash: "shaka",
        autostart: true
    });
    </script>

</body>
</html>
