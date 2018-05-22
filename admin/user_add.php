<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_admin.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Nuovo utente</title>
    
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type='text/javascript' src='../js/jquery.validate.js'></script>
    <script type="text/javascript" src="../js/pwstrength-bootstrap-1.2.2.js"></script>
    
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</head>

<body>

<?php

include("../include/header_admin.php");

if(isset($_POST['submitted']))
{
   $UserHasCreated=$mainactions->CreateUser();
}

?>

<div class="container-fluid">
    <div class="panel panel-primary">
      
      <div class="panel-heading">
	<h2 class="panel-title" style="margin-top:10px;margin-left:6px;"><b>NUOVO UTENTE</b></h2>
      </div>
      
      <div class="panel-body">
	<form role="form" id="create_user_form" action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
	<fieldset >
	
	<div class="form-group btn_actions">
	    <button type="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Crea utente</button>
	    <button type="reset" class="btn btn-default btn-lg btn_action_reset">Cancella tutti i campi</button>
	</div>
	</br>
	<?php

	    if (isset($UserHasCreated) && $UserHasCreated)
	    {
		    echo '<br/><div class="alert alert-success" role="alert">';
			echo '<h4>Utente creato con successo!</h4>';
//			    echo 'Una mail di conferma verrà spedita all\'account di posta indicato.<br/>';
//			    echo 'Per completare la registrazione l\'utente deve cliccare sul link all\'interno della mail.';
			    echo '<br/>';
			    echo '<button type="button" class="btn btn-success btn_action_reload">Crea un altro utente</button>';
		    echo '</div>';
	    }
	    elseif(isset($UserHasCreated))
	    {
		echo '<br/><div class="alert alert-danger" role="alert">';
		    echo '<h4><b>Creazione utente fallita!</b></h4>';
		    echo '<i>'.$mainactions->GetErrorMessage().'</i>';
		    echo '<h5>Modifica i dati inseriti oppure clicca sul pulsante qui sotto per azzerare i campi.</h5>';
		    echo '<button type="button" class="btn btn-danger btn_action_reload">Azzera</button>';
		echo '</div>';
	    }
	?>
	    
	<input type='hidden' name='submitted' id='submitted' value='1'/>
	<input type='text' class='spmhidip' name='<?php echo $utils->GetSpamTrapInputName($mainactions->randomKey); ?>' />
	
	<div class="form-group">
	    <div class="control-group">
		<!-- CAMPO NOME COMPLETO -->
		<label for='name' >Nome utente completo:</label><br/>
		<div class="controls">
		    <input type="text" class="form-control" placeholder="Nome utente completo" name='name' id='name' value='<?php echo $utils->SafeDisplay('name') ?>' maxlength="128" /><br/>
		</div>
	    </div>
	    <div class="control-group">
		<!-- CAMPO INDIRIZZO EMAIL -->
		<label for='email' >Indirizzo email:</label><br/>
		<div class="controls">
		    <input type="email" class="form-control" placeholder="Indirizzo Email" name='email' id='email' value='<?php echo $utils->SafeDisplay('email') ?>' maxlength="128" /><br/>
		</div>
	    </div>
	</div>
	
	<div class="form-group">
	    <div class="control-group">
		<!-- CAMPO USERNAME -->
		<label for='username' >Username:</label><br/>
		<div class="controls">
		    <input type="text" class="form-control" placeholder="Username" name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="128" /><br/>
		</div>
	    </div>
	    <div class="control-group">
		<!-- CAMPO PASSWORD -->
		<label for='password' >Password:</label><br/>
		<div class="controls">
		    <input type="password" class="form-control" placeholder="Password" name='password' id='password' maxlength="128"/><br/>
		</div>
	    </div>
	</div>
	
	<div class="form-group">
	    <!-- CAMPO CONGREGAZIONE -->
	    <label for='groups' >Congregazione:</label><br/>
	    <select class="form-control" name="group_name" id="group_name">
	<?php
	    try
	    {
		/*** query the database ***/
		$result = $dbactions->GetGroups();
	
		if (!$result)
		{
			error_log("No Results");
		}
	
		while($row = mysql_fetch_array($result))
		{
			$group=$row['group_name'];
			echo '<option value="' . $group . '">' . $group . '</option>"';
		}
	    }
	    catch(PDOException $e)
	    {
		echo 'No Results';
	    }
	?>
	</select>
	<br/>

	<!-- CAMPO TIPO UTENTE -->
        <label for='roles' >Tipo di utente:</label><br/>
        <select class="form-control" name="user_role_name" id="user_role_name">
	<?php
	    try
	    {
		/*** query the database ***/
		$result = $dbactions->GetUserRoles();
	
		if (!$result)
		{
			error_log("No Results");
		}
	
		while($row = mysql_fetch_array($result))
		{
			$user_role_name=$row['user_role_name'];
			echo '<option value="' . $user_role_name . '">' . $user_role_name . '</option>"';
		}
	    }
	    catch(PDOException $e)
	    {
		echo 'No Results';
	    }
	?>
	</select>
	
	</br>
	</br>
	</br>
	<div class="form-group btn_actions">
	    <button type="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Crea utente</button>
	    <button type="reset" class="btn btn-default btn-lg btn_action_reset">Cancella tutti i campi</button>
	</div>
    
	</fieldset>
	</form>
    </div>
