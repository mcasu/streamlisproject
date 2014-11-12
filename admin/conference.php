<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <title>Dashboard</title>
    <link rel="stylesheet" href="/style/bootstrap.min.css"/>
    <link rel='stylesheet' type='text/css' href='/style/admin.css' />

    <script type="text/javascript" src="/js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="/include/session.js"></script>
    <script type="text/javascript" src="/js/highcharts-2.2.4/highcharts.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    
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
    <div class="panel panel-primary">
        
        <div class="panel-heading">
            <h2 class="panel-title" style="margin-top:10px;margin-left:6px;"><b>Inizia una conferenza con la tua congregazione</b></h2>
        </div>
        
        <div class="panel-body">
            <label for='groups' >Congregazione:</label><br/>
	    <select class="form-control" name="group_name" id="group_name">
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
            <input type="button" value="Join Conference Room" id="join" class="btn btn-info btn-default btn-block"></input>
        </div>
    </div>
    </p>
</div>
    

    <script type="text/javascript" src="/js/bistri/conference.js"></script>
    <script type="text/javascript" src="/js/bistri/api-demo.js"></script>
</body>
    
</html>
