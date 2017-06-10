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
    <title>Baseline DASH-MPEG Player</title>
    <meta name="description" content="" />

    <!-- Minified Dash & Libraries -->
    <script src="dash.all.js"></script>

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
                url = "https://www.streamlis.it/dash/salerno_lis.mpd",
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
            player.setAutoPlay(false);

            player.attachSource(url);
        }
    </script>

    <style>
        video {
            width: 640px;
            height: 480px;
        }
    </style>

    <body onload="startVideo()">
        <div class="dash-video-player">
            <video controls="true"></video>
        </div>
    </body>
</html>