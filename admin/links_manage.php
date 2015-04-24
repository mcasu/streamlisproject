<?php include("../check_login.php"); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Gestisci relazioni</title>
    <link rel="stylesheet" href="../style/bootstrap.min.css"/>
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>

    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>

<script type="text/javascript">
$(document).ready(function()
{

$("button.viewer_add").click(function()
{
    var group_id = $(this).parent().parent().parent().attr('id');
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
    
$("button.viewer_del").click(function()
{
    var group_id = $(this).parent().parent().parent().attr('id');
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

});

</script>
</head>


<body>
<?php include("../include/header_admin.php"); ?>

<h2>ELENCO CONGREGAZIONI PUBLISHER:</h2>

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

                $count++;
        }

        echo '<div class="container-fluid">';
	echo '<div class="panel-group" id="accordionMain">';	

        foreach ($group_array AS $id => $row)
        {
                $group_id=$row['group_id'];
                $group_name=$row['group_name'];
                $group_type=$row['group_type'];
                $group_role_name=$row['group_role_name'];

		if ($group_role_name=="publisher")
		{
			$viewers = $dbactions->GetViewersByPublisher($group_id);
				
				
			echo '<div class="panel panel-primary">'.
			    /*** PANEL HEADING ***/
			    '<div class="panel-heading">'.
				'<a class="title" data-toggle="collapse" data-parent="#accordionMain" href="#accordionOndemand_'.$group_id.'">'.
				    '<h3 class="panel-title">'.
					'<span class="glyphicon glyphicon-chevron-left pull-left"></span>'.
					'<span><img src="../images/group.png" height="34" width="32"></span>'.
					'<span> <b>'.$group_name.'</b> </span>  '.
					'<span class="glyphicon glyphicon-chevron-right pull-right"></span>'.
				    '</h3>'.
				'</a>'.
			    '</div>';
				
			    /*** PANEL BODY ***/
			    echo '<div id="accordionOndemand_'.$group_id.'" class="panel-collapse collapse">';
			    echo '<div class="panel-body">';
				
			    if (mysql_num_rows($viewers) < 1)
			    { 
				    echo '<h4>Nessun gruppo ha relazioni con questa congregazione.</h4>';
			    }
			    else
			    {
				    echo '<h4>Elenco congregazioni che possono vedere le adunanze di <b>'.$group_name.'</b>:</h4>';
			    }
			    
			    echo '<div>';
				echo '<table class="table table-bordered">'.
				'<tr class="head">'.
					'<th class="text-center">VIEWER ASSOCIATI</th><th class="text-center">AZIONI</th><th class="text-center">VIEWER DISPONIBILI</th>'.
				'</tr>';

				echo '<tr id='.$group_id.'>';
				
				    // TD VIEWER ASSOCIATI
				    echo '<td>';
				    echo '<select multiple class="form-control group_linked" id="gl_'.$group_id.'">';
				    while($row = mysql_fetch_array($viewers))
				    {
					    $viewer_id=$row['viewer_id'];
					    $viewer_name=$row['viewer_name'];

					    echo '<option id="'.$viewer_id.'">'.$viewer_name.'</option>';
				    }
				    echo '</select>';
				    echo '</td>';
		
				    // TD PULSANTI
				    echo '<td>';
					echo '<p class="text-center">';
					    echo '<button class="btn btn-primary viewer_add" type="submit" style="width:150px"><span class="glyphicon glyphicon-arrow-left"></span> Aggiungi il viewer</button>';
					    echo '<br/>';
					    echo '<br/>';
					    echo '<button class="btn btn-primary viewer_del" type="submit" style="width:150px">Elimina il viewer <span class="glyphicon glyphicon-arrow-right"></button>';
					echo '</p>';
				    echo '</td>';

				    $viewers_available=$dbactions->GetViewersAvailable($group_id);
				
				    // TD VIEWER NON ASSOCIATI
				    echo '<td>';
					echo '<select multiple class="form-control group_unlinked" id="gul_'.$group_id.'">';
					while($row = mysql_fetch_array($viewers_available))
					{
						
						echo '<option id="'.$row['group_id'].'">'.$row['group_name'].'</option>';
					}
					echo '</select>';
				    echo '</td>';
				    
				echo '</tr>';
				echo '</table>';
			    echo '</div>';
				    
			echo '</div>'; /* FINE PANEL BODY */
			echo '</div>';
		    echo '</div>'; /* FINE PANEL PRIMARY */
		}
        }
	
	echo '</div>';
	echo '</div>';
    }
    catch(Exception $e)
    {
        echo 'No Results';
    }

?>

</body>
</html>
