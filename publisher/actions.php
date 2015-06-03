<?php include("../check_login.php"); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Operazioni</title>
	
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel="stylesheet" href="../style/jquery.dataTables.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</head>


<body>
<?php include("../include/header_publisher.php"); ?>


<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="pull-right btn_actions">
                <button type="button" class="btn btn-danger btn_actions_delete" style="margin-right:4px;" id="btn_actions_delete">Elimina operazione</button>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="panel-body" id="ondemand_actions_join_panelbody">
            <table class="table table-hover" id="ondemand_actions_join_table">
                <thead>
                    <tr class="head">
                        <th>ID OPERAZIONE</th>
                        <th>ONDEMAND VIDEO DA UNIRE</th>
                        <th>STATO OPERAZIONE</th>
                    </tr>
                </thead>
                
            </table>
        </div>
        
        <div class="panel-footer">
            <div class="pull-right btn_actions">
                <button type="button" class="btn btn-danger btn_actions_delete" style="margin-right:4px;" id="btn_actions_delete">Elimina operazione</button>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<script src="../js/jquery.dataTables.min.js"></script> 
<script type="text/javascript">
    
$(document).ready(function()
{
    $(".btn_actions").find(".btn").attr('disabled',true);
    
    var joinTable = $('#ondemand_actions_join_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/f2c75b7247b/i18n/Italian.json"
        },
        "aoColumnDefs": [{ "bSortable": false, "aTargets": [ 0 ] }],
        "order": [[ 2, 'asc' ]],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/include/functions.php?fname=get_datatable_ondemand_actions_join",
            "type": "POST"
        },
        "rowCallback": function( row, data ) 
        {
            if ( $.inArray(data.DT_RowId, selected) !== -1 ) 
            {
                $(row).addClass('selected');
            }
        }
    });
    
    $('#ondemand_actions_join_table tbody').on( 'click', 'tr', function () 
    {
        var id = this.id;
        var index = $.inArray(id, selected);
 
        if ( index === -1 ) 
        {
            selected.push( id );
        } 
        else 
        {
            selected.splice( index, 1 );
        }
        
        $(this).toggleClass('selected');
        
        var joinTableRowSelected = joinTable.rows('.selected').data().length;
        
        if (joinTableRowSelected > 0)
        {
            $(".btn_actions").find(".btn").attr('disabled',false);
        }
        else
        {
            $(".btn_actions").find(".btn").attr('disabled',true);
        }
    });
    
    $(".btn_actions_delete").click(function()
    {
        console.log("Numero record selezionati: " + joinTable.rows('.selected').data().length);
        
        //var joinSelectedIds = [];
        var joinSelectedIds = $.map(joinTable.rows('.selected').data(), function (row) 
        {
            return row[0];
            
            //joinSelectedIds.push(value);
        } );
        
        console.log("Data: " + joinSelectedIds);
        
        if (confirm("Vuoi davvero eliminare le operazioni selezionate?"))
	{
            $.post("/include/functions.php",{fname:"delete_ondemand_actions_join",joinSelectedIds:joinSelectedIds.toString()},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);
                
                if (status === "success")
                {
                    joinTable.$('.selected').remove();
                }
            });            
            
        }
    });
    
});

</script>

    
</body>
</html>