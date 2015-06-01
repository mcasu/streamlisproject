<?php include("../check_login.php"); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Operazioni</title>
	
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel="stylesheet" href="../style/jquery.dataTables.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/jquery.dataTables.min.js"></script>    
    
<script type="text/javascript">
    
$(document).ready(function()
{

$('#ondemand_actions_join_table').load("/include/functions.php?fname=get_ondemand_actions_join");
    
});

</script>
    
</head>


<body>
<?php include("../include/header_publisher.php"); ?>


<div class="container-fluid">
    <div class="panel panel-default">
    <br/>
        
    <div class="panel-heading">	<h4>Ondemand Join</h4></div>

	<div class="panel-body">
	    <table class="table table-hover" id="ondemand_actions_join_table">
	
	    </table>
	</div>
    </div>
</div>

</body>
</html>