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
    <title>Stream LIS - Eventi On-Demand</title>
    
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script type="text/javascript" src="../include/functions.js"></script>
    <script src="../js/bootstrap.min.js"></script>

<script type="text/javascript">

$(document).ready(function()
{
    $('.play-button').click(function (event){
     
	var url = $(this).attr("href");
	var windowName = "Player";//$(this).attr("name");
	var windowSpecs = 'width=600,height=400, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';
	
	window.open(url, windowName, windowSpecs);
	
	event.preventDefault();
	
	});

    $('.panel-collapse').on('shown.bs.collapse', function () {
	OndemandMp4Loading();
    });
    
    $('.player_iphone').hide();
    
    var auto_refresh = setInterval(OndemandMp4Loading, 10000);


});

</script>

</head>


<body>
<?php include("../include/header_viewer.php"); ?>

<h2>ELENCO EVENTI ON-DEMAND:</h2>

<?php 


try
{
    $publishers = $dbactions->GetPublishersByViewer($mainactions->UserGroupId());

    if (!$publishers)
    {
	    error_log("ERROR - ondemand-normal.php GetPublishersByViewer() - " . $dbactions->GetErrorMessage());
    }

    echo '<div class="container-fluid">';
    echo '<div class="panel-group" id="accordionMain">';

    while($row = mysql_fetch_array($publishers))
    {
	$publisher_id=$row['publisher_id'];
	$publisher_name=$row['publisher_name'];
	$publisher_code=$row['publisher_code'];
	
	$ondemand_events = $dbactions->GetOndemandEventsByPublisher($publisher_code);
	$ondemand_events_number = mysql_num_rows($ondemand_events);
	
	    echo '<div class="panel panel-primary">'.
		/*** PANEL HEADING ***/
		'<div class="panel-heading">'.
		    '<a class="title" data-toggle="collapse" data-parent="#accordionMain" href="#accordionOndemand_'.$publisher_id.'">'.
			'<h3 class="panel-title">'.
			    '<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
			    '<span><img src="../images/group.png" height="34" width="32"></span>'.
			    '<span> <b>'.$publisher_name.'</b> </span>  '.
			    '<span class="badge">'. $ondemand_events_number .'</span>'.
			    '<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
			'</h3>'.
		    '</a>'.
		'</div>';
	
	
	    /*** PANEL BODY ***/
	    echo '<div id="accordionOndemand_'.$publisher_id.'" class="panel-collapse collapse">';
	    echo '<div class="panel-body">';
	    
		if (!$ondemand_events || $ondemand_events_number<1)
		{
		    echo '<div style="margin: 0 0 0 10px">Nessun evento on-demand disponibile per questa congregazione.</div>';
		}
		else
		{
		    echo '<ul class=" list-group left" id="'.$publisher_code.'">';
			    
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
				
				// Check if the database contains a correct date
				$date_parse = date_parse($row['ondemand_date']);
				if ($date_parse["error_count"] != 0 ||
				    !checkdate($date_parse["month"], $date_parse["day"], $date_parse["year"]))
				{
				    $ondemand_date = NULL;
				}
				
				$ondemand_onlydate = $ondemand_onlytime = NULL;
				if (!isset($ondemand_date) || is_null($ondemand_date))
				{
				    $path_parts = pathinfo($ondemand_filename);
				    
				    $strtoremove_lenght = strlen($ondemand_publish_code);
				    $ondemand_datetime = substr($path_parts['filename'], $strtoremove_lenght + 1);
				    
				    list($ondemand_onlydate, $ondemand_onlytime) = split("_", $ondemand_datetime);
				    
				    $ondemand_date = strftime("%A %d %B %Y", strtotime($ondemand_onlydate));
				}
				else
				{
				    //$ondemand_date = new DateTime($row['ondemand_date']);
				    //$ondemand_date = strftime("%A %d %B %Y %H:%M:%S", strtotime($row['ondemand_date']));
				    $ondemand_date = strftime("%A %d %B %Y", strtotime($row['ondemand_date']));
				}
				
				$ondemand_mp4_filename = basename($ondemand_filename,".flv").".mp4";
			
				$thumbnail_img = '../images/thumbnails/'.basename($ondemand_filename,".flv").'.jpg';
				
				if (!file_exists($thumbnail_img))
				{
				    $thumbnail_img = "../images/video_thumbnail.png";
				}
				
				echo '<li class="list-group-item video_list_element">';
				    
				    echo '<div class="video_element_title" id="'.$ondemand_id.'">';
					if (is_null($ondemand_onlydate))						
					{
					    $ondemand_date_day = strftime("%u", strtotime($row['ondemand_date']));
					}
					else
					{
					    $ondemand_date_day = strftime("%u", strtotime($ondemand_onlydate));
					}
					
					if ( ($ondemand_date_day) && ($ondemand_date_day > 5))
					{
					    echo '<h4><b>ADUNANZA PUBBLICA - '.$ondemand_date. '</b></h4>';    
					}
					elseif (($ondemand_date_day) && ($ondemand_date_day <= 5))
					{
					    echo '<h4><b>ADUNANZA DI SERVIZIO - '.$ondemand_date. '</b></h4>';
					}
				    echo '</div>';
				    
				    echo '<ul class="video_element">';
				     
				     /*
					echo '<li>';
					    echo '<div id="'.$ondemand_id.'" class="video_delete">';
						echo '<a class="event_ondemand_delete">'.
						'<img src="../images/delete.png"/></a>';
					    echo '</div>';
					echo '</li>';
				    */
				     
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
							'<img class="video_imgdevice" src="../images/desktop.png"/></a>';
						    echo '<br/>';
						    echo "<label>Guarda il video con <br/>PC Desktop</label>";
						    echo '<br/>';
						    echo '<img class="video_imgos" src="../images/os_windows.png"/> <img class="video_imgos" src="../images/os_linux.png"/>';
						echo '</div>';
					    echo '</li>';
					    /*
					    echo '<li>';    
						echo '<div class="player_smartphone">';
						    echo '<a class="play-button" href="../players/flowplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'" target="_blank">'.
						    '<img src="../images/os_android.png"/></a>';
						    echo '<br/>';
						    echo "<label>Guarda il video con Smartphone Android</label>";
						echo '</div>';
					    echo '</li>';
					    */
					    echo '<li>';
						echo '<div class="video_loading" id="'.basename($ondemand_filename,".flv").'">';
						    echo '<img class="video_imgdevice" src="../images/smartphone.png"/>';
						    echo '<br/>';
						    echo '<div id="block_1" class="barlittle"></div>
						    <div id="block_2" class="barlittle"></div>
						    <div id="block_3" class="barlittle"></div>
						    <div id="block_4" class="barlittle"></div>
						    <div id="block_5" class="barlittle"></div>';
						    echo '<br/>';
						    echo '<label>Creazione video per Tablet o Smartphone in corso...</label>';
						echo '</div>';
						
						echo '<div class="player_iphone" id="'.basename($ondemand_filename,".flv").'">';
						    echo '<a class="play-button" href="/mp4/'.$ondemand_mp4_filename.'" target="_blank">'.
						    '<img class="video_imgdevice" src="../images/smartphone.png"/></a>';
						    echo '<br/>';
						    echo "<label>Guarda il video con <br/>Tablet o Smartphone</label>";
						    echo '<br/>';
						    echo '<img class="video_imgos" src="../images/os_android.png"/> <img class="video_imgos" src="../images/os_apple.png"/>';
						echo '</div>';
					    echo '</li>';
                                            
                                            echo '<li>';
                                                // DOWNLOAD BUTTON
                                                echo '<div class="video_download">';
                                                        echo '<a role="button" class="btn btn-primary btn-lg event_ondemand_download" href="../include/download_file.php?file_path='.$ondemand_mp4_record_filepath.$ondemand_mp4_filename.'" target="_blank" download>';
                                                            echo '<span class="glyphicon glyphicon-download"></span>';
                                                        echo '</a>';
                                                    echo '<br/>';
                                                    echo "<label>Scarica il video</label>";
                                                echo '</div>';
                                            echo '</li>';
                                                                        
				    echo '</ul>';
				
				echo '</li>';
			}
		    echo '</ul>'; /* FINE UL */
		}
		echo '</div>';
		echo '</div>';
	echo '</div>'; /* FINE DIV CLASS "panel-primary" */
	
    }
	
}
catch(Exception $e)
{
    echo 'No Results';
}
    
echo '</div>';
echo '</div>';

?>

</body>
</html>