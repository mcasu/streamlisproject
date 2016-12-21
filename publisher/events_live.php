<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_publisher.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Eventi live</title>
    <link rel="stylesheet" href="../style/jquery-ui.min.css"/>
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/session.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.16/clipboard.min.js"></script>

</head>


<body>
<?php include("../include/header_publisher.php"); ?>

<h2>ELENCO EVENTI LIVE:</h2>
<br/>

<?php 

try
{
    echo '<div class="container-fluid">';
    echo '<div class="panel-group" id="accordionMain">';

    echo '<input type="hidden" class="players_counter_info"/>';
    
    
    $publish_code = $dbactions->GetPublishCodeByGroupId($mainactions->UserGroupId());
    $live_events = $dbactions->GetLiveEventsByPublisher($publish_code);
    $live_events_number = mysql_num_rows($live_events);

    echo '<div class="panel panel-primary">'.
        /*** PANEL HEADING ***/
        '<div class="panel-heading">'.
            '<a data-toggle="collapse" data-parent="#accordionMain" href="#accordionLive_'.$mainactions->UserGroupId().'">'.
                '<h3 class="panel-title">'.
                    '<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
                    '<span><img src="../images/group.png" height="34" width="32"></span>'.
                    '<span> <b>'.$mainactions->UserGroupName().'</b> </span>  '.
                    '<span class="badge">'. $live_events_number .'</span>'.
                    '<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
                '</h3>'.
            '</a>'.
        '</div>';

    /*** PANEL BODY ***/
    echo '<div id="accordionLive_'.$mainactions->UserGroupId().'" class="panel-collapse collapse">';
    echo '<div class="panel-body">';

        echo '<div class="container-fluid" style="overflow:auto">';
        if (!$live_events || $live_events_number < 1)
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

                echo '<div class="row video_list_element" id=' . $live_id . '>';

                    // VIDEO THUMBNAIL + INFO + BUTTONS
                    echo '<div class="col-md-10 div-video-align">';
                        echo '<ul class="video_element" id='.$stream_name.'>';   
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
                                        echo '<br/>';
                                        echo '<button type="button" class="btn btn-primary btn_live_view_link"><span class="glyphicon glyphicon-link"></span> Link</button>';
                                    echo '</div>';
                                echo '</li>';

                                echo '<li>';    
                                    echo '<div class="player_smartphone">';
                                        echo '<a class="play-button" href="../players/flowplayer/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
                                        '<img class="video_imgdevice" src="../images/os_android_old.png"/></a>';
                                        echo '<br/>';
                                        echo "<label>Guarda il video con <br/>device Android</label>";
                                        echo '<br/>';
                                        echo '<button type="button" class="btn btn-primary btn_live_view_link"><span class="glyphicon glyphicon-link"></span> Link</button>';
                                    echo '</div>';
                                echo '</li>';

                                echo '<li>';    
                                    echo '<div class="player_iphone">';
                                        echo '<a class="play-button" href="../players/html5/play-live.php?app_name='.$app_name.'&stream_name='.$stream_name.'" target="_blank">'.
                                        '<img class="video_imgdevice" src="../images/os_apple_old.png"/></a>';
                                        echo '<br/>';
                                        echo "<label>Guarda il video con <br/>device Apple</label>";
                                        echo '<br/>';
                                        echo '<button type="button" class="btn btn-primary btn_live_view_link"><span class="glyphicon glyphicon-link"></span> Link</button>';
                                    echo '</div>';
                            echo '</li>';
                        echo '</ul>';	
                    echo '</div>';

                    // STATUS BUTTON
                    echo '<div class="col-md-2 div-btn-status-align">';
                        echo '<button type="button" class="btn btn-primary players_counter_refresh">Aggiorna</button><br/>';
                        echo '<label style="margin-top:2px;">Utenti che stanno guardando <br/>questa adunanza:</label><br/>';
                        echo '<span class="badge players_counter"/>';
                    echo '</div>';

                echo '</div>';
            }
        }
        echo '</div>';
    echo '</div>';
    echo '</div>';
echo '</div>';
		
        
	
    echo '</div>';
    
    echo '<div id="divEventsLiveViewLink">';
            echo '<br/>';
            echo '<input id="inputEventsLiveViewLink" class="default-cursor" readonly>';
            echo '<button class="btn" data-clipboard-target="#inputEventsLiveViewLink">';
                echo '<span class="glyphicon glyphicon-copy"></span>';
            echo '</button>';
            //echo '<br/>';
            //echo '<div class="alert alert-success" role="alert">LINK COPIATO!</div>';
    echo '</div>';
        
echo '</div>';

}
catch(Exception $e)
{
    error_log('ERROR - Publisher events_live.php - '.$e->getMessage());
}

?>

</body>
    
<script type="text/javascript">
$(document).ready(function()
{

    $('.play-button').click(function (event)
    {

        var url = $(this).attr("href");
        var windowName = "Player";//$(this).attr("name");
        var windowSpecs = 'width=600,height=440, scrollbars=yes, resizable=yes, status=no, toolbar=no, menubar=no, location=no';

        window.open(url, windowName, windowSpecs);

        event.preventDefault();

    });

    $(".panel-collapse").on('show.bs.collapse', function()
    {
        var stream_name = $(this).find("ul.video_element").attr('id');
        
        $(".players_counter_info").attr('id', stream_name);
        
        var players_counter_obj = $(this).find("span.players_counter");
        players_counter_obj.load('/include/functions.php?fname=get_current_live_players_number&streamName=' + $(".players_counter_info").attr('id'));
    });
    
    $(".players_counter_refresh").click(function()
    {
        $(this).prop("disabled", true);
        
        var players_counter_obj = $(this).parent().find("span.players_counter");
        //alert("CLASS: " + players_counter_obj.attr('class'));
        players_counter_obj.load('/include/functions.php?fname=get_current_live_players_number&streamName=' + $(".players_counter_info").attr('id'));
        
        $(this).prop("disabled", false);

    });
    
    $("#divEventsLiveViewLink").hide();
    $(".btn_live_view_link").click(function(e)
    {
        e.preventDefault();
        
        var liveViewLinkDlg = $('#divEventsLiveViewLink').dialog({
            title: 'Live link',
            resizable: true,
            autoOpen:false,
            modal: true,
            hide: 'fade',
            width:720,
            buttons: [
//               {
//                    text: "Copia",
//                    click: function() {
//                        $("#divEventsLiveViewLink div.alert-success").show();
//                   }
//               },
               {
                   text: "Chiudi",
                   click: function() {
                       //$("#divEventsLiveViewLink div.alert-success").hide();
                       $('#divEventsLiveViewLink').dialog("close");
                   }
               }
            ]
        });        
        var eventsLiveId = $(this).parent().parent().parent().parent().parent().attr('id');
        var eventsLivePlayerType = $(this).parent().attr('class');

        $("#divEventsLiveViewLink div.alert-success").hide();
        
        // Load the link
        $.post("/include/functions.php",{
                fname:"events_live_view_link",
                eventsLiveId:eventsLiveId,
                eventsLivePlayerType:eventsLivePlayerType},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);

                if (status === "success")
                {
                    $('#inputEventsLiveViewLink').val(data);
                    liveViewLinkDlg.dialog('open');
                }
            });
    });    
    
});

</script>
    
</html>
