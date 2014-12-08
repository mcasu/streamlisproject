<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Congregazioni</title>
	
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    
<script type="text/javascript">
    
$(document).ready(function()
{
    $("#btn_group_delete").prop('disabled', true);
    
    $("input:radio").click(function(lastSelectedRow)
    {
	$("#groups_table").find("tr").removeClass("active");
	
	var isChecked = $(this).prop("checked");
	var selectedRow = $(this).parent("td").parent("tr");
    
	if (isChecked)
	{
	    selectedRow.addClass("active");
	    $("#btn_group_delete").prop('disabled', false);
	    //selectedRow.css({ "background-color": "#D4FFAA", "color": "GhostWhite" });
	}
	else
	{
	    selectedRow.removeClass("active");
	    //selectedRow.css({ "background-color": '', "color": "black" });
	}
	
    });

    $("#btn_group_delete").click(function()
    {
	var tr_obj = $('input[name=group_selected]:checked').parent("td").parent("tr");
	
	var tr_id=tr_obj.attr('id');
	//alert("Vuoi cancellare id: " + tr_id);
	
	if (confirm("Vuoi davvero eliminare la congregazione con ID [" + tr_id + "]?"))
	{
	    $.post("group_delete.php",{group_id:tr_id,},
	    function(data,status)
	    {
		    //alert("Data: " + data + "\nStatus: " + status);
		    tr_obj.fadeOut(1000, function() 
		    {
			    tr_obj.remove();
			    $("#btn_group_delete").prop('disabled', true);
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

<h5 class="pull-right" style="margin-right: 3px;"><b><?= $mainactions->UserFullName(); ?></b>, bentornato! </h5>
<p><h4> La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b></h4></p>

<div class="container-fluid">
    <div class="panel panel-default">

    <div class="panel-heading">
	<button type="button" class="btn btn-danger" id="btn_group_delete">Elimina congregazione</button></div>

	<div class="panel-body">
	    <table class="table table-hover" id="groups_table">
	
	    <?php
	    
		$result = $dbactions->GetGroups();
		
		if ($result)
		{
		    echo '<tr class="head">';
			echo'<th></th><th>CONGREGAZIONE</th><th>ID</th><th>TIPO</th><th>RUOLO</th><th>PUBLISH CODE</th>';
		    echo '</tr>';
		    
		    while ($row = mysql_fetch_array($result))
		    {
			$values[0]=$row['group_name'];
			$values[1]=$row['group_id'];
			$values[2]=$row['group_type'];
			$values[3]=$row['group_role_name'];
			$values[4]=$row['publish_code'];
			
			echo '<tr class="groups_table" id="' .$values[1].'">';
				    echo '<td><input type="radio" name="group_selected" /></td>';
				    echo '<td>' . $values[0] . '</td>';
				    echo '<td>' . $values[1] . '</td>';
				    echo '<td>' . $values[2] . '</td>';
				    echo '<td>' . $values[3] . '</td>';
				    echo '<td>' . $values[4] . '</td>';
			echo '</tr>';
		    }
		    
		}
	    
	    ?>
	    </table>
	</div>
    </div>
</div>

</body>
</html>