
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

<script type="text/javascript" src="../js/moment.min.js"></script>
<script type="text/javascript" src="../js/underscore.js"></script>
<script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="../include/session.js"></script>

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
		    par.parent().parent().fadeOut(1000, function()
		    {
			    par.parent().parent().remove();
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


$('.player_iphone').hide();

var root = $(this).find('h2.trigger');
var auto_refresh = setInterval(
function()
{
    //alert("Ciao pippo");
    root.each(function( index )
    {
	//alert( index + " GroupId: " + $(this).next('.toggle_container').attr('id'));
	if ( $(this).hasClass("active") )
	{
	    divleft = $(this).next('div.toggle_container').find('div.left');
	    var divleft_id = divleft.attr('id');
	    //alert("Oggetto: " + divleft_id);
	    	    
	    divleft.children().each(function( index )
	    {
		var iphoneobj = $(this).find(".player_iphone:first");
		var videoloadobj = $(this).find(".video_loading:first");
		var iphone_href = iphoneobj.children().first('a').attr('href');
		var iphone_id = iphoneobj.attr('id');
	
		if (iphoneobj.hasClass("active"))
		{
		    //alert(index + " già controllato e attivo: " + iphone_id);
		    return;
		}
		
		/************************************************************************
		 *** Controllo se esiste il file .mp4 nella cartella della congregazione
		 ***********************************************************************/
		var uri =  iphone_href.substr(4);
		var fullurl = 'http://' + document.location.hostname + '/mp4/' + divleft_id + uri;
		
		$.ajax({
		    url: fullurl,
		    type:'HEAD',
		    error:
			function(){
			    iphoneobj.removeClass("active");
			    iphoneobj.hide();
			    videoloadobj.hide();
			    return;
			    //alert("Url [ " + fullurl + " ] FAILED.");
			},
		    success:
			function(){
			    //alert("Url [ " + fullurl + " ] SUCCESS.");
			}
		});
		
		/**************************************************************************
		 *** Controllo se la richiesta http al link .mp4 risponde correttamente ***
		 *************************************************************************/
		link_url = "http://" + document.location.hostname + iphone_href;
		$.ajax({
		    url: link_url,
		    type:'HEAD',
		    error:
			function(){
			    iphoneobj.removeClass("active");
			    iphoneobj.hide();
			    videoloadobj.show();
			    //alert("Url [ " + link_url + " ] FAILED.");
			},
		    success:
			function(){
			    videoloadobj.hide();
			    iphoneobj.show();
			    iphoneobj.addClass("active");
			    //alert("Url [ " + link_url + " ] SUCCESS.");
			}
		});
		
		/*
		url = "http://" + document.location.hostname + iphone_href;
		var jqxhr = $.get(url, function() {})
		    .done(function()
		    {
			videoloadobj.hide();
			iphoneobj.show();
			iphoneobj.addClass("active");
			//alert( "GET [" + url + "] for [" + iphone_id + "] SUCCESS" );
		    })
		    .fail(function()
		    {
			iphoneobj.removeClass("active");
			iphoneobj.hide();
			videoloadobj.show();
			//alert( "GET [" + url + "] for [" + iphone_id + "] FAILED" );
		    });
		    */
		});
	}
    });
    
    //$('.player_iphone').load('ondemand_apple_div_load.php?url="' + $(this).attr('id') + '"').fadeIn("slow");
}, 10000);


});

</script>
</head>


<body>
<?php include("header.php"); ?>
<br/>
<div align="right" id='fg_membersite_content'>
Ciao <b><?= $mainactions->UserFullName(); ?></b></div>

<div id='fg_membersite_content'>
<p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b>.</p>
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
				'<a href="#">'.$group_name.
				'<img class= "group_logo" src="../images/group.png" />'.
				'<label class="eventnum">['.$ondemand_events_number.' eventi]</label></a>'.
			     '</h2>';
				
			echo '<div class="toggle_container" id="'.$group_id.'">';
			    if (!$ondemand_events || $ondemand_events_number<1)
			    {
				echo '<div style="margin: 0 0 0 10px">Nessun evento on-demand disponibile per questa congregazione.</div>';
			    }
			    else
			    {
				echo '<div class="left" id="'.$group_publish_code.'">';
				    	
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
							echo '<div id="'.$ondemand_id.'" class="video_delete">';
							    echo '<a class="event_ondemand_delete">'.
							    '<img src="../images/delete.png"/></a>';
							echo '</div>';
						    echo '</li>';
						    
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
							    echo '<div class="video_loading id="'.basename($ondemand_filename,".flv").'">';
								echo '<img src="../images/os_apple.png"/>';
								echo '<br/>';
								echo '<div id="block_1" class="barlittle"></div>
								<div id="block_2" class="barlittle"></div>
								<div id="block_3" class="barlittle"></div>
								<div id="block_4" class="barlittle"></div>
								<div id="block_5" class="barlittle"></div>';
								echo '<br/>';
								echo '<label>Creazione video per Apple in corso...</label>';
							    echo '</div>';
							    echo '<div class="player_iphone" id="'.basename($ondemand_filename,".flv").'">';
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
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>

</body>
</html>
