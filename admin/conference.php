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
    <script type="text/javascript" src="https://api.bistri.com/bistri.conference.min.js?v=3"></script>
    <script type="text/javascript" src="/js/bistri/jwconference.js"></script>
    
</head>

<body>

<?php include("header.php");
  
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
?>
    
<div class="container-fluid">
    
    <p>
    <div id="panelJoin" class="panel panel-default">
        
        <div class="panel-heading">
            <!--<h2 class="panel-title" style="margin-top:10px;margin-left:6px;"><b>Inizia una conferenza con la tua congregazione</b></h2>-->
        </div>
        
        <div class="panel-body">
            <label for='groups' >Congregazione:</label><br/>
	    <select id="roomSelector" class="form-control" name="group_name" id="group_name">
            <?php    
                foreach ($group_array AS $id => $row)
                {
                    $group_id=$row['group_id'];
                    $group_name=$row['group_name'];
                    $group_type=$row['group_type'];
                    $group_role_name=$row['group_role_name'];
                    $group_publish_code=$row['publish_code'];

                    if ($group_role_name=="publisher")
                    {
                        echo '<option value="' . $group_publish_code . '">' . $group_name . '</option>"';
                    }
                }
            ?>
            </select>
            <br/>
            <br/>
            <br/>
            <input type="button" value="Join Conference Room" id="join" class="btn btn-info btn-default btn-block"></input>
            <input type="button" value="Quit Conference Room" id="quit" class="btn btn-danger btn-default btn-block"></input>
            <br/>
        </div>
    </div>
    </p>
 
    <div id="panelVideo">

        <div class="container">
            <p class="pull-left">
                <div id="panelVideoLocal" class="panel panel-primary">
                   <div class="panel-heading">
                       <h4 class="panel-title">Mio video</h4>
                   </div>

                   <div class="panel-body">
                    <!-- Columns start at 50% wide on mobile and bump up to 33.3% wide on desktop -->
                    <div id="localStreams" class="row">
                      <div class="panel panel-default">
                         <h5 class="panel-title pull-left" style="margin-left: 2px;margin-top: 2px;"><b><?php $mainactions->UserFullName(); ?></b></h5>
                         <div class="col-xs-6 col-md-4" id="myvideo"></div>
                      </div>

                      <div class="col-xs-6 col-md-4"></div>
                      <div class="col-xs-6 col-md-4"></div>
                    </div>
                   </div>
                </div>
            </p>
        </div>
        
       <div id="panelVideoRemote" class="panel panel-primary">
           <div class="panel-heading">
               <h4 class="panel-title" style="margin-left: 2px;margin-top: 2px;">Altri fratelli</h4>
           </div>

           <div class="panel-body">
            <!-- Columns are always 50% wide, on mobile and desktop -->
            <div class="row">
                <div id="video-1" class="col-xs-6 remoteStreams"></div>
                <div id="video-2" class="col-xs-6 remoteStreams"></div>
            </div>
           </div>
       </div>
     
    </div>
</div>    
    
    <!--<script type="text/javascript" src="/js/bistri/api-demo.js"></script>-->
    
    <script type="text/javascript">
	$(document).ready(function()
        {
            $("#quit").hide();
            $("#panelVideo").hide();
        });
    </script>
</body>
    
</html>

