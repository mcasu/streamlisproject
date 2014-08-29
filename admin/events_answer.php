<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

$user_role = $mainactions->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
        $utils->RedirectToURL("../viewer/ondemand-normal.php");
}

?>

<!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>Risposte</title>
    
    <!--<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">-->
    
    <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>


    <!--
    <script type="text/javascript" src="https://api.bistri.com/bistri.conference.min.js"></script>
    <script type="text/javascript" src="../js/bistri/conference.js"></script>

    <style>
	.container
	{
	    margin-top: 50px;
	    width: 400px;
	    }
	    .pane {
	    margin: 20px;
	    text-align: center;
	    }
	    video {
	    width: 100%;
	}
    </style>
    -->
    
</head>

<body>
<?php include("header.php"); ?>
<br/>

<script>

    // Peer server's address.
    var serverAddress='http://www.jwstream.org:8095/webrtc';


    var p2p=new Woogeen.Peer({
	iceServers : [{
	    urls : "stun: www.jwstream.org"
	} ]
    });

// Initialize a Peer object with stun server
var roomToken=JSON.stringify({host:serverAddress, id:Utils.getQueryStrings()['roomId']}); // Tokens for join a room.

// It only initializes a Woogeen.Stream object. Using localStream.init() to initialize stream.
var localStream = Woogeen.Stream({video:true}); 
localStream.addEventListener("access-accepted", function(evt)
{
    // access-accepted event will be triggered when user accepted to use camera/microphone
    $('#local video').get(0).src = URL.createObjectURL(localStream.stream);

    // Show local stream
    p2p.joinRoom(roomToken, localStream);

    // Join a chat room.
});

$(document).ready(function()
{
    $('#login').click(function(){
	p2p.connect(serverAddress,$('#uid').val());

	// Connect to peer server.
	$('#uid').prop('disabled',true);
    });
    
    $('#connect').click(function()
    {
	// Initialize local stream.
	localStream.init();
    });

    $('#logoff').click(function()
    {
	p2p.leaveRoom(roomToken);
	// Quit current chat room.
	$('#uid').prop('disabled',false);
    });

});

p2p.addEventListener('stream-subscribed',function(e)
{
    // A remote stream is available.
    $('#remote video').show();
    
    // Show remote stream
    $('#remote video').get(0).src = URL.createObjectURL(e.stream.stream); 
});

p2p.addEventListener('chat-stopped',function(e)
{
    // Chat stopped
    $('#remote video').hide();
});

p2p.addEventListener('chat-started',function(e)
{
    // Chat started
    console.log('Video chat is started.');
});

</script>

<!---
<div class="container">
    <div class="pane" id="pane_0">
        <img src="http://static.tumblr.com/uzwqx7a/8VIm8jofz/logo.png">
    </div>

    <div class="pane" id="pane_1" style="display: none">
	<input type="text" placeholder="Conference Name" id="room_field" class="form-control"><br>
	<input type="button" value="Join Conference Room" id="join" class="btn btn-info btn-default btn-block">
    </div>
    
    <div class="pane" id="pane_2" style="display: none">
	<div class="myvideo" id="video_container"></div>
	<input type="button" value="Quit Conference Room" id="quit" class="btn btn-danger btn-default btn-block">
    </div>

</div>
-->

</body>
</html>
