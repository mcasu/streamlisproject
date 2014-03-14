
<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

$user_role = $fgmembersite->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
        $utils->RedirectToURL("../viewer/ondemand-normal.php");
}

try
{
        $result = $dbactions->GetGroups();

        if (!$result)
        {
                error_log("No Results");
        }

        $count=0;
        $group_array=Array();
        while($row = mysql_fetch_array($result))
        {
                $group_id=$row['group_id'];
                $group_name=$row['group_name'];
                $group_type=$row['group_type'];
                $group_role_name=$row['group_role_name'];
		$group_publish_code=$row['publish_code'];

                $group_array[$group_id]=Array();
                $group_array[$group_id]['group_id']=$row['group_id'];
                $group_array[$group_id]['group_name']=$row['group_name'];
                $group_array[$group_id]['group_type']=$row['group_type'];
                $group_array[$group_id]['group_role_name']=$row['group_role_name'];
		$group_array[$group_id]['publish_code']=$row['publish_code'];

                $count++;
        }
}
catch(Exception $e)
{
        echo 'No Results';
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>

	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<title>JW LIS Streaming - Eventi live</title>
	<link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
	<link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>

<script type="text/javascript">
$(document).ready(function()
{

$("a.event_ondemand_delete").click(function()
{
	var ondemand_id=$(this).parent().attr('id');
	par=$(this).parent();
	
	if (confirm("Vuoi davvero eliminare?"))
	{
	    $.post("event_delete.php",{type:"ondemand",event_id:ondemand_id,},

	    function(data,status)
	    {
		/*alert("Data: " + data + "\nStatus: " + status);*/
		    par.parent().fadeOut(1000, function()
		    {
			    par.parent().remove();
		    });
	    });
	}
});

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
<?php include("header.php"); ?>
<br/>
<div align="right" id='fg_membersite_content'>
Ciao <b><?= $fgmembersite->UserFullName(); ?></b></div>

<div id='fg_membersite_content'>
<p>La tua congregazione e' <b><?= $fgmembersite->UserGroupName(); ?></b>.</p>
</div>

<h2>ELENCO EVENTI ON-DEMAND:</h2>

<?php 


try
{
        foreach ($group_array AS $id => $row)
        {
                $group_id=$row['group_id'];
                $group_name=$row['group_name'];
                $group_type=$row['group_type'];
                $group_role_name=$row['group_role_name'];
		$group_publish_code=$row['publish_code'];

		if ($group_role_name=="publisher")
		{
		    $ondemand_events = $dbactions->GetOndemandEventsByPublisher($group_publish_code);
		    $ondemand_events_number = mysql_num_rows($ondemand_events);
			echo '<h2 class="toggle trigger">'.
				'<a href="#">'.$group_name.'<label align="right">'.$ondemand_events_number.'</label></a>'.
			     '</h2>';  /*<img class="group_logo" src="../images/group.png" height="30" width="32"/>*/
				
			echo '<div class="toggle_container" id="'.$group_id.'">';
			    if (!$ondemand_events || $ondemand_events_number<1)
			    {
				echo 'Nessun evento on-demand disponibile per questa congregazione.';
			    }
			    else
			    {
				echo '<div class="left">';
				    echo '<div id="fg_membersite_content">';
				    echo '<table class="imagetable">'.
					'<tr>'.
					'<th>ID EVENTO</th><th>APP</th><th>FILE</th><th>DURATA</th><th>BITRATE</th><th>CODEC</th><th>GUARDA IL VIDEO</th><th>AZIONI</th>'.
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
							echo '<td id="'.$ondemand_id.'"><a class="event_ondemand_delete" href="javascript:void()"/>'.
							'<img src="../images/delete.png" width="20"/></td>';
						echo '</tr>';	
					}
				echo '</table>';
				    echo '</div>';
				echo '</div>'; /* FINE DIV CLASS "left" */
			    }
		    echo '</div>'; /* FINE DIV CLASS "toggle_container" */
		}
        }
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>

</body>
</html>
