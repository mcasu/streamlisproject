<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_admin.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Congregazioni</title>
	
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
    
    var selectedGroup = [];
    var groupsTable = $('#groups_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/f2c75b7247b/i18n/Italian.json"
        },
        "aoColumnDefs": [{ "bSortable": false, "aTargets": [ 0 ] }],
        "order": [[ 1, 'asc' ], [ 4, 'asc' ]],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/include/functions.php",
            "type": "POST",
            "data": { fname : "get_datatable_groups" }
        },
        "rowCallback": function( row, data )
        {
            if ( $.inArray(data.DT_RowId, selectedGroup) !== -1 ) 
            {
                $(row).addClass('selected');
            }
        }
    });    
    
//    $('#groups_table').DataTable({
//        "language": {
//            "url": "//cdn.datatables.net/plug-ins/f2c75b7247b/i18n/Italian.json"
//        },
//        "aoColumnDefs": [{ "bSortable": false, "aTargets": [ 0 ] }],
//        "order": [[ 1, 'asc' ]]
//    });
    
    $("#btn_group_delete").prop('disabled', true);
    $("#btn_group_getlivelink").prop('disabled', true);
    
    $('#groups_table tbody').on( 'click', 'tr', function () 
    {
        var id = this.id;
        var index = $.inArray(id, selectedGroup);
 
        if ( index === -1 ) 
        {
            selectedGroup.push( id );
        } 
        else 
        {
            selectedGroup.splice( index, 1 );
        }
        
        $(this).toggleClass('selected');
        
        var groupsTableRowSelected = groupsTable.rows('.selected').data().length;
        
        if (groupsTableRowSelected > 0)
        {
            var groupSelectedRole = $.map(groupsTable.rows('.selected').data(), function (row) 
            {
                return jQuery(row[4]).text();
            } );
                
            $("#btn_group_delete").prop('disabled', false);
            
            if (groupsTableRowSelected === 1)
            {
                if ( (groupSelectedRole.indexOf("Viewer") >= 0) )
                {
                    $("#btn_group_getlivelink").prop('disabled', true);
                }
                else
                {
                    $("#btn_group_getlivelink").prop('disabled', false);
                }
            }
        }
        else
        {
            $("#btn_group_delete").prop('disabled', true);
            $("#btn_group_getlivelink").prop('disabled', true);
        }
    }); 
    
        
//    $("input:radio").click(function(lastSelectedRow)
//    {
//	$("#groups_table").find("tr").removeClass("active");
//	
//	var isChecked = $(this).prop("checked");
//	var selectedRow = $(this).parent("td").parent("tr");
//    
//	if (isChecked)
//	{
//	    selectedRow.addClass("active");
//	    $("#btn_group_delete").prop('disabled', false);
//	    //selectedRow.css({ "background-color": "#D4FFAA", "color": "GhostWhite" });
//	}
//	else
//	{
//	    selectedRow.removeClass("active");
//	    //selectedRow.css({ "background-color": '', "color": "black" });
//	}
//	
//    });

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
<?php include("../include/header_admin.php"); ?>

<div class="container-fluid">
    <div class="panel panel-default">

    <h3>ELENCO CONGREGAZIONI ASSOCIATE:</h3>
    <br/>
    
    <div class="panel-heading">
	<button type="button" class="btn btn-danger" id="btn_group_delete">Elimina congregazione</button>
        <button type="button" class="btn btn-primary" id="btn_group_getlivelink"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Genera live URL</button>
    </div>

	<div class="panel-body">
	    <table class="table table-hover" id="groups_table">
                <thead>
                    <tr class="head">
                        <th>ID</th>
                        <th>CONGREGAZIONE</th>
                        <th>TIPO</th>
                        <th>RUOLO</th>
                        <th>PUBLISH CODE</th>
                    </tr>
                </thead>
                
	    <?php
	    
//		$result = $dbactions->GetGroups();
//		
//		if ($result)
//		{
//                    echo '<thead>';
//                        echo '<tr class="head">';
//                            echo'<th></th>';
//                            echo '<th>CONGREGAZIONE</th>';
//                            echo '<th>ID</th>';
//                            echo '<th>TIPO</th>';
//                            echo '<th>RUOLO</th>';
//                            echo '<th>PUBLISH CODE</th>';
//                        echo '</tr>';
//		    echo '</thead>';
//                    
//                    echo '<tbody>';
//		    while ($row = mysql_fetch_array($result))
//		    {
//			$values[0]=$row['group_name'];
//			$values[1]=$row['group_id'];
//			$values[2]=$row['group_type'];
//			$values[3]=$row['group_role_name'];
//			$values[4]=$row['publish_code'];
//			
//			echo '<tr class="groups_table" id="' .$values[1].'">';
//				    echo '<td><input type="radio" name="group_selected" /></td>';
//				    echo '<td>' . $values[0] . '</td>';
//				    echo '<td>' . $values[1] . '</td>';
//				    echo '<td>' . $values[2] . '</td>';
//                                    echo '<td>';
//                                        if ($values[3] == "publisher")
//                                        {
//                                            echo '<span class="label label-warning">' . $values[3] . '</span>';
//                                        }
//                                        else
//                                        {
//                                            echo '<span class="label label-default">' . $values[3] . '</span>';
//                                        }
//                                    echo '</td>';
//				    echo '<td>' . $values[4] . '</td>';
//			echo '</tr>';
//		    }
//                    echo '</tbody>';
//		}
	    
	    ?>
	    </table>
	</div>
    </div>
</div>

</body>
</html>