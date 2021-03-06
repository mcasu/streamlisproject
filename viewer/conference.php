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

    <title>Conference</title>
    <link rel="stylesheet" href="/style/bootstrap.min.css"/>
    <link rel='stylesheet' type='text/css' href='/style/admin.css' />

    <script type="text/javascript" src="/js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="/include/session.js"></script>
    <script type="text/javascript" src="/js/highcharts-2.2.4/highcharts.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://api.bistri.com/bistri.conference.min.js"></script>
    <script type="text/javascript" src="/players/jwplayer/jwplayer.js" ></script>
    
    <script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"it","siteId":1168862,"cookiePolicyId":74934126,"banner":{"textColor":"#fff","backgroundColor":"#333"}}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script>
</head>

<body>

<?php include("../include/header_viewer.php");
  
    $result = $dbactions->GetPublishersByViewer($mainactions->UserGroupId());

    if (!$result)
    {
        error_log("No Results");
    }

?>
    
<div class="container-fluid">
    
    <input type="hidden" class="username" id="<?= $mainactions->UserName(); ?>"/>
    <input type="hidden" class="userrole" id="<?= $mainactions->GetSessionUserRole(); ?>"/>
    <p>
    <div id="panelJoin" class="panel panel-default">
        
        <div class="panel-heading">
            <!--<h2 class="panel-title" style="margin-top:10px;margin-left:6px;"><b>Inizia una conferenza con la tua congregazione</b></h2>-->
        </div>
        
        <div class="panel-body">
            <div class="container-fluid">
                <label for='groups' >Congregazione:</label><br/>
                <select id="roomSelector" class="form-control" name="group_name" id="group_name">
                <?php    
                    while($row = mysql_fetch_array($result))
                    {
                        $publisher_id=$row['publisher_id'];
                        $publisher_name=$row['publisher_name'];
                        $publisher_code=$row['publisher_code'];

                        echo '<option value="' . $publisher_code . '">' . $publisher_name . '</option>"';
                    }
                ?>
                </select>
            </div>
            
            <br/>
            
            <div class="alert alert-danger" role="alert">
                <h4>La congregazione selezionata non sta trasmettendo alcuna adunanza.</h4>
            </div>
            
            <br/>
            <div id="streamSelectorContainer" class="container-fluid">
                
            </div>
         
            <br/>
            <br/>
            <input type="button" value="START" id="join" class="btn btn-info btn-default btn-block"></input>
            <input type="button" value="STOP" id="quit" class="btn btn-danger btn-default btn-block"></input>
            <br/>
            
            <div id="panelVideo">
                <div class="row">
                    <div id="localStreamsMyVideo" class="panel panel-primary pull-left col-md-2 col-xs-3" style="margin-right: 6px;">
                      <div class="panel-heading text-center">
                          <h4 class="panel-title" style="margin-left: 4px; margin-top: 4px;">Video di <b><?= $mainactions->UserFullName(); ?></b></h4>
                      </div>
                        <div id="myvideo" class="panel-body"></div>
                    </div>
                    <div id="localStreamsPlayer" class="panel panel-primary pull-left col-md-3 col-xs-4">
                      <div class="panel-heading text-center">
                          <h4 class="panel-title" style="margin-left: 4px; margin-top: 4px;">Adunanza in corso</b></h4>
                      </div>
                        <div id="remotevideo" class="panel-body"></div>
                    </div>
                </div>
            </div>            
            
        </div>
    </div>
    </p>
</div>    
    
    <script type="text/javascript" src="/include/functions.js"></script>
    
    <script type="text/javascript">
	$(document).ready(function()
        {
            $("#quit").hide();
            $('#join').prop('disabled', true);
            $('#streamSelectorContainer').hide();
            $("#panelVideo").hide();
            $(".alert-danger").hide();
            
            CheckGroupStatus();
            
            var auto_refresh = setInterval(CheckGroupStatus, 10000);
            
            $('#roomSelector').on('change', function() 
            {
                CheckGroupStatus();
            });
            
            function CheckGroupStatus()
            {
                $(".alert-danger").hide();
                var result = CheckLiveExistsForPublishCode($('#roomSelector').val());
                //var members = BistriConference.getRoomMembers($('#roomSelector').val());
                
                //alert("ROOM: " + $('#roomSelector').val() + " COUNT: " + members.length);
                if (result  === "false")
                {
                    quitConference();
                    //alert('La congregazione con code [' + $('#roomSelector').val() + '] NON sta trasmettendo.');
                    $(".alert-danger").show();
                    $('#join').prop('disabled', true);
                    $('#streamSelectorContainer').hide();
                }
                else
                {
                    $(".alert-danger").hide();
                    
                    $('#streamSelectorContainer').show();
                    $('#streamSelectorContainer').load('/include/functions.php?fname=get_stream_selector_container&publishCode=' + $('#roomSelector').val());
                    
                    $('#join').prop('disabled', false);
                }
            };
            
            
        });
    </script>
    
    <script type="text/javascript" src="/js/bistri/jwconference-viewer.js"></script>
    
    <?php include("../include/footer.php"); ?>
</body>
    
</html>

