<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");
//include(getenv("DOCUMENT_ROOT") . "/include/check_role_viewer.php");
//
//$utils = $mainactions->GetUtilsInstance();
//$dbactions = $mainactions->GetDBActionsInstance();
//
//if(!$mainactions->CheckLogin())
//{
//    $utils->RedirectToURL("../../login.php");
//    exit;
//}


if(isset($_GET['filename'])) 
{
	$filename=$_GET['filename'];
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
	    <?php echo '<div class="flowplayer fixed-controls play-button no-volume no-mute" style="text-align:center" data-rtmp="rtmp://www.streamlis.it:1935/vod-flash">'; ?>
		<video autoplay>
			<?php echo '<source type="video/flash" src="'.$filename_withoutext.'.flv">'; ?>
	        </video>
	    </div>		
<?php
	
echo '<script>';

    'flowplayer.conf = {'.
	'live: true,'.
	'rtmp: "rtmp://'.$ip_actual.':1935/vod/'.$filename.'",'.
	'ratio: 9/16,'.
	'width: 480px,'.
	'swf: "flowplayer.swf"'.
	 '};';

    echo '</script>';

?>
		</center>
</body>
</html>