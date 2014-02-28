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
		<div class="flowplayer" id="player" data-engine="flash" style="text-align:center">
		    <video>
			<?php echo '<source type="video/flash" src="'.$stream_name.'">'; ?>
		    </video>
		</div>
		
<?php
	
echo '<script type="text/javascript">'.

    'flowplayer.conf = {'.
	'live: true,'.
	'rtmp: "rtmp://54.213.120.163:1935/'.$app_name.'",'.
	'ratio: 9/16,'.
	'swf: "flowplayer.swf" };'.
    '</script>';

?>
		</center>
</body>
</html>
