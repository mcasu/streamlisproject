<?PHP
require_once("../../include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("../../login.php");
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

/*$path_parts = pathinfo($filename);*/
$filename_withoutext=substr($filename, 0, strlen($filename)-4);

?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="skin/functional.css">
	
</head>
<body>
    <script src="../../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="flowplayer.js" ></script>
    
    <div class="span-18" align="center"> <h1>On-demand Video Streaming</h1> </div>
		
	<center>
	    <?php echo '<div class="flowplayer fixed-controls play-button no-volume no-mute" style="text-align:center" data-rtmp="rtmp://54.213.120.163:1935/vod">'; ?>
		<video autoplay>
			<?php /*echo '<source type="video/flash" src="'.$filename_withoutext.'.flv">'; */ ?>
			<?php echo '<source type="video/mp4" src="'.$filename_withoutext.'.mp4">'; ?>
	        </video>
	    </div>		
<?php
	
echo '<script>';

    'flowplayer.conf = {'.
	'live: true,'.
	'rtmp: "rtmp://54.213.120.163:1935/vod/'.$filename.'",'.
	'ratio: 3/4,'.
	'width: 480px,'.
	'height: 640px,'.
	'swf: "flowplayer.swf"'.
	 '};';


	    /* echo '<script type="text/javascript">'.
			'jwplayer("player").setup({
                        file: "rtmp://54.213.120.163:1935/vod/'.$filename.'",
                        autostart: true,
                        controls: true,
                        aspectratio: "4:3",
                        width: 640,
                        height: 480,
                        });'.
			'</script>'; */
    echo '</script>';

?>
		</center>
</body>
</html>
