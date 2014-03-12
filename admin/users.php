<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

$user_role = $fgmembersite->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
        $utils->RedirectToURL("../viewer/live-normal.php");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1' />
	<title>JW LIS Streaming - Utenti</title>
	
	<link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
	<link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript">
$(function()
{
	$("a.userdelete").click(function()
	{
		var td_id=$(this).parent().attr('id');
		par=$(this).parent();

		if (confirm("Vuoi davvero eliminare?")){
		$.post("user_delete.php",{user_id:td_id,},
		function(data,status)
		{
			/*alert("Data: " + data + "\nStatus: " + status);*/
			par.parent().fadeOut(1000, function() 
			{
				par.parent().remove();
			});
		});
		}
	});
});
        </script>

</head>


<body>
<?php include("header.php"); ?>

<br/>

<div align="right" id='fg_membersite_content'>
User logged <b><?= $fgmembersite->UserFullName(); ?></b></div>

<div id='fg_membersite_content'>
<p>La tua congregazione e' <b><?= $fgmembersite->UserGroupName(); ?></b></p>
</div>
<br/>

<div id='fg_membersite_content'>
<table class="imagetable" id="users_table">
<tr>
	<th>NOME</th><th>ID</th><th>MAIL</th><th>USERNAME</th><th>CONGREGAZIONE</th><th>TIPO</th><th>CONFERMATO</th><th>AZIONI</th>
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

		echo '<tr class="users_table">';
			echo "<td>" . $user_name . "</td>";
			echo "<td>" . $user_id . "</td>";
			echo "<td>" . $user_mail . "</td>";
			echo "<td>" . $username . "</td>";
			echo "<td>" . $user_group_name . "</td>";
			echo "<td>" . $user_role_name . "</td>";
			echo "<td>" . $confirmcode . "</td>";
			echo "<td id=\"".$user_id."\"><a class=\"userdelete\" href=\"javascript:void()\"/>".
			"<img src=\"../images/delete.png\" width=\"20\"/></td>";
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
