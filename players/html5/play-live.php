<!DOCTYPE html>
<?PHP
require_once("../../include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
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
    <title>HTTP Live Streaming</title> 
</head> 
<body>
    <center>
    <?php
	echo '<video controls src="http://www.streamlis.it/hls/'.$stream_name.'/index.m3u8"  height="240" width="320" >';
	echo '</video>';
    ?>
    </center>
</body> 
</html>