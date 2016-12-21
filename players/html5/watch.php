<!DOCTYPE html>
<?PHP
$token = filter_input(INPUT_GET, 't');

if(!isset($token) || empty($token)) 
{
    // Access forbidden:
    header('HTTP/1.1 403 Forbidden');
    // Set our response code
    http_response_code(403);
    echo "<h1>403 Forbidden - Url non valida.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}
require_once(getenv("DOCUMENT_ROOT") . "/include/config.php");
$dbactions = $mainactions->GetDBActionsInstance();

if ($myhostname == "lnxstreamserver-dev")
{
    $ip_actual = $ip_private;
}
else
{
    $ip_actual = $ip_public;
}

$data = $dbactions->GetEventsLiveData($token);

if (!$data || empty($data))
{
    // Access forbidden:
    header('HTTP/1.1 401 Unauthorized');
    // Set our response code
    http_response_code(401);
    echo "<h1>401 Unauthorized - Access denied.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}

$row = mysql_fetch_array($data);
$stream_name = $row['stream_name'];

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