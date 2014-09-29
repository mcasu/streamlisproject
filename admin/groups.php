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
	<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1' />
	<title>JW LIS Streaming - Congregazioni</title>

	<link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
	<link rel='stylesheet' type='text/css' href='../style/header.css' />
	<link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../include/session.js"></script>

        <script type="text/javascript">
        $(function(){
                $("a.groupdelete").click(function()
		{
                	var td_id=$(this).parent().attr('id');
	                par=$(this).parent();
			
			if (confirm("Vuoi davvero eliminare?"))
			{
			    $.post("group_delete.php",{group_id:td_id,},
	    
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
</br>
<div align="right" id='fg_membersite_content'>
User logged <b><?= $mainactions->UserFullName(); ?></b></div>

<div id='fg_membersite_content'>
<p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b></p>
</div>
</br>

<div id='fg_membersite_content'>
<table class="imagetable">
<tr class="head">
	<th>CONGREGAZIONE</th><th>ID</th><th>TIPO</th><th>RUOLO</th><th>PUBLISH CODE</th><th>AZIONI</th>
</tr>
<?php
    try
    {
        $result = $dbactions->GetGroups();

        if (!$result)
        {
                error_log("No Results");
        }

        while($row = mysql_fetch_array($result))
        {
                $group_id=$row['group_id'];
                $group_name=$row['group_name'];
                $group_type=$row['group_type'];
                $group_role_name=$row['group_role_name'];
                $group_publish_code=$row['publish_code'];
		echo '<tr>';
			echo "<td>" . $group_name . "</td>";
			echo "<td>" . $group_id . "</td>";
			echo "<td>" . $group_type . "</td>";
			echo "<td>" . $group_role_name . "</td>";
			echo "<td>" . $group_publish_code . "</td>";
			echo "<td id=\"".$group_id."\"><a class=\"groupdelete\" href=\"javascript:void()\"/>".
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
