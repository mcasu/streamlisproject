
<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

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

                $count++;
        }
}
catch(Exception $e)
{
        echo 'No Results';
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<head>

	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
	<title>JW LIS Streaming - Gestisci relazioni</title>
	<link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
	<link rel='stylesheet' type='text/css' href='../style/admin.css' />

<script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>

<script type="text/javascript">
$(document).ready(function()
{

$("input.viewer_add").click(function()
{
    var group_id = $(this).parent().parent().attr('id');
    var group_unlinked_id = "gul_" + group_id;
    var group_linked_id = "gl_" + group_id;
    
    //alert(group_unlinked_id);
    
    var viewertoadd = "";
    $( "#" + group_unlinked_id + " option:selected" ).each(function()
    {
	viewer_id = $(this).attr('id');
	viewertoadd += viewer_id + "|";
    });
    //alert(viewertoadd);
    

    $.post("link_add.php",{viewerlist:viewertoadd,publisher_id:group_id}, function(data,status) {
	//alert("Data: " + data + "\nStatus: " + status);

	if (status == "success")
	{
	    viewertoadd = "";
	    $( "#" + group_unlinked_id + " option:selected" ).each(function()
	    {
		viewer_id = $(this).attr('id');
		$("#" + group_linked_id).append('<option id="' + viewer_id + '">' + $(this).text() + '</option>');
				
		viewertoadd += $(this).text() + "|";
		
		$(this).fadeOut(1000, function()
		{
		    $(this).remove();
		});
	    });
	}	    
    });

});
    
$("input.viewer_del").click(function()
{
    var group_id = $(this).parent().parent().attr('id');
    var group_unlinked_id = "gul_" + group_id;
    var group_linked_id = "gl_" + group_id;
    
    //alert(group_linked_id);
    
    var viewertodel = "";
    $( "#" + group_linked_id + " option:selected" ).each(function()
    {
	viewer_id = $(this).attr('id');
	viewertodel += viewer_id + "|";
    });
    //alert(viewertodel);
    

    $.post("link_del.php",{viewerlist:viewertodel,publisher_id:group_id}, function(data,status) {
	//alert("Data: " + data + "\nStatus: " + status);

	if (status == "success")
	{
	    viewertodel = "";
	    $( "#" + group_linked_id + " option:selected" ).each(function()
	    {
		viewer_id = $(this).attr('id');
		$("#" + group_unlinked_id).append('<option id="' + viewer_id + '">' + $(this).text() + '</option>');
				
		viewertodel += $(this).text() + "|";
		
		$(this).fadeOut(1000, function()
		{
		    $(this).remove();
		});
	    });
	}	    
    });
    
});    
    

$(".toggle_container").hide();

$("h2.trigger").css("cursor","pointer").toggle(function(){
  	$(this).addClass("active"); 
  }, function () {
  $(this).removeClass("active");
  });

$("h2.trigger").click(function()
{
	$(this).next(".toggle_container").slideToggle("slow");
});

});

</script>
</head>


<body>
<?php include("header.php"); ?>
<br/>
<div align="right" id='fg_membersite_content'>
Ciao <b><?= $fgmembersite->UserFullName(); ?></b></div>

<div id='fg_membersite_content'>
<p>La tua congregazione e' <b><?= $fgmembersite->UserGroupName(); ?></b>.</p>
</div>

<h2>ELENCO CONGREGAZIONI PUBLISHER:</h2>

<div>
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
				
				echo '<h2 class="toggle trigger">'.
					'<a href="#">'.$group_name.'</a>'.
				     '</h2>';  /*<img class="group_logo" src="../images/group.png" height="30" width="32"/>*/
					
					echo '<div class="toggle_container" id="'.$group_id.'">';
					    if (mysql_num_rows($viewers) < 1)
					    { 
						    echo '<div style="margin: 0 0 0 10px">Nessun gruppo ha relazioni con questa congregazione.</div>';
					    }
					    else
					    {
						    echo '<div style="margin: 0 0 0 10px">Elenco congregazioni che possono vedere le adunanze di <b>'.$group_name.'</b>:</div>';
					    }
					    
					    echo '<div class="left">';
						echo '<div id="fg_membersite_content">';
						echo '<table class="imagetable">'.
						'<tr>'.
							'<th>VIEWER ASSOCIATI</th><th>AZIONI</th><th>VIEWER DISPONIBILI</th>'.
						'</tr>';
	
						echo '<tr id='.$group_id.'>';
						
						    // TD VIEWER ASSOCIATI
						    echo '<td>';
						    echo '<select class="group_linked" id="gl_'.$group_id.'" style="min-width:120px" multiple>';
						    while($row = mysql_fetch_array($viewers))
						    {
							    $viewer_id=$row['viewer_id'];
							    $viewer_name=$row['viewer_name'];
	    
							    echo '<option id="'.$viewer_id.'">'.$viewer_name.'</option>';
						    }
						    echo '</select>';
						    echo '</td>';
				
						    // TD PULSANTI
						    echo '<td rowspan="2">';
						    echo '<input class="viewer_add" type="submit" name="viewer_add" value="<<"/>';
						    echo '<br/>';
						    echo '<input class="viewer_del" type="submit" name="viewer_del" value=">>"/>';
						    echo '</td>';

						    $viewers_available=$dbactions->GetViewersAvailable($group_id);
						
						    // TD VIEWER NON ASSOCIATI
						    echo '<td>';
						    echo '<select class="group_unlinked" id="gul_'.$group_id.'" style="min-width:120px" multiple>';
						    while($row = mysql_fetch_array($viewers_available))
						    {
							    
							    echo '<option id="'.$row['group_id'].'">'.$row['group_name'].'</option>';
						    }
						    echo '</select>';
						    echo '</td>';
						    
						echo '</tr>';
						echo '</table>';
						echo '</div>';
					    echo '</div>'; /* FINE DIV CLASS "left" */
				echo '</div>'; /* FINE DIV CLASS "toggle_container" */
		}
        }
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>

</div>
</body>
</html>
