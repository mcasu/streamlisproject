<?PHP
require_once("../include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("login.php");
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
</head>
<body>
		<!-- HEADER -->
        <div class="span-18" align="center">
            <h1>Live Video Streaming</h1>
        </div>
		<center>
		<div id="player" style="text-align:center"></div>
		
		<?php
	
echo '$f("live", "flowplayer.swf", {
 
    clip: {
        url: \'live\',
        live: true,
        // configure clip to use influxis as our provider, it uses our rtmp plugin
        provider: \'influxis\'
    },
 
    // streaming plugins are configured under the plugins node
    plugins: {
 
        // here is our rtpm plugin configuration
        influxis: {
            url: "flowplayer.rtmp-3.2.13.swf",
 
            // netConnectionUrl defines where the streams are found
            netConnectionUrl: \'rtmp://54.213.120.163:1935/'.$app_name.'/'.$stream_name.'
        }
    }
});';

		?>
		</center>
</body>
</html>
