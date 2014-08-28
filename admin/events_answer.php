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
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

<!--    
    <script type="text/javascript" src="https://api.bistri.com/bistri.conference.min.js"></script>
    <script type="text/javascript" src="../js/bistri/conference.js"></script>-->

    <script src="//www.rtcmulticonnection.org/latest.js"></script>


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
</head>

<body>
<?php include("header.php"); ?>
<br/>


<button id="openNewSessionButton">Open New Room</button>

<script>

var connection = new RTCMultiConnection();

// easiest way to customize what you need!
connection.session = {
    audio: true,
    video: true
};

// on getting local or remote media stream
connection.onstream = function(e) {
    document.body.appendChild(e.mediaElement);
};

// setup signaling channel
connection.connect();

// open new session
document.querySelector('#openNewSessionButton').onclick = function() {
    connection.open();
};

</script>

<!--
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

</div>-->

</body>
</html>
