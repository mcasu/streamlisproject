<!doctype html>
<?PHP
include(getenv("DOCUMENT_ROOT") . "/check_login.php");

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
    <meta charset="utf-8"/>
    <title>HLS Player</title>
    <meta name="description" content="" />

    <script type="text/javascript" src="https://bitmovin-a.akamaihd.net/bitmovin-player/stable/7.1/bitmovinplayer.js"></script>
    
<!--    
    <script src="app/lib/q.js"></script>
    <script src="app/lib/dijon.js"></script>
    <script src="app/lib/xml2json.js"></script>
    <script src="app/lib/objectiron.js"></script>
    <script src="app/lib/long.js"></script>
    <script src="app/lib/Math.js"></script>
    
    <script src="dash.all.js"></script>
 -->
<!--
    <script>
        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                vars[key] = value;
            });
            return vars;
        }

        function startVideo() {
            var vars = getUrlVars(),
                url = "https://www.streamlis.it/dash/index.mpd",
                video,
                context,
                player;

            if (vars && vars.hasOwnProperty("url")) {
                url = vars.url;
            }

            video = document.querySelector(".dash-video-player video");
            context = new Dash.di.DashContext();
            player = new MediaPlayer(context);

            player.startup();

            player.attachView(video);
            player.setAutoPlay(true);

            player.attachSource(url);
        }
    </script>
-->
    <style>
        video {
            width: 640px;
            height: 480px;
        }
    </style>

    <body>
        
<!--        <div class="dash-video-player">
            <video controls="true"></video>
        </div>
 -->   
        
    <div id="player"></div>
<script type="text/javascript">
    var conf = {
        key:       "87f64fdd-b06e-4ce6-8bcc-2ccdf6249f9a",
        source: {
            //dash:        "https://www.streamlis.it/dash/<?php echo $stream_name; ?>/index.mpd",
            hls:        "https://www.streamlis.it/hls/<?php echo $stream_name; ?>.m3u8",
            labeling: {
                hls: {
                    qualities: function(quality) {
                        return quality.height + 'p';
                    }
                },
                dash: {
                    qualities: function(quality) {
                        return quality.height + 'p';
                    }
                }
            }
        },
        tweaks: {
            autoqualityswitching : false,
            max_buffer_level     : 10
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