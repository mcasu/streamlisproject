<?php include("../check_login.php"); ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US" style="margin: 0px; height: 100%;">
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
<body style="margin: 0px; padding:0px; height: 100%;">
<?php include("../include/header_admin.php"); ?>


<iframe style="display: block; height: 100%; width: 100%;" src="/zabbix"></iframe>

</body>
</html>
