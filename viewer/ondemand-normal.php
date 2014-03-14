<?PHP
require_once("../include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
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

	<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
        <script type="text/javascript">
	
		$(document).ready(function(){
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

<div align="right"><b><?= $fgmembersite->UserFullName(); ?></b>, Welcome back!</div>
<div id='fg_membersite_content'>

<div>
<p>La tua congregazione e' <b><?= $fgmembersite->UserGroupName(); ?></b>.</p>
</div>

<hr margin="0"/>
<h2>ELENCO EVENTI ON-DEMAND PER PUBLISHER:</h2>
<hr margin="0"/>

<?php

try
    {
	$publishers = $dbactions->GetPublishersByViewer($fgmembersite->UserGroupId());

        if (!$publishers)
        {
                error_log("No Results");
        }

	while($row = mysql_fetch_array($publishers))
        {
                $publisher_id=$row['publisher_id'];
                $publisher_name=$row['publisher_name'];
                $publisher_type=$row['group_type'];
                $publisher_role_name=$row['group_role_name'];
                $publisher_code=$row['publish_code'];

                if ($publisher_role_name=="publisher")
                {
                        $ondemand_events = $dbactions->GetOndemandEventsByPublisher($publisher_code);
			echo '<p><b>'. $publisher_name . '</b> '.
                        '<img align="center" src="../images/group.png" border="0" height="48" width="48"/>';

			if (!$ondemand_events || mysql_num_rows($ondemand_events)<1)
			{
				echo '</br>Nessun evento on-demand disponibile per questa congregazione.';
			}
			else
			{
				echo '<table class="imagetable">'.
				'<tr>'.
				'<th>ID EVENTO</th><th>APP</th><th>FILE</th><th>DURATA</th><th>BITRATE</th><th>CODEC</th><th>GUARDA IL VIDEO</th>'.
				'</tr>';

                                while($row = mysql_fetch_array($ondemand_events))
                                {
	                        	$ondemand_id=$row['ondemand_id'];
	                                $ondemand_publish_code=$row['ondemand_publish_code'];
	                                $ondemand_app_name=$row['ondemand_app_name'];
	                                $ondemand_filename=$row['ondemand_filename'];
	                                $ondemand_movie_duration=$row['ondemand_movie_duration'];
	                                $ondemand_movie_bitrate=$row['ondemand_movie_bitrate'];
	                                $ondemand_movie_codec=$row['ondemand_movie_codec'];
				
					echo '<tr>';
			                        echo '<td align="center">' . $ondemand_id . '</td>';
			                        echo '<td align="center">' . $ondemand_app_name . '</td>';
			                        echo '<td align="center">' . $ondemand_filename . '</td>';
			                        echo '<td align="center">' . $ondemand_movie_duration . '</td>';
			                        echo '<td align="center">' . $ondemand_movie_bitrate . '</td>';
			                        echo '<td align="center">' . $ondemand_movie_codec . '</td>';
			                        echo '<td align="left">'.
						'<a class="play-button" href="../players/jwplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'" target="_blank">'.
						'<button type="button"><img align="center" src="../images/jwplayer-logo.png" width="86" height="24"/></button></a>'.
						'<a class="play-button" href="../players/flowplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'" target="_blank">'.
						'<button type="button"><img align="center" src="../images/flowplayer-logo.png" width="86" height="24"/></button></a>'.
						'</td>';
				        echo '</tr>';	
                                }
			echo '</table>';
                        echo '</p>';
			}
                        echo '<hr style="margin: 1em 0" />';
                }
        }
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>


<br><br><br>
</div>
</body>
</html>
