<!DOCTYPE html>
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

$app_name = filter_input(INPUT_GET, 'app_name');

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

<html> 
<head> 
    <title>HTTP Live Streaming</title> 
</head> 
<body>
    <center>
    <?php
	echo '<video controls src="https://www.streamlis.it/hls/'.$stream_name.'/index.m3u8"  height="240" width="320" >';
	echo '</video>';
    ?>
    </center>
</body> 
</html>