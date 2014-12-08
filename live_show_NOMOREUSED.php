<!DOCTYPE HTML>

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
        $utils->RedirectToURL("../home-normal.php");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="../include/animatedcollapse.js">

/***********************************************
* Animated Collapsible DIV v2.4- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more
***********************************************/

</script>


<script type="text/javascript">

<?php

try
{
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

		$group_array[$group_id]=Array();
		$group_array[$group_id]['group_id']=$row['group_id'];
		$group_array[$group_id]['group_name']=$row['group_name'];
		$group_array[$group_id]['group_type']=$row['group_type'];
		$group_array[$group_id]['group_role_name']=$row['group_role_name'];
		
		/*echo 'animatedcollapse.addDiv(\''.$group_id.'\', \'fade=1,height=80px\')';*/
		echo 'animatedcollapse.addDiv(\''.$group_id.'\', \'fade=1\')';
		echo "\r\n";
		$count++;
	}
}
catch(Exception $e)
{
	echo 'No Results';
}

?>

animatedcollapse.ontoggle=function($, divobj, state){ //fires each time a DIV is expanded/contracted
	//$: Access to jQuery
	//divobj: DOM reference to DIV being expanded/ collapsed. Use "divobj.id" to get its ID
	//state: "block" or "none", depending on state
}

animatedcollapse.init()

</script>

<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Stream LIS - Gestisci relazioni</title>
      <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
<style type="text/css">
table.imagetable {
        font-family: verdana,arial,sans-serif;
        font-size:11px;
        color:#333333;
        border-width: 1px;
        border-color: #999999;
        border-collapse: collapse;
}
table.imagetable th {
        background:#b5cfd2 url('/images/cell-blue.jpg');
        border-width: 1px;
        padding: 8px;
        border-style: solid;
        border-color: #999999;
}
table.imagetable td {
        background:#dcddc0 url('/images/cell-grey.jpg');
        border-width: 1px;
        padding: 8px;
        border-style: solid;
        border-color: #999999;
}
</style>
</head>
<body>
<?php include("header.php"); ?>
</br>
<div align="right" id='fg_membersite_content'>
User logged <b><?= $fgmembersite->UserFullName(); ?></b></div>
<div>
<p>La tua congregazione e' <b><?= $fgmembersite->UserGroupName(); ?></b>.</p>
</div>

</br>
<h2>ELENCO CONGREGAZIONI PUBLISHER:</h2>

<?php 

try
    {
        foreach ($group_array AS $id => $row)
        {
                $group_id=$row['group_id'];
                $group_name=$row['group_name'];
                $group_type=$row['group_type'];
                $group_role_name=$row['group_role_name'];

		if ($group_role_name=="publisher")
		{
			$viewers = $dbactions->GetViewersByPublisher($group_id);
	                echo '<p><b>'. $group_name . '</b>'.
			'<img align="center" src="../images/group.png" border="0" height="48" width="48"/>';
	
			echo '<a href="javascript:animatedcollapse.toggle(\''.$group_id.'\')"></a>'.
				'<a href="javascript:animatedcollapse.show(\''.$group_id.'\')">Mostra</a> || '.
				'<a href="javascript:animatedcollapse.hide(\''.$group_id.'\')">Nascondi</a>'.
				'<div id="'.$group_id.'" style="display:none">'.
				'Elenco congregazioni che possono vedere le adunanze di <b>'.$group_name.'</b>:'.
				'<div><table class="imagetable">'.
				'<tr>'.
					'<th>VIEWER NAME</th><th>VIEWER ID</th>'.
				'</tr>';
				
				while($row = mysql_fetch_array($viewers))
				{
			                $viewer_id=$row['viewer_id'];
			                $viewer_name=$row['viewer_name'];

			                echo '<tr>';
			                        echo "<td>" . $viewer_name . "</td>";
			                        echo "<td>" . $viewer_id . "</td>";
			                echo '</tr>';
			        }
			echo '</table></div>';
			echo '</div></p>'.
				'<hr style="margin: 1em 0" />';
		}
        }
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>


</body>
</html>
