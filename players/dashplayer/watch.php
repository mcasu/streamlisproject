<!DOCTYPE html>
<?PHP
$token = filter_input(INPUT_GET, 't');
$stream_type = filter_input(INPUT_GET, 'stream_type');

if(!isset($token) || empty($token) || !isset($stream_type)) 
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

$groupData = $dbactions->GetGroupByToken($token);

if ($groupData && !empty($groupData['publish_code']))
{
    $data = $dbactions->GetLiveEventsByPublisher($groupData['publish_code']);
}
else
{
    $data = $dbactions->GetEventsLiveData($token);
}

if (!$data || empty($data) || mysql_num_rows($data) !== 1)
{
    // Access forbidden:
    header('HTTP/1.1 401 Unauthorized');
    // Set our response code
    http_response_code(401);
    echo "<h1>401 Unauthorized - Access denied.</h1><br/><h3>Contattare l'amministratore di sistema.</h3>";
    exit; 
}

$row = mysql_fetch_array($data);
$app_name = $row['app_name'];
$stream_name = $row['stream_name'];

?>

<html>
<head>
    <meta charset="utf-8"/>
    <title>DASH-MPEG Player</title>
    <meta name="description" content="" />

    <script type="text/javascript" src="bitmovin-player/bitmovinplayer.js"></script>
    
<!--    <style>
        video {
            width: 480px;
            height: 360px;
        }
    </style>-->

    <body>
        
        <div id="player"></div>

        <script type="text/javascript">
            var conf = {
                key:       "87f64fdd-b06e-4ce6-8bcc-2ccdf6249f9a",
                source: {
                    <?php 
                    if ($stream_type == "dash") { echo 'dash:   "https://www.streamlis.it/dash/'.$stream_name.'/index.mpd"'; }
                    if ($stream_type == "hls") { echo 'hls:   "https://www.streamlis.it/hls/'.$stream_name.'/index.m3u8"'; }
                    ?>
                },
                playback: {
                    autoplay: true
                },
                tweaks: {
                    autoqualityswitching : true,
                    max_buffer_level     : 2
                }
            };
            var player = bitmovin.player("player");
            player.setup(conf).then(function(value) {
                // Success
                console.log("Successfully created bitmovin player instance");
            }, function(reason) {
                // Error!
                console.log("Error while creating bitmovin player instance");
            });
        </script>
        
    </body>
</html>