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
      <title>JW LIS Streaming - On-demand</title>
      <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
	<link rel="STYLESHEET" type="text/css" href="../style/admin.css">

	<script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="../include/session.js"></script>
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

<h2>ELENCO EVENTI ON-DEMAND PER PUBLISHER:</h2>

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

		$ondemand_events = $dbactions->GetOndemandEventsByPublisher($publisher_code);
		$ondemand_events_number = mysql_num_rows($ondemand_events);
		echo '<h2 class="toggle trigger">'.
			'<a href="#">'.$publisher_name.
			'<img class= "group_logo" src="../images/group.png" />'.
			'<label class="eventnum" align="right">['.$ondemand_events_number.' eventi]</label></a>'.
		     '</h2>'; 
			
		echo '<div class="toggle_container" id="'.$publisher_id.'">';
		if (!$ondemand_events || $ondemand_events_number<1)
		{
		    echo '<div style="margin: 0 0 0 10px">Nessun evento on-demand disponibile per questa congregazione.</div>';
		}
		else
		{
		    echo '<div class="left">';
    
			while($row = mysql_fetch_array($ondemand_events))
			{
			    $ondemand_id=$row['ondemand_id'];
			    $ondemand_publish_code=$row['ondemand_publish_code'];
			    $ondemand_app_name=$row['ondemand_app_name'];
			    $ondemand_filename=$row['ondemand_filename'];
			    $duration_time = $utils->SecondsToTime($row['ondemand_movie_duration'],true);
			    $ondemand_movie_duration= $duration_time['h'] . " ore " . $duration_time['m'] . " minuti " . $duration_time['s'] . " secondi" ;
			    $ondemand_movie_bitrate=number_format($row['ondemand_movie_bitrate'],0,',','.') . " Kbps";
			    $ondemand_movie_codec=$row['ondemand_movie_codec'];
		    
			    $ondemand_mp4_filename = basename($ondemand_filename,".flv").".mp4";
			    
			    $thumbnail_img = '../images/thumbnails/'.basename($ondemand_filename,".flv").'.jpg';
						
				    if (!file_exists($thumbnail_img))
				    {
					$thumbnail_img = "../images/thumbnails/video_thumbnail.png";
				    }
				     echo '<ul class="video_element">';   
					    echo '<li>';
						echo '<div class="video_thumb">';
						    echo '<img src="'.$thumbnail_img.'"/>';
						echo '</div>';
					    echo '</li>';
					    
					    echo '<li>';
						echo '<div class="video_info">';
						    echo '<b>Nome video: </b>'.basename($ondemand_filename,".flv");
						    echo '<br/>';
						    echo '<b>Durata del video: </b>'.$ondemand_movie_duration;
						    echo '<br/>';
						    echo '<b>Bitrate: </b>'.$ondemand_movie_bitrate;
						    echo '<br/>';
						    echo '<b>Codec: </b>'.$ondemand_movie_codec;
						echo '</div>';
					    echo '</li>';
					    
					    echo '<li>';
						echo '<div class="player_desktop">';
						    echo '<a class="play-button" href="../players/jwplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'" target="_blank">'.
						    '<img src="../images/desktop.png"/></a>';
						    echo '<br/>';
						    echo "<label>Guarda il video con PC Desktop</label>";
						echo '</div>';
					    echo '</li>';
					    
					    echo '<li>';    
						echo '<div class="player_smartphone">';
						    echo '<a class="play-button" href="../players/flowplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'" target="_blank">'.
						    '<img src="../images/os_android.png"/></a>';
						    echo '<br/>';
						    echo "<label>Guarda il video con Smartphone Android</label>";
						echo '</div>';
					    echo '</li>';
					    
					    echo '<li>';    
						echo '<div class="player_iphone">';
						    echo '<a class="play-button" href="/mp4/'.$ondemand_mp4_filename.'" target="_blank">'.
						    '<img src="../images/os_apple.png"/></a>';
						    echo '<br/>';
						    echo "<label>Guarda il video con Apple Iphone</label>";
						echo '</div>';
					    echo '</li>';
				    echo '</ul>';
			}
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
