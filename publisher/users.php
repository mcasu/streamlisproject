<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_publisher.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Utenti</title>
	
    <link rel="stylesheet" href="../style/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="../style/jquery-ui.min.css"/>
    <link rel="stylesheet" href="../style/bootstrap.min.css"/>
    <link rel='stylesheet' type='text/css' href='../style/admin.css'/>
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type='text/javascript' src='../js/jquery.validate.js'></script>
    <script type="text/javascript" src="../include/session.js"></script>
    <script type="text/javascript" src="../js/jquery.dataTables.min.js"></script>
    
    <script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"it","siteId":1168862,"cookiePolicyId":74934126,"banner":{"textColor":"#fff","backgroundColor":"#333"}}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script>
</head>

<body>
<?php include("../include/header_publisher.php"); ?>

<input type="hidden" class="groupid" id="<?=$mainactions->UserGroupId();?>"/>;
    
<div class="container-fluid">
    <div class="panel panel-default">

        <h3>ELENCO UTENTI ASSOCIATI:</h3>
        <br/>
        
        <div class="panel-heading">
            <button type="button" class="btn btn-danger" id="btn_user_delete"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Elimina utente</button>
            <button type="button" class="btn btn-primary" id="btn_user_resetpwd"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Reset password</button>
        </div>

        <div class="panel-body">
            <input class="inputUserData" id="<?= $mainactions->UserId(); ?>" type="hidden"/>
            <div id="resetpwd_alert_success" class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h3>Reset password effettuato con successo!</h3>
                <h5>Le nuove credenziali sono state spedite via mail all'indirizzo <?= $mainactions->UserEmail(); ?></h5>
            </div>
            <div id="resetpwd_alert_fail" class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h3>Reset password fallito!</h3>
                <h5>Contatta l'amministratore di sistema per risolvere il problema.</h5>
            </div>
            
            <table class="table table-hover" id="users_table">
                <thead>
                    <tr class="head">
                        <th>ID</th>
                        <th>NOME</th>
                        <th>USERNAME</th>
                        <th>CONGREGAZIONE</th>
                        <th>TIPO</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
</div>

<script type="text/javascript">
    
$(document).ready(function()
{
    var groupId = $('.groupid').attr('id');
    var selectedUser = [];
    var usersTable = $('#users_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/f2c75b7247b/i18n/Italian.json"
        },
        "aoColumnDefs": [{ "bSortable": true, "aTargets": [ 0 ] }],
        "order": [[ 1, 'asc' ], [ 3, 'asc' ]],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/include/functions.php",
            "type": "POST",
            "data": { fname : "get_datatable_users", groupId : groupId }
        },
        "rowCallback": function( row, data )
        {
            if ( $.inArray(data.DT_RowId, selectedUser) !== -1 ) 
            {
                $(row).addClass('selected');
            }
        }
    });
    
    $("#btn_user_delete").prop('disabled', true);
    $("#btn_user_resetpwd").hide();
    $("#resetpwd_alert_success").hide();
    $("#resetpwd_alert_fail").hide();
    
    $('#users_table tbody').on( 'click', 'tr', function () 
    {
        var id = this.id;
        var index = $.inArray(id, selectedUser);
 
        if ( index === -1 ) 
        {
            selectedUser.push( id );
        } 
        else 
        {
            selectedUser.splice( index, 1 );
        }
        
        $(this).toggleClass('selected');
        
        var usersTableRowSelected = usersTable.rows('.selected').data().length;
        
        if (usersTableRowSelected > 0)
        {
            var userSelectedRole = $.map(usersTable.rows('.selected').data(), function (row) 
            {
                return jQuery(row[5]).text();
            } );
                
            if ( (userSelectedRole.indexOf("Viewer") >= 0) || (userSelectedRole.indexOf("Publisher") >= 0) )
            {
                $("#btn_user_delete").prop('disabled', false);
            }
            
            if (usersTableRowSelected === 1)
            {
                if ( (userSelectedRole.indexOf("Viewer") >= 0) || (userSelectedRole.indexOf("Publisher") >= 0) )
                {
                    $("#btn_user_resetpwd").show();
                }
            }
            else
            {
                $("#btn_user_resetpwd").hide();
            }
        }
        else
        {
            $("#btn_user_resetpwd").hide();
            $("#btn_user_delete").prop('disabled', true);
        }
    });    

    $("#btn_user_delete").click(function()
    {
        console.log("Numero utenti selezionati: " + usersTable.rows('.selected').data().length);
        
        var userSelectedIds = $.map(usersTable.rows('.selected').data(), function (row) 
        {
            return row[0];
        } );
        
        console.log("User id selezionati: " + userSelectedIds);
        
        if (confirm("Vuoi davvero eliminare gli utenti selezionati?"))
	{
            $.post("/include/functions.php",{fname:"users_delete",userIds:userSelectedIds.toString()},
            function(data,status)
            {
                //alert("Data: " + data + "\nStatus: " + status);
                
                if (status === "success")
                {
                    usersTable.$('.selected').remove();
                    $("#btn_user_delete").prop('disabled', true);
                }
            });            
            
        }
    });

    $("#btn_user_resetpwd").click(function()
    {
        var userSelectedId = $.map(usersTable.rows('.selected').data(), function (row) 
        {
            return row[0];
        } );
        
        var userSelectedName = $.map(usersTable.rows('.selected').data(), function (row) 
        {
            return row[1];
        } );
        
        if (confirm("Vuoi davvero cambiare la password dell'utente " + userSelectedName + " (ID #" + userSelectedId + ") ?"))
	{
            $("#resetpwd_alert_success").hide();
            $("#resetpwd_alert_fail").hide();
    
            var userAdminId = $('.inputUserData').attr('id');
            var userId = userSelectedId[0];
            $.post("../include/functions.php",{
                fname:"users_resetpwd",
                userToResetId:userId, 
                userAdminId:userAdminId},
	    function(data,status)
	    {
		    //alert("Data: " + data + "\nStatus: " + status);
                    if (status === "success")
                    {
                        $("#resetpwd_alert_success").show();
                    }
                    else
                    {
                        $("#resetpwd_alert_fail").show();
                    }
	    });
        }
    });
    
});

</script>
    
    <?php include("../include/footer.php"); ?>
</body>
</html>