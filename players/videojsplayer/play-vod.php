<?PHP
require_once("../../include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
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
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="skin/functional.css">
    <script src="../../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="flowplayer.js" ></script>
</head>
<body>
        <div class="span-18" align="center">
            <h1>On-demand HTML5 Video Streaming</h1>
        </div>
		<center>
	    <?php
		echo '<div class="flowplayer fixed-controls play-button no-volume no-mute"'.
		'data-rtmp="rtmp://www.streamlis.it:1935/vod-hls">';
		    echo '<video autoplay>';
			echo '<source type="video/mp4" src="'.$filename_withoutext.'.mp4">';
		    echo '</video>';
	    echo '</div>';
	    ?>
</body>
</html>