<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

$user_role = $mainactions->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
	$utils->RedirectToURL("../viewer/live-normal.php");
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Home page</title>
      <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">

    <script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    
    <script type="text/javascript">
	$(document).ready(function()
        {
	    function AutoRefresh()
	    {
		location.reload(); 
	    }
					    
	    var auto_refresh = setInterval(AutoRefresh, 5000);
	});
    </script>
    
</head>
<body>
<?php include("header.php"); ?>
<br/>
<div align="right" id='fg_membersite_content'>
<b><?= $mainactions->UserFullName(); ?></b>, Welcome back!</div>

<div><p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b>.</p></div>

<p><b>Utenti loggati:</b></p>
<div id='fg_membersite_content'>
<table class="imagetable" id="users_table">
<tr class="head">
	<th>NOME</th><th>ID</th><th>MAIL</th><th>USERNAME</th><th>CONGREGAZIONE</th><th>TIPO</th><th>ULTIMO LOGIN</th><th>ULTIMO UPDATE</th>
</tr>
<?php
    try
    {
        $result = $dbactions->GetUsers();

        if (!$result)
        {
                error_log("No Results");
        }

        while($row = mysql_fetch_array($result))
        {
                $user_id=$row['user_id'];
                $user_name=$row['user_name'];
                $user_mail=$row['user_mail'];
                $username=$row['username'];
                $confirmcode=$row['confirmcode']=="y"?"SI":"NO";
                $user_group_name=$row['user_group_name'];
                $user_role_name=$row['user_role_name'];
		$user_logged=$row['user_logged'];
		$user_last_login=strftime("%A %d %B %Y %H:%M:%S", strtotime($row['last_login']));
		$user_last_update=strftime("%A %d %B %Y %H:%M:%S", strtotime($row['last_update']));
    
	    if ($user_logged == '0')
	    {
		continue;
	    }
		
		echo '<tr class="users_table">';
			echo "<td>" . $user_name . "</td>";
			echo "<td>" . $user_id . "</td>";
			echo "<td>" . $user_mail . "</td>";
			echo "<td>" . $username . "</td>";
			echo "<td>" . $user_group_name . "</td>";
			echo "<td>" . $user_role_name . "</td>";
			echo "<td>" . $user_last_login . "</td>";
			echo "<td>" . $user_last_update . "</td>";
		echo '</tr>';
        }
    }
    catch(PDOException $e)
    {
        echo 'No Results';
    }
?>
</table>
</div>

</body>
</html>
