<?PHP
require_once("../include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("login.php");
    exit;
}


if(isset($_GET['filename'])) 
{
	$filename=$_GET['filename'];
}

if(isset($_GET['stream_name'])) 
{
	$stream_name=$_GET['stream_name'];
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
                        file: "rtmp://54.213.120.163:1935/vod/'.$filename.'",
                        autostart: true,
                        controls: true,
                        aspectratio: "4:3",
                        width: 640,
                        height: 480,
                        });'.
			'</script>';

		?>
		</center>
</body>
</html>
