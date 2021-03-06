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

    <title>Dashboard</title>
    <link rel="stylesheet" href="../style/bootstrap.min.css"/>
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script type="text/javascript" src="../js/highcharts-2.2.4/highcharts.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    
    <script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"it","siteId":1168862,"cookiePolicyId":74934126,"banner":{"textColor":"#fff","backgroundColor":"#333"}}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script>
</head>

<body>

<?php include("../include/header_admin.php"); ?>

    <script type="text/javascript">
	$(document).ready(function()
        {
	    function AutoRefresh()
	    {
                $('#dashboard_user_charts').load('/charts/loadcharts_user.php');
                $('#dashboard_event_charts').load('/charts/loadcharts_event.php');

                $('#badgeUserTotalNumber').load('/include/functions.php?fname=get_user_total_number');
                $('#badgeUserLoggedNumber').load('/include/functions.php?fname=get_user_logged_number');
                
                $('#badgeUserCongregationTotalNumber').load('/include/functions.php?fname=get_congregation_total_number');
                $('#badgeUserGroupTotalNumber').load('/include/functions.php?fname=get_group_total_number');
                
	    }
					    
	    var auto_refresh = setInterval(AutoRefresh, 60000);
	    
	    
	    $('#dashboard_user_charts').load('/charts/loadcharts_user.php');
	    $('#dashboard_event_charts').load('/charts/loadcharts_event.php');
            
            $('#badgeUserTotalNumber').load('/include/functions.php?fname=get_user_total_number');
            $('#badgeUserLoggedNumber').load('/include/functions.php?fname=get_user_logged_number');
            
            $('#badgeUserCongregationTotalNumber').load('/include/functions.php?fname=get_congregation_total_number');
            $('#badgeUserGroupTotalNumber').load('/include/functions.php?fname=get_group_total_number');
	    
	});
    </script>


<?php
    try
    {
        $result = $dbactions->GetUsers(true);

        if (!$result)
        {
                error_log("No Results");
		exit;
        }
	
	$users_logged_number = mysql_num_rows($result);

echo '<div class="container-fluid">';
    echo '<div class="panel-group" id="accordionMain">';
	/***********************/
	/***** USERS PANEL *****/
	/***********************/
	echo '<div class="panel panel-primary">'.
	    '<div class="panel-heading">'.
		'<a data-toggle="collapse" data-parent="#accordionMain" href="#accordionUsers">'.
		    '<h3 class="panel-title">'.
			'<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
			'<span><img src="../images/user.png" height="34" width="32"></span>'.
			'<span> <b>UTENTI E GRUPPI</b> </span>  '.
			'<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
		    '</h3>'.
		'</a>'.
	    '</div>';
	    
	    
	    echo '<div id="accordionUsers" class="panel-collapse collapse in">';
		echo '<div class="panel-body">';

                    echo '<div class="well" style="width: 100%; max-width:300px;">';
                        echo '<h5><b>Utenti registrati: <b/><span id="badgeUserTotalNumber" class="badge"></span></h5>';
                        echo '<h5><b>Utenti loggati: <b/><span id="badgeUserLoggedNumber" class="badge"></span></h5>';
                        echo '<br/>';
                        echo '<h5><b>Congregazioni: <b/><span id="badgeUserCongregationTotalNumber" class="badge"></span></h5>';
                        echo '<h5><b>Gruppi: <b/><span id="badgeUserGroupTotalNumber" class="badge"></span></h5>';
                    echo '</div>';
                    echo '<br/>';

                    echo '<div id="dashboard_user_charts" style="min-width: 1020px; overflow:auto"></div>';
                    echo '<br/>';

                    //echo '<div class="container-fluid" style="overflow:auto">';
                    echo '<table class="table table-condensed">';
                        echo '<tr class="head">';
                        echo '<th>NOME</th><th>USERNAME</th><th>CONGREGAZIONE</th><th>TIPO</th><th>ULTIMO LOGIN</th>';
                        echo '</tr>';

                        while($row = mysql_fetch_array($result))
                        {
                            $user_id=$row['user_id'];
                            $user_name=$row['user_name'];
                            $user_mail=$row['user_mail'];
                            $username=$row['username'];
                            $user_group_name=$row['user_group_name'];
                            $user_role_name=$row['user_role_name'];
                            $userIsLogged=$row['user_logged'];
                            $user_last_login=strftime("%A %d %B %Y %H:%M:%S", strtotime($row['last_login']));
                            $user_last_update=strftime("%A %d %B %Y %H:%M:%S", strtotime($row['last_update']));

                            if ($userIsLogged == '0')
                            {
                                continue;
                            }

                                echo '<tr>';
                                        echo "<td>" . $user_name . "</td>";
                                        echo "<td>" . $username . "</td>";
                                        echo "<td>" . $user_group_name . "</td>";
                                        echo "<td>" . $user_role_name . "</td>";
                                        echo "<td>" . $user_last_login . "</td>";
                                echo '</tr>';
                        }    
                    echo '</table>';

                    //echo '</div>';
			
		echo '</div>';
	    echo '</div>';
	echo '</div>';
      
	/************************/
	/***** EVENTS PANEL *****/
	/************************/

	echo '<div class="panel panel-primary">'.
	    '<div class="panel-heading">'.
		'<a data-toggle="collapse" data-parent="#accordionMain" href="#accordionEvents">'.
		    '<h3 class="panel-title">'.
			'<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
			'<span><img src="../images/event.png" height="34" width="32"></span>'.
			'<span> <b>EVENTI</b> </span>  '.
			'<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
		    '</h3>'.
		'</a>'.
	    '</div>';
	
	    echo '<div id="accordionEvents" class="panel-collapse collapse in">';
		echo '<div class="panel-body">';
		    echo '<div id="dashboard_event_charts" class="container-fluid"></div>';
		echo '</div>';
	    echo '</div>';
	echo '</div>';
    
    echo '</div>';
echo '</div>';
    
    }
    catch(PDOException $e)
    {
        echo 'No Results';
    }
?>

<?php include("../include/footer.php"); ?>
</body>
</html>
