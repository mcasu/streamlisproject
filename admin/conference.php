<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_admin.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <title>Conference</title>
    <link rel="stylesheet" href="/style/bootstrap.min.css"/>
    <link rel='stylesheet' type='text/css' href='/style/admin.css' />

    <script type="text/javascript" src="/js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="/include/session.js"></script>
    <script type="text/javascript" src="/js/highcharts-2.2.4/highcharts.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://api.bistri.com/bistri.conference.min.js"></script>
    <script type="text/javascript" src="/players/jwplayer/jwplayer.js" ></script>
    
</head>

<body>

<?php include("../include/header_admin.php");

$publishCode = $dbactions->GetPublishCodeByGroupId($mainactions->UserGroupId());

?>
    
<div class="container-fluid">
    
    <input type="hidden" class="username" id="<?= $mainactions->UserName(); ?>"/>
    <?php echo '<input type="hidden" class="userrole" id="' . $mainactions->GetSessionUserRole() . '"/>'; ?>
    <?php echo '<input type="hidden" class="group_publishcode" id="' . $publishCode . '"/>'; ?>
    <p>
    <div id="panelJoin" class="panel panel-default">
        
        <div class="panel-heading">
            <div><h4><b>Commenti</b></h4></div>
        </div>
        
        <div class="panel-body">
            <br/>
            
            <div class="alert alert-danger" role="alert">
                <h4>La tua congregazione non sta trasmettendo alcuna adunanza.</h4>
            </div>
            
            <br/>
            <div id="streamSelectorContainer" class="container-fluid">
                
            </div>
         
            <br/>
            <br/>
            <input type="button" value="Inizia la conferenza" id="join" class="btn btn-info btn-default btn-block"></input>
            <input type="button" value="Chiudi la conferenza" id="quit" class="btn btn-danger btn-default btn-block"></input>
            <br/>
            
            <h5 id="joined_user_number">Utenti collegati: <span class="label label-primary"></span></h5><br/>
            
            <div id="panelVideo">

                <div id="panelVideoLocal" class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h4 class="panel-title" style="margin-top: 4px;">ADUNANZA</h4>
                    </div>
                    <div class="panel-body">
                            <div class="select">
                                <label for="videoSource">Seleziona la webcam:  </label> <select id="videoSource"></select>
                            </div>
                            <br/>
                            <video id="myvideo" autoplay controls></video>
                    </div>
                </div>
                
                <div id="panelVideoRemote" class="panel panel-primary">
                    <div class="panel-heading text-center">
                        <h4 class="panel-title" style="margin-top: 4px;">COMMENTI</h4>
                    </div>

                    <div class="panel-body">
                     <!-- Columns are always 50% wide, on mobile and desktop -->
                     <div class="row">
                         <div class="panel panel-default col-md-3 col-xs-2">
                             <div class="panel-heading text-center">
                                 <h5></h5>
                             </div>
                             <div class="panel-body">
                                 <div id="video-1" class="remoteStreams"></div>
                             </div>
                         </div>
                         <div class="panel panel-default col-md-3 col-xs-2">
                             <div class="panel-heading text-center">
                                 <h5></h5>
                             </div>
                             <div class="panel-body">
                                 <div id="video-2" class="remoteStreams"></div>
                             </div>
                         </div>
                         <div class="panel panel-default col-md-3 col-xs-2">
                             <div class="panel-heading text-center">
                                 <h5></h5>
                             </div>
                             <div class="panel-body">
                                 <div id="video-3" class="remoteStreams"></div>
                             </div>
                         </div>
                         <div class="panel panel-default col-md-3 col-xs-2">
                             <div class="panel-heading text-center">
                                 <h5></h5>
                             </div>
                             <div class="panel-body">
                                 <div id="video-4" class="remoteStreams"></div>
                             </div>
                         </div>
                     </div>
                    </div>
                </div>
            </div>            
            
        </div>
    </div>
    </p>
 
</div>    
    
    <!--<script type="text/javascript" src="/js/bistri/api-demo.js"></script>-->
    <script type="text/javascript" src="/include/functions.js"></script>
    
    <script type="text/javascript">
	$(document).ready(function()
        {
            $("#quit").hide();
            $('#join').prop('disabled', true);
            $('#streamSelectorContainer').hide();
            $("#panelVideo").hide();
            $(".alert-danger").hide();
            $("#joined_user_number").hide();
            
            CheckGroupStatus();
            
            var auto_refresh = setInterval(CheckGroupStatus, 10000);
            
            function CheckGroupStatus()
            {
                $(".alert-danger").hide();
                var result = CheckLiveExistsForPublishCode($('.group_publishcode').attr('id'));
                if (result  === "false")
                {
                    //alert('La congregazione con code [' + $('#roomSelector').val() + '] NON sta trasmettendo.');
                    $(".alert-danger").show();
                    $('#join').prop('disabled', true);
                    $('#streamSelectorContainer').hide();
                }
                else
                {
                    $(".alert-danger").hide();
                    
                    $('#streamSelectorContainer').show();
                    $('#streamSelectorContainer').load('/include/functions.php?fname=get_stream_selector_container&publishCode=' + $('.group_publishcode').attr('id'));
                    
                    $('#join').prop('disabled', false);
                }
            };
            
            
        });
    </script>
    
    <script type="text/javascript" src="/js/bistri/jwconference-publisher.js"></script>
    <!--<script src="/js/adapter.js"></script>-->
    <script src="/js/common.js"></script>
   
    <?php include("../include/footer.php"); ?>
</body>
    
</html>

