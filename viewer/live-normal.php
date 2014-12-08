<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Eventi live</title>
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../include/session.js"></script>
<script src="../js/bootstrap.min.js"></script>

<script type="text/javascript">
$(document).ready(function()
{

$('.play-button').click(function (event){
 
    var url = $(this).attr("href");
    var windowName = "Player";//$(this).attr("name");
    var windowSpecs = 'width=640,height=480, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';
    
    window.open(url, windowName, windowSpecs);
    
    event.preventDefault();
    
    });

});

</script>
</head>


<body>
<?php include("header-normal.php"); ?>
<br/>

<h5 class="pull-right" style="margin-right: 3px;"><b><?= $mainactions->UserFullName(); ?></b>, bentornato! </h5>
<p><h4> La tua congregazione è <b><?= $mainactions->UserGroupName(); ?></b></h4></p>

<h2>ELENCO EVENTI LIVE:</h2>


<?php 

try
{
    $result = $dbactions->GetPublishersByViewer($mainactions->UserGroupId());

    if (!$result)
    {
	    error_log("No Results");
    }

    echo '<div class="container-fluid">';
    echo '<div class="panel-group" id="accordionMain">';

    while($row = mysql_fetch_array($result))
    {
	$publisher_id=$row['publisher_id'];
	$publisher_name=$row['publisher_name'];
	$publisher_code=$row['publisher_code'];

	$live_events = $dbactions->GetLiveEventsByPublisher($publisher_code);
	$live_events_number = mysql_num_rows($live_events);
	
	    echo '<div class="panel panel-primary">'.
		/*** PANEL HEADING ***/
		'<div class="panel-heading">'.
		    '<a data-toggle="collapse" data-parent="#accordionMain" href="#accordionLive_'.$publisher_id.'">'.
			'<h3 class="panel-title">'.
			    '<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
			    '<span><img src="../images/group.png" height="34" width="32"></span>'.
			    '<span> <b>'.$publisher_name.'</b> </span>  '.
			    '<span class="badge">'. $live_events_number .'</span>'.
			    '<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
			'</h3>'.
		    '</a>'.
		'</div>';
	
	    /*** PANEL BODY ***/
	    echo '<div id="accordionLive_'.$publisher_id.'" class="panel-collapse collapse">';
	    echo '<div class="panel-body">';
	    
	    if (!$live_events || $live_events_number<1)
	    {
		echo '<div style="margin: 0 0 0 10px">Nessun evento live disponibile per questa congregazione.</div>';
	    }
	    else
	    {
		while($row = mysql_fetch_array($live_events))
		{
		    $live_id=$row['live_id'];
		    $app_name=$row['app_name'];
		    $stream_name=$row['stream_name'];
		    $live_date=$row['live_date'];
		    $live_time=$row['live_time'];
		    $client_addr=$row['client_addr'];
		    $live_date_formatted = strftime("%A %d %B %Y", strtotime($row['live_date']));
		
		    $thumbnail_img = "../images/video_thumbnail.png";
		
		    echo '<div class="video_element_title">';
			$live_date_day = strftime("%u", strtotime($row['live_date']));
			if ( ($live_date_day) && ($live_date_day > 5))
			{
			    echo '<h4><b>ADUNANZA PUBBLICA - '.$live_date_formatted. '</b></h4>';    
			}
			elseif (($live_date_day) && ($live_date_day <= 5))
			{
			    echo '<h4><b>ADUNANZA DI SERVIZIO - '.$live_date_formatted. '</b></h4>';
			}
		    echo '</div>';
		    
		    echo '<ul class="video_element">';   
			echo '<li>';
			    echo '<div class="video_thumb">';
				echo '<img src="'.$thumbnail_img.'"/>';
			    echo '</div>';
			echo '</li>';
				    
			echo '<li>';
			    echo '<div class="video_info">';
				echo '<b>Path: </b>'.$app_name.'/'.$stream_name;
				echo '<br/>';
				echo '<b>Data di pubblicazione: </b>'.$live_date.' ore <b>'.$live_time.'</b>';
				echo '<br/>';
				echo '<b>Pubblicato da: </b>'.$client_addr;
			    echo '</div>';
			echo '</li>';
				    
			echo '<li>';
				echo '<div class="player_desktop">';
				    echo '<a class="play-button" href="../players/jwplayer/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
				    '<img class="video_imgdevice" src="../images/desktop.png"/></a>';
				    echo '<br/>';
				    echo "<label>Guarda il video con <br/>PC Desktop</label>";
				echo '</div>';
			    echo '</li>';
			    
			    echo '<li>';    
				echo '<div class="player_smartphone">';
				    echo '<a class="play-button" href="../players/flowplayer/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
				    '<img class="video_imgdevice" src="../images/os_android_old.png"/></a>';
				    echo '<br/>';
				    echo "<label>Guarda il video con <br/>device Android</label>";
				echo '</div>';
			    echo '</li>';
			    
			    echo '<li>';    
				echo '<div class="player_iphone">';
				    echo '<a class="play-button" href="../players/html5/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
				    '<img class="video_imgdevice" src="../images/os_apple_old.png"/></a>';
				    echo '<br/>';
				    echo "<label>Guarda il video con <br/>device Apple</label>";
				echo '</div>';
			echo '</li>';
		    echo '</ul>';	
		}
	    }
	    
	    echo '</div>';
	    echo '</div>';
	echo '</div>';
	    
    }
	
    echo '</div>';
    echo '</div>';
    
}
catch(Exception $e)
{
    echo 'No Results';
}

?>

</body>
</html>
