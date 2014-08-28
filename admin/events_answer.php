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
        $utils->RedirectToURL("../viewer/live-normal.php");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>

	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<title>JW LIS Streaming - Risposte</title>
	<link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
	<link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script language="JavaScript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script language="JavaScript" src="../js/jquery-flash-webcam-master/jquery.flashwebcam.js"></script>

</head>


<body>
<?php include("header.php"); ?>
<br/>
<div align="right" id='fg_membersite_content'>
Ciao <b><?= $mainactions->UserFullName(); ?></b></div>

<div id='fg_membersite_content'>
<p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b>.</p>
</div>

<h2>ELENCO EVENTI RISPOSTE:</h2>

<div id="webcam"></div>
    <div id="controls">
        <button id="play-btn">&#9658;</button>
        <button id="record-btn">&#9679;</button>
        <button id="stop-btn">&#9632;</button>
    </div>

<script>

$(document).ready(function() {
        $('#record-btn').attr('disabled', true);
        $('#stop-btn').attr('disabled', true);
        $('#play-btn').attr('disabled', false);
        $('#webcam').webcam({
            videoName: 'video-test',
            onConnected: function() {
                $('#record-btn').attr('disabled', false);
            },
            onDisconnected: function() {
                $('#record-btn').attr('disabled', true);
            },
            onRecording: function() {
                $('#record-btn').attr('disabled', true);
                $('#stop-btn').attr('disabled', false);
                $('#play-btn').attr('disabled', true);
            },
            onStop: function() {
                $('#record-btn').attr('disabled', false);
                $('#stop-btn').attr('disabled', true);
                $('#play-btn').attr('disabled', false);
            },
            onPlaying: function() {
                $('#record-btn').attr('disabled', true);
                $('#stop-btn').attr('disabled', false);
                $('#play-btn').attr('disabled', true);
            },
            onPlaybackEnded: function() {
                $('#record-btn').attr('disabled', false);
                $('#stop-btn').attr('disabled', true);
                $('#play-btn').attr('disabled', false);
            },
            width: 320,
            height: 240,
            serverUrl: 'rtmp://www.jwstream.org/flash'
        });

        $('#record-btn').click(function() {
            $.webcam.startRecording();
        });

        $('#stop-btn').click(function() {
            $.webcam.stopRecording();
        });

        $('#play-btn').click(function() {
            $.webcam.playRecording();
        });
    });
    
</script>
</body>
</html>
