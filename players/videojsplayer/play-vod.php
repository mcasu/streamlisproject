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

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="http://vjs.zencdn.net/4.5/video-js.css" rel="stylesheet">
    <script src="http://vjs.zencdn.net/4.5/video.js"></script>
</head>
<body>
        <div class="span-18" align="center">
            <h1>On-demand HTML5 Video Streaming</h1>
        </div>
		<center>
		    <?php
			echo '<video id="my_video_1" class="video-js vjs-default-skin" controls preload="auto" width="640" height="480" data-setup="{}">';
			    echo '<source src="rtmp://www.jwstream.org:1935/vod-hls/'.$filename.'" type="video/mp4">';
			echo '</video>';
		    ?>
	</div>
</body>
</html>