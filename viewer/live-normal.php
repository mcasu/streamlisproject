<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_viewer.php");
?>

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
    var windowSpecs = 'width=640, height=360, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';
    
    window.open(url, windowName, windowSpecs);
    
    event.preventDefault();
    
    });

});

</script>

    <script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"it","siteId":1168862,"cookiePolicyId":74934126,"banner":{"textColor":"#fff","backgroundColor":"#333"}}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script>
</head>


<body>
<?php include("../include/header_viewer.php"); ?>

<h2>ELENCO EVENTI LIVE:</h2>


<?php 

try
{
    $result = $dbactions->GetPublishersByViewer($mainactions->UserGroupId());

    if (!$result)
    {
	    error_log("ERROR - live-normal.php GetPublishersByViewer() - " . $dbactions->GetErrorMessage());
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
                                    '<div class="img_play_container">'.
                                        '<img class="video_imgdevice" src="../images/desktop.png"/>'.
                                        '<div class="playbutton_overlay"></div>'.
                                    '</div>'.
                                    '</a>';
                                    echo '<br/>';
                                    echo "<label>Guarda il video con <br/>device o PC Windows</label>";
                                    echo '<br/>';
                                    echo '<img class="video_imgos" src="../images/os_linux.png"/> <img class="video_imgos" src="../images/os_windows.png"/>';
				echo '</div>';
			    echo '</li>';
			    
                        echo '<li>';    
                            echo '<div class="player_smartphone">';
                                echo '<a class="play-button" href="../players/dashplayer/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'&stream_type=hls" target="_blank">'.
                                '<div class="img_play_container">'.
                                    '<img class="video_imgdevice" src="../images/smartphone.png"/>'.
                                    '<div class="playbutton_overlay"></div>'.
                                '</div>'.
                                '</a>';
                                echo '<br/>';
                                echo "<label>Guarda il video con device <br/>Android o Apple iOS</label>";
                                echo '<br/>';
                                echo '<img class="video_imgos" src="../images/os_android.png"/> <img class="video_imgos" src="../images/os_apple.png"/>';
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

    <?php include("../include/footer.php"); ?>
</body>
</html>
