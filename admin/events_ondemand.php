<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_admin.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Eventi On-Demand</title>
    
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel="stylesheet" href="../style/jquery-ui.min.css"/>
    <link rel='stylesheet' href='../style/admin.css' />

    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script type="text/javascript" src="../include/functions.js"></script>
    

<script type="text/javascript">

$(document).ready(function()
{
    $("a.event_ondemand_delete").click(function()
    {
	    var ondemand_id=$(this).attr('id');
	    
	    if (confirm("Vuoi davvero eliminare?"))
	    {
                var li_video_obj=$(this).parent().parent().parent();
		$.post("event_delete.php",{type:"ondemand",event_id:ondemand_id},
    
		function(data,status)
		{
		    /*alert("Data: " + data + "\nStatus: " + status);*/
                    li_video_obj.fadeOut(1000);
		});
	    }
    });
    
    $("li.video_list_element").click(function(e)
    {
        //alert("TARGET ID: " + e.target.id + " TARGET CLASS: " + $(e.target).attr("class"));
        if (e.target.id === "video_checkbox" 
                || $(e.target).attr("class") === "video_imgdevice"
                || $(e.target).attr("class").indexOf("event_ondemand_download") >= 0
                || $(e.target).attr("class").indexOf("glyphicon-download") >= 0
                || $(e.target).attr("class").indexOf("event_ondemand_delete") >= 0
                || $(e.target).attr("class").indexOf("glyphicon-trash") >= 0)
        {
            return true;
        }
        
        var checkbox_obj = $(this).find("div.div-checkbox-align");
            
        if ($(this).hasClass("active"))
        {
            //alert("Voce attiva: " + checkbox_obj.children("input.video_checkbox").attr("class"));
            checkbox_obj.children("input.video_checkbox").prop("checked", false);
            $(this).removeClass("active");
        }
        else
        {
            //alert("Voce NON attiva: " + checkbox_obj.children("input.video_checkbox").attr("class"));
            checkbox_obj.children("input.video_checkbox").prop("checked", true);
            $(this).addClass("active");
        }
        
        var checkedItems = {}, counter = 0;
        $(".checked-list-box li.active").each(function(idx, li) {
            //alert("Ho trovato un check attivo.");
            checkedItems[counter] = $(li).text();
            counter++;
        });

        //alert ("Check attivi: " + counter);
        if (counter !== 0)
        {
            $(".btn_actions").find(".btn").attr('disabled',false);
            
            if (counter < 2)
            {
                $(".btn_video_join").attr('disabled',true);
            }            
        }
        else
        {
            $(".btn_actions").find(".btn").attr('disabled',true);
        }
        
        e.stopImmediatePropagation();
    });
    
    $(".video_checkbox").change(function()
    {
        //alert("Hai cambiato la checkbox.");
        
        if($(this).prop("checked"))
        {
            //call the function to be fired
            //when your box changes from
            //unchecked to checked
            $(this).parent().parent().parent().addClass("active");
        }
        else
        {
            //call the function to be fired
            //when your box changes from
            //checked to unchecked
            $(this).parent().parent().parent().removeClass("active");
        }
        
        var checkedItems = {}, counter = 0;
        $(".checked-list-box li.active").each(function(idx, li) {
            //alert("Ho trovato un check attivo.");
            checkedItems[counter] = $(li).text();
            counter++;
        });

        //alert ("Check attivi: " + counter);
        if (counter !== 0)
        {
            $(".btn_actions").find(".btn").attr('disabled',false);
            
            if (counter < 2)
            {
                $(".btn_video_join").attr('disabled',true);
            }            
        }
        else
        {
            $(".btn_actions").find(".btn").attr('disabled',true);
        }
        
    });
    
    $(".btn_video_delete").click(function()
    {
        if (confirm("Vuoi davvero eliminare tutti i video selezionati?"))
	{
            var checkedItems = {}, counter = 0;
            $(".checked-list-box li.active").each(function(idx, li) {

                var ondemand_id=$(this).attr('id');
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
    
    $(".alert-warning").hide();
    $(".alert-success").hide();
    $(".alert-danger").hide();
    $( "#ondemand-convert-dialog-confirm" ).hide();
    
    $(".btn_video_convert").click(function()
    {
        var checkedItems = [];
        $(".checked-list-box li.active").each(function(idx, li) {

            var ondemand_id=$(this).attr('id');
            checkedItems.push(ondemand_id);
        });
            
        var ondemandIdList = checkedItems.toString();
        var userId = $('.userid').attr('id');
        
        $( "#ondemand-convert-dialog-confirm" ).dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Converti subito": function() {
                  $( this ).dialog( "close" );
                },
                "Converti la prossima notte": function() 
                {
                    var result = MarkOndemandVideoToConvert(ondemandIdList, userId);

                    //alert("RISULTATO: " + result);

                    if (result === "2")
                    {
                        $(".alert-warning").show();
                        $(".alert-success").hide();
                        $(".alert-danger").hide();
                        $(".alert-warning").html('<button type="button" class="close" data-dismiss="alert">' +
                                            '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                                            '<h4 style="margin-top: 2px;"><b>OPERAZIONE NON PERMESSA!</b>\nMi dispiace. Uno o più video selezionati sono già schedulati per la conversione.</h4>');

                    }
                    else if (result === "1")
                    {
                        $(".alert-danger").show();    
                        $(".alert-warning").hide();
                        $(".alert-success").hide();
                        $(".alert-danger").html('<button type="button" class="close" data-dismiss="alert">' +
                                    '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                                    '<h4 style="margin-top: 2px;"><b>OPERAZIONE FALLITA!</b>\nMi dispiace. Non sono riuscito a memorizzare le informazioni per convertire i video selezionati.</h4>');

                    }
                    else if (result === "0")
                    {
                        $(".alert-success").show();
                        $(".alert-danger").hide();    
                        $(".alert-warning").hide();
                        $(".alert-success").html('<button type="button" class="close" data-dismiss="alert">' +
                                    '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                                    '<h4 style="margin-top: 2px;"><b>OPERAZIONE RIUSCITA!</b>\nI video selezionati saranno convertiti questa notte e potrai vedere il risultato domani.</h4>');                

                    }
                    
                $( this ).dialog( "close" );
                }
            }
        });
    });     
    
    $(".btn_video_join").click(function()
    {
        var checkedItems = [];
        $(".checked-list-box li.active").each(function(idx, li) {

            var ondemand_id=$(this).attr('id');
            checkedItems.push(ondemand_id);
        });
            
        var ondemandIdList = checkedItems.toString();
        var userId = $('.userid').attr('id');
        
        if (confirm("Vuoi davvero unire i video selezionati? [" + ondemandIdList + "]"))
	{   
            var result = MarkOndemandVideoToJoin(ondemandIdList, userId);
            
            //alert("RISULTATO: " + result);
            
            if (result === "2")
            {
                $(".alert-warning").show();
                $(".alert-success").hide();
                $(".alert-danger").hide();
                $(".alert-warning").html('<button type="button" class="close" data-dismiss="alert">' +
                                    '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                                    '<h4 style="margin-top: 2px;"><b>OPERAZIONE NON PERMESSA!</b>\nMi dispiace. Uno o più video sono già stati selezionati per fare il join.</h4>');
                
            }
            else if (result === "1")
            {
                $(".alert-danger").show();    
                $(".alert-warning").hide();
                $(".alert-success").hide();
                $(".alert-danger").html('<button type="button" class="close" data-dismiss="alert">' +
                            '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                            '<h4 style="margin-top: 2px;"><b>OPERAZIONE FALLITA!</b>\nMi dispiace. Non sono riuscito a memorizzare le informazioni per unire i video selezionati.</h4>');
                
            }
            else if (result === "0")
            {
                $(".alert-success").show();
                $(".alert-danger").hide();    
                $(".alert-warning").hide();
                $(".alert-success").html('<button type="button" class="close" data-dismiss="alert">' +
                            '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
                            '<h4 style="margin-top: 2px;"><b>OPERAZIONE RIUSCITA!</b>\nI video selezionati saranno uniti questa notte e potrai vedere il risultato domani.</h4>');                
                
            }
        }
    });    
    
    $('.play-button').click(function (event){
     
	var url = $(this).attr("href");
	var windowName = "Player";//$(this).attr("name");
	//var windowSpecs = 'width=640,height=480, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';
        var windowSpecs = 'width=640, height=360, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';
	
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
<?php include("../include/header_admin.php"); ?>

<?php 

try
{
        echo '<input type="hidden" class="userid" id="' . $mainactions->UserId() .'"/>';
        
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
                echo '<button type="button" class="btn btn-danger btn_video_delete" style="margin-right:4px;" id="btn_video_delete">Elimina video</button>';
                echo '<button type="button" class="btn btn-primary btn_video_join" style="margin-right:4px;" id="btn_video_join">Unisci video</button>';
                echo '<button type="button" class="btn btn-primary btn_video_convert" style="margin-right:4px;" id="btn_video_convert">Converti video</button>';
            echo '</div>';
            echo '<div class="clearfix"></div>';
        echo '</div>';
        
        echo '<div class="panel-body">';
        
            echo '<div class="alert alert-success alert-dismissible" role="alert"></div>';
            echo '<div class="alert alert-warning alert-dismissible" role="alert"></div>';
            echo '<div class="alert alert-danger alert-dismissible" role="alert"></div>';
            
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
                                            echo '<div class="container-fluid" style="overflow:auto">';
                                            echo '<ul class="list-group checked-list-box" id="'.$group_publish_code.'">';

                                                while($row = mysql_fetch_array($ondemand_events))
                                                {
                                                        $ondemand_id = $row['ondemand_id'];
                                                        $ondemand_publish_code = $row['ondemand_publish_code'];
                                                        $ondemand_app_name = $row['ondemand_app_name'];
                                                        $ondemand_filename = $row['ondemand_filename'];
                                                        $ondemand_filesize = $row['ondemand_filesize'] ? round((float)$row['ondemand_filesize'], 2)." MB" : "N/A";
                                                        $duration_time = $utils->SecondsToTime($row['ondemand_movie_duration'],true);
                                                        $ondemand_movie_duration = $duration_time['h'] . " ore " . $duration_time['m'] . " minuti " . $duration_time['s'] . " secondi" ;
                                                        $ondemand_movie_bitrate = number_format($row['ondemand_movie_bitrate'],0,',','.') . " Kbps";
                                                        $ondemand_movie_framerate = $row['ondemand_movie_framerate'] ? $row['ondemand_movie_framerate']." fps" : "N/A";
                                                        $ondemand_movie_res = $row['ondemand_movie_res'] ? $row['ondemand_movie_res'] : "N/A";
                                                        $ondemand_movie_codec = $row['ondemand_movie_codec'];

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

                                                        echo '<li class="list-group-item video_list_element" style="overflow:auto" id="'.$ondemand_id.'">';
                                                        
                                                            echo '<div class="row video_element_title">';                                                            
                                                                                                                               
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
                                                                    echo '<input type="checkbox" class="video_checkbox" id="video_checkbox"/>';
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
                                                                                echo '<b>Durata del video: </b>'.$ondemand_movie_duration . ' ('.$ondemand_filesize.')';
                                                                                echo '<br/>';
                                                                                echo '<b>Risoluzione: </b>'.$ondemand_movie_res.' <b>Framerate: </b>'.$ondemand_movie_framerate;
                                                                                echo '<br/>';
                                                                                echo '<b>Codifica: </b>'.$ondemand_movie_codec;
                                                                            echo '</div>';
                                                                        echo '</li>';

                                                                        echo '<li>';
                                                                            echo '<div class="player_desktop">';
                                                                                echo '<a class="play-button" href="../players/jwplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'&filetype=flv" target="_blank">'.
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
                                                                                echo '<label>Creazione video per <br/>Tablet o Smartphone in corso...</label>';
                                                                                echo '<br/>';
                                                                                echo '<div class="container-fluid" style="text-align:center;">';
                                                                                echo '<div id="block_1" class="barlittle"></div>
                                                                                    <div id="block_2" class="barlittle"></div>
                                                                                    <div id="block_3" class="barlittle"></div>
                                                                                    <div id="block_4" class="barlittle"></div>
                                                                                    <div id="block_5" class="barlittle"></div>';
                                                                                echo '</div>';
                                                                            echo '</div>';

                                                                            echo '<div class="player_iphone" id="'.basename($ondemand_filename,".flv").'">';
                                                                                echo '<a class="play-button" href="../players/jwplayer/play-vod.php?stream_name='.$ondemand_publish_code.'&filename='.$ondemand_filename.'&filetype=mp4" target="_blank">'.
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
                                                                echo '</div>';
                                                                
                                                                // TRASH BUTTON
                                                                echo '<div class="col-md-1 div-btn-actions-align">';
                                                                        echo '<a id="'.$ondemand_id.'" role="button" class="btn btn-danger btn-lg event_ondemand_delete">'.
                                                                            '<span class="glyphicon glyphicon-trash"></span>';
                                                                        echo '</a>';
                                                                echo '</div>';
                                                                
                                                            echo '</div>';

                                                        echo '</li>';
                                                }
                                            echo '</ul>'; /* FINE UL */
                                            echo '</div>';
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
                echo '<button type="button" class="btn btn-danger btn_video_delete" style="margin-right:4px;" id="btn_video_delete">Elimina video</button>';
                echo '<button type="button" class="btn btn-primary btn_video_join" style="margin-right:4px;" id="btn_video_join">Unisci video</button>';
                echo '<button type="button" class="btn btn-primary btn_video_convert" style="margin-right:4px;" id="btn_video_convert">Converti video</button>';
            echo '</div>';
            echo '<div class="clearfix"></div>';
        echo '</div>';
        
    echo '</div>';
    echo '</div>';
    }
    catch(Exception $e)
    {
        error_log('ERROR - Admin events_ondemand.php - '.$e->getMessage());
    }

?>

<div id="ondemand-convert-dialog-confirm" title="Come vuoi convertire i video selezionati?">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Vuoi convertire subito i video o schedulare l'operazione la prossima notte?</p>
</div>
    
</body>
</html>