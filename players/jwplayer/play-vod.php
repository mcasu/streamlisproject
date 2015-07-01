<?PHP
require_once("../../include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("../../login.php");
    exit;
}

$filename = filter_input(INPUT_GET, 'filename');

if(!isset($filename) || empty($filename)) 
{
    // Access forbidden:
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Url non valida.</h1><br/>Contattare l'amministratore di sistema.";
    exit; 
}

if(isset($_GET['stream_name'])) 
{
	$stream_name=$_GET['stream_name'];
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
        <div class="span-18" align="center">
            <h1>On-demand Video Streaming</h1>
        </div>
		<center>
		<div id="player" style="text-align:center"></div>
		
		<?php
		
		echo '<script type="text/javascript">'.
			'jwplayer("player").setup({
                        file: "rtmp://www.streamlis.it:1935/vod-flash/'.$filename.'",
                        autostart: true,
                        controls: true,
			rtmp: {
			    bufferlength: 0.1  
			},
                        aspectratio: "4:3",
                        width: 640,
                        height: 480,
                        });'.
			'</script>';

		?>
		</center>
</body>
</html>
