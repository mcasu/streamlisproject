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
      <title>Home page</title>
    <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

	<script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript">
	
		$(document).ready(function(){
		    $('.play-button').click(function (event){

		    var url = $(this).attr("href");
		    var windowName = "Player";//$(this).attr("name");
		    var windowSpecs = 'width=640,height=480, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';

		    window.open(url, windowName, windowSpecs);

		    event.preventDefault();

		    });
		    
		    $(".toggle_container").hide();

		    $("h2.trigger").css("cursor","pointer").toggle(function(){
			    $(this).addClass("active"); 
		      }, function () {
		      $(this).removeClass("active");
		      });
		    
		    $("h2.trigger").click(function()
		    {
			    $(this).next(".toggle_container").slideToggle("slow");
		    });
		});
	</script>
</head>
<body>
<?php include("header-normal.php"); ?>
<br/>
<div align="right"><b><?= $mainactions->UserFullName(); ?></b>, Benvenuto!</div>

<div>
<p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b>.</p>
</div>

<h2>ELENCO EVENTI LIVE PER PUBLISHER:</h2>

<?php

try
    {
	$publishers = $dbactions->GetPublishersByViewer($mainactions->UserGroupId());

        if (!$publishers)
        {
                error_log("No Results");
        }

	while($row = mysql_fetch_array($publishers))
        {
                $publisher_id=$row['publisher_id'];
                $publisher_name=$row['publisher_name'];
                $publisher_code=$row['publisher_code'];

		$live_events = $dbactions->GetLiveEventsByPublisher($publisher_code);
		$live_events_number = mysql_num_rows($live_events);
		echo '<h2 class="toggle trigger">'.
		    '<a href="#">'.$publisher_name.
		    '<img class= "group_logo" src="../images/group.png" />'.
		    '<label class="eventnum" align="right">['.$live_events_number.' eventi]</label></a>'.
		'</h2>';
		
		echo '<div class="toggle_container" id="'.$publisher_id.'">';
		    if (!$live_events || $live_events_number<1)
		    {
			echo '<div style="margin: 0 0 0 10px">Nessun evento live disponibile per questa congregazione.</div>';
		    }
		    else
		    {
			echo '<div class="left">';
			    echo '<div id="fg_membersite_content">';
			    echo '<table class="imagetable">'.
			    '<tr class="head">'.
			    '<th>ID EVENTO</th><th>DATA</th><th>ORA</th><th>APP</th><th>STREAM NAME</th><th>PUBBLICATO DA</th><th>GUARDA IL VIDEO</th>'.
			    '</tr>';
    
			    while($row = mysql_fetch_array($live_events))
			    {
				    $live_id=$row['live_id'];
				    $app_name=$row['app_name'];
				    $stream_name=$row['stream_name'];
				    $live_date=$row['live_date'];
				    $live_time=$row['live_time'];
				    $client_addr=$row['client_addr'];
			    
				    echo '<tr>';
					    echo '<td align="center">' . $live_id . '</td>';
					    echo '<td align="center">' . $live_date . '</td>';
					    echo '<td align="center">' . $live_time . '</td>';
					    echo '<td align="center">' . $app_name . '</td>';
					    echo '<td align="center">' . $stream_name . '</td>';
					    echo '<td align="center">' . $client_addr . '</td>';
					    echo '<td align="left">'.
						'<a class="play-button" href="../players/jwplayer/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
						'<button type="button"><img align="center" src="../images/jwplayer-logo.png" width="84" height="24"/></button></a>'.
						
						'<a class="play-button" href="../players/flowplayer/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
						'<button type="button"><img align="center" src="../images/flowplayer-logo.png" width="84" height="24"/></button></a>'.
						'</td>';
				    echo '</tr>';
			    }
			    echo '</table>';
			    echo '</div>';
			echo '</div>'; /* FINE DIV CLASS "left" */
		    }
		echo '</div>'; /* FINE DIV CLASS "toggle_container" */
        }
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>


<br><br><br>
</body>
</html>
