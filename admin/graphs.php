<?php include("../check_login.php"); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
      
    <title>Graphics</title>
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
<?php include("../include/header_admin.php"); ?>
</br>

<h5 class="pull-right" style="margin-right: 3px;"><b><?= $mainactions->UserFullName(); ?></b>, bentornato! </h5>
<p><h4 style="margin-left:4px;"> La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b></h4></p>


<iframe height="700" width="100%" src="/monitorix"></iframe>

</body>
</html>
