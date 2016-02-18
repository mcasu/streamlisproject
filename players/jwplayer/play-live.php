<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");
//include(getenv("DOCUMENT_ROOT") . "/include/check_role_viewer.php");

//$utils = $mainactions->GetUtilsInstance();
//$dbactions = $mainactions->GetDBActionsInstance();
//
//if(!$mainactions->CheckLogin())
//{
//    $utils->RedirectToURL("../../login.php");
//    exit;
//}

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
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	
	<script type="text/javascript" src="jwplayer.js" ></script>
</head>
<body>
    
    <div class="container-fluid">
        <center>
            <h1>Live Video Streaming</h1>
            <div id="player" style="text-align:center; vertical-align:middle; width: 100%; height: 90%"/>
        </center>
        
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
            aspectratio: "16:9"
            });'.
            '</script>';

    ?>
</body>
</html>
