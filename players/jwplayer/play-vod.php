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
	
	<script type="text/javascript" src="jwplayer.js" ></script>
</head>
<body>
    
    <div class="container-fluid">
        <center>
            <h1>On-demand Video Streaming</h1>
            <video id="player" style="text-align:center; width: 100%; height: auto;"/>
        </center>
    </div>
    
    <?php

        echo '<script type="text/javascript">'.
                'jwplayer("player").setup({
                file: "rtmp://www.streamlis.it:1935/vod-flash/'.$filename.'",
                autostart: true,
                controls: true,
                rtmp: {
                    bufferlength: 0.1  
                },
                aspectratio: "4:3"
                });'.
                '</script>';
            
//        echo '<script type="text/javascript">'.
//                'jwplayer("player").setup({
//                file: "rtmp://www.streamlis.it:1935/vod-flash/'.$filename.'",
//                autostart: true,
//                controls: true,
//                rtmp: {
//                    bufferlength: 0.1  
//                },
//                aspectratio: "4:3",
//                width: 640,
//                height: 480,
//                });'.
//                '</script>';

    ?>
</body>
</html>
