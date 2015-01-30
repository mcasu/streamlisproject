<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

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
    $("a.event_ondemand_delete").click(function()
    {
	    var ondemand_id=$(this).parent().attr('id');
	    
	    if (confirm("Vuoi davvero eliminare?"))
	    {
                var li_video_obj=$(this).parent().parent().parent().parent();
		$.post("event_delete.php",{type:"ondemand",event_id:ondemand_id},
    
		function(data,status)
		{
		    /*alert("Data: " + data + "\nStatus: " + status);*/
                    li_video_obj.fadeOut(1000);
		});
	    }
    });
    
    $("#btn_video_delete").click(function()
    {
        if (confirm("Vuoi davvero eliminare tutti i video selezionati?"))
	{
            var checkedItems = {}, counter = 0;
            $(".checked-list-box li.active").each(function(idx, li) {

                var ondemand_id=$(this).children(".video_element_title").first().attr('id');
                checkedItems[counter] = ondemand_id;
                counter++;
                
                //alert("Cancello elemento: " + ondemand_id);
                
                var li_video_obj = $(this);
                $.post("event_delete.php",{type:"ondemand",event_id:ondemand_id},
    
		function(data,status)
		{
		    //alert("Data: " + data + "\nStatus: " + status + "\nThis class: " + li_video_obj.attr('class'));
                    li_video_obj.fadeOut(1000);
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

    $('.panel-collapse').on('shown.bs.collapse', function () {
	//AddCheckBoxIconToList();
        OndemandMp4Loading();
    });
    
    $('.player_iphone').hide();
    
    $(".btn_actions").find(".btn").attr('disabled',true);
    
    //$("#btn_video_delete").prop('disabled', true);
    //$("#btn_video_archive").prop('disabled', true);
    
    var auto_refresh = setInterval(OndemandMp4Loading, 10000);


});


</script>
</head>


<body>
<?php include("header.php"); ?>
<br/>

<h5 class="pull-right" style="margin-right: 3px;"><b><?= $mainactions->UserFullName(); ?></b>, bentornato! </h5>
<p><h4> La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b></h4></p>

<?php 


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

    echo '<div class="container-fluid">';
    
    echo '<div class="panel panel-default">';

        echo '<div class="panel-heading">';
            //echo '<h3 style="display:inline; vertical-align:middle; margin-right:20px">ELENCO EVENTI ON-DEMAND</h3>';
            echo '<div class="pull-right btn_actions">';
                echo '<button type="button" class="btn btn-danger" style="margin-right:4px;" id="btn_video_delete">Elimina video</button>';
                echo '<button type="button" class="btn btn-primary" style="margin-right:4px;" id="btn_video_archive">Archivia video</button>';
            echo '</div>';
            echo '<div class="clearfix"></div>';
        echo '</div>';
        
        echo '<div class="panel-body">';
        
            echo '<div class="panel-group" id="accordionMain">';

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

                            echo '<div class="panel panel-primary">';

                                /// PANEL HEADING
                                echo '<div class="panel-heading">'.
                                    '<a class="title" data-toggle="collapse" data-parent="#accordionMain" href="#accordionOndemand_'.$group_id.'">'.
                                        '<h3 class="panel-title">'.
                                            '<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
                                            '<span><img src="../images/group.png" height="34" width="32"></span>'.
                                            '<span> <b>'.$group_name.'</b> </span>  '.
                                            '<span class="badge">'. $ondemand_events_number .'</span>'.
                                            '<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
                                        '</h3>'.
                                    '</a>'.
                                '</div>';


                                /// PANEL BODY
                                echo '<div id="accordionOndemand_'.$group_id.'" class="panel-collapse collapse">';
                                    echo '<div class="panel-body">';

                                        if (!$ondemand_events || $ondemand_events_number<1)
                                        {
                                            echo '<div style="margin: 0 0 0 10px">Nessun evento on-demand disponibile per questa congregazione.</div>';
                                        }
                                        else
                                        {
                                            echo '<ul class="list-group checked-list-box left" id="'.$group_publish_code.'">';

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
                                                        
                                                            echo '<div class="row video_element_title" id="'.$ondemand_id.'">';                                                            
                                                                                                                               
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
                                                                    echo '<h4 style="display:inline;"><b>ADUNANZA PUBBLICA - '.$ondemand_date. '</b></h4>';    
                                                                }
                                                                elseif (($ondemand_date_day) && ($ondemand_date_day <= 5))
                                                                {
                                                                    echo '<h4 style="display:inline;"><b>ADUNANZA DI SERVIZIO - '.$ondemand_date. '</b></h4>';
                                                                }
                                                            echo '</div>';

                                                            echo '<div class="row">';
                                                                
                                                                // CHECKBOX
                                                                echo '<div class="col-md-1 div-checkbox-align">';
                                                                    echo '<input type="checkbox" id="checkbox"/>';
                                                                echo '</div>';

                                                                // VIDEO THUMBNAIL + INFO + BUTTONS
                                                                echo '<div class="col-md-10 div-video-align">';
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
                                                                                    '<img class="video_imgdevice" src="../images/desktop.png"/></a>';
                                                                                echo '<br/>';
                                                                                echo "<label>Guarda il video con <br/>PC Desktop</label>";
                                                                                echo '<br/>';
                                                                                echo '<img class="video_imgos" src="../images/os_windows.png"/> <img class="video_imgos" src="../images/os_linux.png"/>';
                                                                            echo '</div>';
                                                                        echo '</li>';

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
                                                                    echo '</ul>';
                                                                echo '</div>';
                                                                
                                                                // TRASH BUTTON
                                                                echo '<div class="col-md-1 div-btn-actions-align">';
                                                                    echo '<button type="button" id="'.$ondemand_id.'" class="btn btn-default btn-lg video_delete">';
                                                                        echo '<a class="event_ondemand_delete">'.
                                                                            '<span class="glyphicon glyphicon-trash"></span>'.
                                                                        '</a>';
                                                                    echo '</button>';
                                                                echo '</div>';
                                                                
                                                            echo '</div>';

                                                        echo '</li>';
                                                }
                                            echo '</ul>'; /* FINE UL */
                                        }
                                    echo '</div>';
                                echo '</div>';

                            echo '</div>'; /* FINE DIV CLASS "panel-primary" */
                        }
                }
            echo '</div>';
            
        echo '</div>';
        
        echo '<div class="panel-footer">';
            echo '<div class="pull-right btn_actions">';
                echo '<button type="button" class="btn btn-danger" style="margin-right:4px;" id="btn_video_delete">Elimina video</button>';
                echo '<button type="button" class="btn btn-primary" style="margin-right:4px;" id="btn_video_archive">Archivia video</button>';
            echo '</div>';
            echo '<div class="clearfix"></div>';
        echo '</div>';
        
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