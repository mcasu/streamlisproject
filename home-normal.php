<?PHP
require_once("./include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("login.php");
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Home page</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">

	<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
        <script type="text/javascript">
	
		$(document).ready(function(){
			$('.play-button').click(function (event){
 
			var url = $(this).attr("href");
			var windowName = "popUp";//$(this).attr("name");
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
<h2>ELENCO EVENTI LIVE PER PUBLISHER:</h2>
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
                        $live_events = $dbactions->GetLiveEventsByPublisher($publisher_code);
			echo '<p><b>'. $publisher_name . '</b> (Code '.$publisher_code.')'.
                        '<img align="center" src="images/group.png" border="0" height="48" width="48"/>';

			if (!$live_events || mysql_num_rows($live_events)<1)
			{
				echo '</br>Nessun evento live disponibile per questa congregazione.';
			}
                                while($row = mysql_fetch_array($live_events))
                                {
	                                        $live_id=$row['live_id'];
	                                        $live_app=$row['app_name'];
	                                        $live_stream=$row['stream_name'];
					
						echo '<br/>Evento ID '.$live_id.' APP '.$live_app.' STREAM '.$live_stream.
						' <a class="play-button" href="jwplayer/play-live.php?app_name='.$live_app.'&stream_name='.$live_stream.'" target="_blank"><img align="center" src="images/play.png" width="36"/></a>';
						echo '<br/>';
                                }
                        echo '</p>'.
                                '<hr style="margin: 1em 0" />';
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
