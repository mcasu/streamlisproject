<?PHP
require_once("../include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>Risposte</title>
    <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

	<script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui-1.10.4.custom.min.js"></script>
	<script language="JavaScript" src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>

	<script language="JavaScript" src="../js/scriptcam/scriptcam.js"></script>
	<script type="text/javascript">
	$(document).ready(function()
		{
		    var today = $.datepicker.formatDate('yymmdd', new Date()).toString();
		    //alert(today);
		    
		    var userid = $(".memberinfo").attr('id');
		    //alert(userid);
		    
		    $('#webcamStartButton').attr('disabled', true);
		    $('#webcamStopButton').attr('disabled', true);
		    
		    $("#webcam").scriptcam({
			path: '../js/scriptcam/',
			width: 640,
			height: 480,
			useMicrophone: false,
			showMicrophoneErrors: false,
			onError: oopsError,
			timeLeft: remaining,
			fileName: today + '_' + userid + '_answervideo',
	    	        connected: enableRecord,
			fileReady: fileReady,
		    });
		    
		    $("#loading-div-background").css({ opacity: 0.8 });
		    
		});
	
	function ShowWaitAnimation() {
           
            $("#loading-div-background").show();
        }
	
	function fileReady() {
	    $("#loading-div-background").hide();
	    //$("#webcam").show();
	    
	    $('#webcamStopButton').attr('disabled', true);
	    $('#webcamStartButton').attr('disabled', false);
	    
	    location.reload();
	}
	
	function oopsError(errorId,errorMsg) {
	    alert(errorMsg);
	}
	
	function remaining(value) {
	    $('#timeLeftForYou').text(value);
	}
	
	function enableRecord() {
	    $('#webcamStartButton').attr('disabled', false);
	    $('#timeLeftForYou').attr('disabled', true);
	    
	    alert(dateTime);
	}
	
	function start_recording(clicked) {
	    $('#webcamStartButton').attr('disabled', true);
	    $('#webcamStopButton').attr('disabled', false);
	    $('#timeLeftForYou').attr('disabled', false);
	    $.scriptcam.startRecording();
	}
	
	function stop_recording(clicked) {
	    $('#webcamStartButton').attr('disabled', false);
	    $('#timeLeftForYou').attr('disabled', true);
	    $.scriptcam.closeCamera();
	    
	    $('#webcamStopButton').attr('disabled', true);
	    //$("#webcam").hide();
	    ShowWaitAnimation();
	}
	</script>
</head>
<body>
<?php include("header-normal.php"); ?>
<br/>
<div class="memberinfo" id="<?= $mainactions->UserId(); ?>" align="right"><b><?= $mainactions->UserFullName(); ?></b>, Benvenuto!</div>

<div>
<p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b>.</p>
</div>

<h2>REGISTRA LE TUE RISPOSTE</h2>
    
<?php

try
    {
	$publishers = $dbactions->GetPublishersByViewer($mainactions->UserGroupId());

        if (!$publishers)
        {
                error_log("No Results");
        }
	
	echo '<div class="webcamButtons">';
	    echo '<input id="webcamStartButton" type="button" onclick="start_recording()" value="Start"/>';
	    echo '<input id="webcamStopButton" type="button" onclick="stop_recording()" value="Stop"/>';
	echo '</div>';
	
	echo '<div class="webcamLabels">';
	    echo '<label>Tempo rimasto: <label/>';
	    echo '<label id="timeLeftForYou"></label>';
	echo '</div>';
	
	echo '<div class="webcamPlugin" id="webcam"></div>';
	
	
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>

    <div id="loading-div-background">
	<div id="loading-div" class="ui-corner-all" >
	    <img style="height:36px;width:36px;margin:30px;" src="../images/please_wait.gif" alt="Loading.."/>
	    <h2 style="color:gray;font-weight:normal;">Please wait....</h2>
	</div>
    </div>

<br><br><br>
</body>
</html>