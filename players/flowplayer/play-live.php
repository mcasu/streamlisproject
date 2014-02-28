<!DOCTYPE html>
<?PHP
require_once("../../include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("../../login.php");
    exit;
}


if(isset($_GET['app_name'])) 
{
	$app_name=$_GET['app_name'];
}

if(isset($_GET['stream_name'])) 
{
	$stream_name=$_GET['stream_name'];
}

?>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="//releases.flowplayer.org/5.4.6/skin/minimalist.css">
    
</head>
<body>
		<!-- HEADER -->
        <div class="span-18" align="center">
            <h1>Live Video Streaming</h1>
        </div>
		<center>
		<div id="player" style="text-align:center">
		    <!--<video>
		    </video>-->
		</div>

<script src="../../js/jquery-1.11.0.min.js"></script>    
<script src="flowplayer-3.2.13.min.js"></script>
   
<?php
	
echo '<script>';

/*
    'flowplayer.conf = {'.
	'live: true,'.
	'rtmp: "rtmp://54.213.120.163:1935/'.$app_name.'/'.$stream_name.'",'.
	'ratio: 3/4,'.
	'width: 640px,'.
	'height: 480px,'.
	'swf: "http://releases.flowplayer.org/5.4.3/flowplayer.swf" };';
*/	

echo 'flowplayer("player", "flowplayer-3.2.18.swf", {'.
        'clip: {'.
            'url : "'.$stream_name.'",'. /*this is the name of the stream assset in the encoder*/
	    'live : true,'.  /*// tell flowplayer it's live*/
	    'provider: "rtmp"'.
	'},'.
 
	'plugins: {'.
	    'rtmp: {'.
	    'url: "flowplayer.rtmp-3.2.13.swf",'.
	    'netConnectionUrl: "rtmp://54.213.120.163:1935/'.$app_name.'",'.  /*this is the rest of the URL excluding the stream name that you set in the encoder*/
	    'subscribe: true'.  /*subscribe to the stream*/
                    '}'.
                 '}'.
        '});';

echo '</script>';

?>
		</center>
</body>
</html>
