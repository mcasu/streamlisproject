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
    <title>Stream LIS - Utenti</title>
	
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
    var selectedUser = [];
    var usersTable = $('#users_table').DataTable({
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
            "data": { fname : "get_datatable_users" }
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
    $("#btn_user_edit").hide();
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
            $("#btn_user_delete").prop('disabled', false);
            
            if (usersTableRowSelected === 1)
            {
                var userSelectedRole = $.map(usersTable.rows('.selected').data(), function (row) 
                {
                    return jQuery(row[5]).text();
                } );
                
                alert("RUOLO: " + userSelectedRole);
                
                if ( (userSelectedRole.indexOf("Viewer") >= 0) || (userSelectedRole.indexOf("Publisher") >= 0) )
                {
                    $("#btn_user_resetpwd").show();
                }
                
                $("#btn_user_edit").show();
            }
            else
            {
                $("#btn_user_resetpwd").hide();
                $("#btn_user_edit").hide();
            }
        }
        else
        {
            $("#btn_user_resetpwd").hide();
            $("#btn_user_edit").hide();
            $("#btn_user_delete").prop('disabled', true);
        }
    });    

    $("#btn_user_delete").click(function()
    {
	var tr_obj = $('input[name=user_selected]:checked').parent("td").parent("tr");
	
	var tr_id=tr_obj.attr('id');
	//alert("Vuoi cancellare id: " + tr_id);
	
	if (confirm("Vuoi davvero eliminare l'utente con ID [" + tr_id + "]?"))
	{
	    $.post("user_delete.php",{user_id:tr_id},
	    function(data,status)
	    {
		    //alert("Data: " + data + "\nStatus: " + status);
		    tr_obj.fadeOut(1000, function() 
		    {
			    tr_obj.remove();
			    $("#btn_user_delete").prop('disabled', true);
		    });
	    });
	}
    });

    $("#btn_user_resetpwd").click(function()
    {
        var tr_obj = $('input[name=user_selected]:checked').parent("td").parent("tr");
	var tr_id=tr_obj.attr('id');
        
        if (confirm("Vuoi davvero cambiare la password dell'utente con ID [" + tr_id + "]?"))
	{
            $("#resetpwd_alert_success").hide();
            $("#resetpwd_alert_fail").hide();
    
            var userAdminId = $('.inputUserData').attr('id');
            $.post("../include/functions.php",{fname:"users_resetpwd", userId:tr_id, userAdminId:userAdminId},
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
    
</head>


<body>
<?php include("../include/header_admin.php"); ?>


<div class="container-fluid">
    <div class="panel panel-default">

        <h3>ELENCO UTENTI ASSOCIATI:</h3>
        <br/>
        
        <div class="panel-heading">
            <button type="button" class="btn btn-danger" id="btn_user_delete"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Elimina utente</button>
            <button type="button" class="btn btn-primary" id="btn_user_resetpwd"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Reset password</button>
            <button type="button" class="btn btn-primary" id="btn_user_edit"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Modifica</button>
        </div>

        <div class="panel-body">
            <input class="inputUserData" id="<?= $mainactions->UserId(); ?>" type="hidden"/>
            <div id="resetpwd_alert_success" class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h3>Reset password effettuato con successo!</h3>
                <h5>Le nuove credenziali sono state spedite al tuo account di posta elettronica.</h5>
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
                        <th>MAIL</th>
                        <th>USERNAME</th>
                        <th>CONGREGAZIONE</th>
                        <th>TIPO</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

</body>
</html>