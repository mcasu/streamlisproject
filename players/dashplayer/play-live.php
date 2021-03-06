<!DOCTYPE html>
<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");

$app_name = filter_input(INPUT_GET, 'app_name');
$stream_name = filter_input(INPUT_GET, 'stream_name');
$stream_type = filter_input(INPUT_GET, 'stream_type');

if(!isset($stream_name) || empty($stream_name) || !isset($stream_type)) 
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
    <meta charset="utf-8"/>
    <title>HTML5 Player</title>
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