</div>

<script type='text/javascript'>
    
jQuery(document).ready(function ()
{
    var options = {};
    options.common =
    {
	minChar: 8,
	bootstrap3: true,
	usernameField: "#username",
    };

    $.validator.addMethod("pwcheck", function(value, element) 
    {
        //var pattern = /^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%&]).*$/;
        var pattern = /^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/;
        return pattern.test(value);
    });
    
    options.rules =
    {
	activated: {
	    wordTwoCharacterClasses: true,
	    wordRepetitions: true,
	    wordSimilarToUsername: true,
	    wordOneSpecialChar: true,
            pwcheck: true
	}
    };
    
    options.ui =
    {
	showErrors: true,
	errorMessages:
	{
	    wordLength: "La password è troppo corta",
	    wordNotEmail: "Non puoi usare un indirizzo email come password",
	    wordSimilarToUsername: "La password non può contenere il tuo username",
	    wordTwoCharacterClasses: "Use different character classes",
	    wordRepetitions: "Troppi caratteri ripetuti",
	    wordSequences: "Non puoi usare 3 caratteri successivi. (Es. 'abc' o '123')",
            pwcheck: "Password non valida. Ricorda di inserire almeno un carattere minuscolo, uno maiuscolo e un numero."
	}
	
    };
    
    $(':password').pwstrength(options);
    
    $('#create_user_form').validate(
    {
	rules: {
	    name: {
		required: true,
		minlength: 4
	    },
	    email: {
		required: true,
		email: true
	    },
	    username: {
		required: true,
		minlength: 6
	    },
	    password: {
		required: true,
                pwcheck: true,
		minlength: 8
	    }
	},
        
        messages: {
            password: {
                required: "Per creare un utente devi inserire una password valida.",
                pwcheck: "Password non valida. Ricorda di inserire almeno un carattere minuscolo, uno maiuscolo e un numero.",
                minlength: "La password deve essere di almeno 8 caratteri." 
                }
        },
	
	highlight: function(element)
	{
	    $(element).closest('.control-group').removeClass('success').removeClass('has-success').addClass('error');
	},
	
	success: function(element)
	{
	    element.addClass('valid').closest('.control-group').removeClass('error').addClass('success').addClass('has-success');
	}
    });



    if( $('.alert').is(':visible') )
    {
	$('.btn_action_reset').attr('disabled', "disabled");
    }
    if( $('.alert-success').is(':visible') )
    {
	$('.btn_actions').hide();
	$('input').attr('disabled',true);
	$('select').attr('disabled',true);
    }
    $('.btn_action_reload').click(function()
    {
	var url = "user_add.php";
	$(location).attr('href',url);
    });

});

</script>

    <?php include("../include/footer.php"); ?>
</body>
</html>
