<?php include("../check_login.php"); ?>

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
    $('#users_table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/f2c75b7247b/i18n/Italian.json"
        },
        "aoColumnDefs": [{ "bSortable": false, "aTargets": [ 0 ] }],
        "order": [[ 1, 'asc' ], [ 4, 'asc' ]]
    });
    
    $("#btn_user_delete").prop('disabled', true);
    $("#btn_user_resetpwd").hide();
    $("#resetpwd_alert_success").hide();
    $("#resetpwd_alert_fail").hide();
    
    $("input:radio").click(function(lastSelectedRow)
    {
        $("#btn_user_resetpwd").hide();
        
	$("#users_table").find("tr").removeClass("active");
	
	var isChecked = $(this).prop("checked");
	var selectedRow = $(this).parent("td").parent("tr");
    
	if (isChecked)
	{
	    selectedRow.addClass("active");
	    $("#btn_user_delete").prop('disabled', false);
            
            var role = selectedRow.find(".userRole").attr('name');
            if (role === "normal" || role === "publisher")
            {
                $("#btn_user_resetpwd").show();
            }
	    //selectedRow.css({ "background-color": "#D4FFAA", "color": "GhostWhite" });
	}
	else
	{
	    selectedRow.removeClass("active");
	    //selectedRow.css({ "background-color": '', "color": "black" });
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

<br/>

<h5 class="pull-right" style="margin-right: 3px;"><b><?= $mainactions->UserFullName(); ?></b>, bentornato! </h5>
<p><h4 style="margin-left:4px;">La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b></h4></p>

<div class="container-fluid">
    <div class="panel panel-default">

        <h3>ELENCO UTENTI ASSOCIATI:</h3>
        <br/>
        
        <div class="panel-heading">
            <button type="button" class="btn btn-danger" id="btn_user_delete">Elimina utente</button>
            <button type="button" class="btn btn-primary" id="btn_user_resetpwd">Reset password</button>
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

            <?php

                $result = $dbactions->GetUsers();

                if ($result)
                {
                    echo '<thead>';
                        echo '<tr class="head">';
                            echo'<th></th>';
                            echo '<th>NOME</th>';
                            echo '<th>ID</th>';
                            echo '<th>MAIL</th>';
                            echo '<th>USERNAME</th>';
                            echo '<th>CONGREGAZIONE</th>';
                            echo '<th>TIPO</th>';
                        echo '</tr>';
                    echo '</thead>';
                    
                    echo '<tbody>';
                    $index = 0;
                    while ($row = mysql_fetch_array($result))
                    {
                        $values[0]=$row['user_name'];
                        $values[1]=$row['user_id'];
                        $values[2]=$row['user_mail'];
                        $values[3]=$row['username'];
                        $values[4]=$row['user_group_name'];
                        $values[5]=$row['user_role_name'];

                        echo '<tr class="users_table" id="' .$values[1].'">';
                                    echo '<td><input type="radio" name="user_selected" /></td>';
                                    echo '<td>' . $values[0] . '</td>';
                                    echo '<td>' . $values[1] . '</td>';
                                    echo '<td>' . $values[2] . '</td>';
                                    echo '<td>' . $values[3] . '</td>';
                                    echo '<td>' . $values[4] . '</td>';
                                    echo '<td class="userRole" name="' . $values[5] . '">';
                                        if ($values[5] == "admin")
                                        {
                                            echo '<span class="label label-success">' . $values[5] . '</span>';
                                        }
                                        elseif ($values[5] == "publisher")
                                        {
                                            echo '<span class="label label-warning">' . $values[5] . '</span>';
                                        }
                                        else
                                        {
                                            echo '<span class="label label-default">' . $values[5] . '</span>';
                                        }
                                    echo '</td>';
                        echo '</tr>';
                        
                        $index++;
                    }
                    echo '</tbody>';

                }

            ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>