<!DOCTYPE html>
<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");
include(getenv("DOCUMENT_ROOT") . "/include/check_role_viewer.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("../../login.php");
    exit;
}

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
    <link rel="stylesheet" href="skin/functional.css">
    
</head>
<body>

<script src="../../js/jquery-1.11.0.min.js"></script>    
<script src="flowplayer.min.js"></script>

        <div class="span-18" align="center">
            <h1>Live Video Streaming</h1>
        </div>
	
	<center>
	    <?php echo '<div class="flowplayer fixed-controls play-button no-volume no-mute" style="text-align:center" '.
			'data-rtmp="rtmp://www.streamlis.it:1935/'.$app_name.'">'; ?>
	        <video autoplay>
			<?php echo '<source type="video/flash" src="'.$stream_name.'">'; ?>
	        </video>
	    </div>

   
<?php
	
echo '<script>';

    'flowplayer.conf = {'.
	'live: true,'.
	'rtmp: "rtmp://'.$ip_actual.':1935/'.$app_name.'/'.$stream_name.'",'.
	'ratio: 3/4,'.
	'width: 480px,'.
	'height: 640px,'.
	/*'swf: "http://releases.flowplayer.org/5.4.3/flowplayer.swf" */
	'swf: "flowplayer.swf"'.
	 '};';

echo '</script>';

?>

		</center>
</body>
</html>
