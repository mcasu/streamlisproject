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
	
    <link rel="stylesheet" href="../style/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../style/jquery-ui.min.css"/>        
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../js/jquery.dataTables.min.js"></script>
    
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
            else
            {
                $("#btn_group_getlivelink").prop('disabled', true);
            }
        }
        else
        {
            $("#btn_group_delete").prop('disabled', true);
            $("#btn_group_getlivelink").prop('disabled', true);
        }
    }); 

    $("#btn_group_delete").click(function()
    {
        console.log("Numero gruppi selezionati: " + groupsTable.rows('.selected').data().length);
        
        var groupSelectedIds = $.map(groupsTable.rows('.selected').data(), function (row) 
        {
            return row[0];
        } );
        
        console.log("Group id selezionati: " + groupSelectedIds);
        
        if (confirm("Vuoi davvero eliminare i gruppi selezionati?"))
	{
            $.post("/include/functions.php",{fname:"groups_delete",groupIds:groupSelectedIds.toString()},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);
                
                if (status === "success")
                {
                    groupsTable.$('.selected').remove();
                    $("#btn_group_delete").prop('disabled', true);
                }
            });            
            
        }
    });        
    
    $("#divGroupsGetLiveLink").hide();
    $("#btn_group_getlivelink").click(function(e)
    {
        e.preventDefault();
        
        var groupGetLiveLinkDlg = $('#divGroupsGetLiveLink').dialog({
            title: 'Live link unico',
            resizable: true,
            autoOpen:false,
            modal: true,
            hide: 'fade',
            width:750,
            buttons: [
//               {
//                    text: "Copia",
//                    click: function() {
//                        $("#divEventsLiveViewLink div.alert-success").show();
//                   }
//               },
               {
                   text: "Chiudi",
                   click: function() {
                       //$("#divEventsLiveViewLink div.alert-success").hide();
                       $('#divGroupsGetLiveLink').dialog("close");
                   }
               }
            ]
        });        
        
        // Questa funzione è eseguibile solo quando è selezionato un singolo gruppo quindi il group_id sarà sempre 1
        var groupSelectedId = $.map(groupsTable.rows('.selected').data(), function (row) 
        {
            return row[0];
        } );
        
        console.log("Group id selezionato: " + groupSelectedId);
        
        $("#divGroupsGetLiveLink div.alert-success").hide();
        
        // Load live link for Desktop
        var liveLinkType = "desktop";
        $.post("/include/functions.php",{
                fname:"groups_get_live_link",
                groupId:groupSelectedId.toString(),
                liveLinkType:liveLinkType},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);

                if (status === "success")
                {
                    $('#inputGroupsGetLiveLinkDesktop').val(data);
                }
        });
        
        // Load live link for Smartphone
        var liveLinkType = "smartphone";
        $.post("/include/functions.php",{
                fname:"groups_get_live_link",
                groupId:groupSelectedId.toString(),
                liveLinkType:liveLinkType},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);

                if (status === "success")
                {
                    $('#inputGroupsGetLiveLinkSmartphone').val(data);
                }
        });        
        
        // Load live link for Iphone
        var liveLinkType = "iphone";
        $.post("/include/functions.php",{
                fname:"groups_get_live_link",
                groupId:groupSelectedId.toString(),
                liveLinkType:liveLinkType},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);

                if (status === "success")
                {
                    $('#inputGroupsGetLiveLinkIphone').val(data);
                }
        });         
        
        groupGetLiveLinkDlg.dialog('open');
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
                
	    </table>
	</div>
    </div>
    
    <div id="divGroupsGetLiveLink">
        <br/>
        <img class="video_imgdevice" src="../images/desktop.png" style="width: 40px; height: 40px; margin-bottom: 2px;"/>
        <input id="inputGroupsGetLiveLinkDesktop" class="form-control default-cursor" type="text" readonly>
        <br/>
        <img class="video_imgdevice" src="../images/os_android_old.png" style="width: 40px; height: 40px; margin-bottom: 2px;"/>
        <input id="inputGroupsGetLiveLinkSmartphone" class="form-control default-cursor" type="text" readonly>
        <br/>
        <img class="video_imgdevice" src="../images/os_apple_old.png" style="width: 40px; height: 40px; margin-bottom: 2px;"/>
        <input id="inputGroupsGetLiveLinkIphone" class="form-control default-cursor" type="text" readonly>
        <br/>
    </div>
</div>

</body>
</html>