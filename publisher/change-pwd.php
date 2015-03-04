<?php include("/check_login.php"); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Cambia la password</title>
    <link rel="stylesheet" href="/style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='/style/admin.css' />
    
    <script type="text/javascript" src="/js/jquery-1.11.0.min.js"></script>
    <script type='text/javascript' src='/js/jquery.validate.js'></script>
    <script type="text/javascript" src="/js/pwstrength-bootstrap-1.2.2.js"></script>
    
    <script type="text/javascript" src="/include/session.js"></script>
    <script src="/js/bootstrap.min.js"></script>
</head>

<body>

<?php

include("header.php");
   
if(isset($_POST['submitted']))
{
    $PasswordChanged = $mainactions->ChangePassword();
}

?>
</br>
<h5 class="pull-right" style="margin-right: 3px;"><b><?= $mainactions->UserFullName(); ?></b>, bentornato! </h5>
<p><h4> La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b></h4></p>
</br>

<div class="container-fluid">
    <div class="panel panel-primary">
        
    <div class="panel-body">
    <form role="form" id='changepwd' action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
        <fieldset></fieldset>
    
            <input type='hidden' name='submitted' id='submitted' value='1'/>
            
            <?php

                if ($PasswordChanged)
                {
                        echo '<br/><div class="alert alert-success" role="alert">';
                            echo '<h4>Password cambiata con successo!</h4>';
                            echo '<br/>';
                            echo '<p>';
                                echo '<a href="/logout.php">Ritorna alla pagina di login.</a>';
                            echo '</p>';
                        echo '</div>';
                }
                elseif(isset($PasswordChanged))
                {
                    echo '<br/><div class="alert alert-danger" role="alert">';
                        echo '<h4><b>Procedura di cambio password fallita!</b></h4>';
                        echo '<i>'.$mainactions->GetErrorMessage().'</i>';
                        echo '<h5>Modifica i dati inseriti oppure clicca sul pulsante qui sotto per azzerare i campi.</h5>';
                        echo '<button type="button" class="btn btn-danger btn_action_reload">Azzera</button>';
                    echo '</div>';
                }
            ?>
 
            <div class="form-group">
                <input type='hidden' name='username' id='username' value='<?= $mainactions->UserName(); ?>'/>
                <div class="control-group">
                    <!-- CAMPO PASSWORD ATTUALE -->
                    <label for='oldpassword' >Password attuale:</label><br/>
                    <div class="controls">
                        <input required autofocus type="password" class="form-control" placeholder="Password attuale" name='oldpassword' id='oldpassword' maxlength="128" /><br/>
                    </div>
                </div>
                <div class="control-group">
                    <!-- CAMPO PASSWORD NUOVA -->
                    <label for='password' >Password nuova:</label><br/>
                    <div class="controls">
                        <input required type="password" class="form-control" placeholder="Password nuova" name='newpassword' id='newpassword' maxlength="128"/><br/>
                    </div>
                </div>
            </div>
            
            <br/><br/>
            <div class="form-group btn_actions">
                        <button type="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Cambia la password</button>
                        <button type="reset" class="btn btn-default btn-lg btn_action_reset">Cancella tutti i campi</button>
            </div>
    
        </fieldset>
    </form>
    </div>
    
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
    
    options.rules =
    {
	activated: {
	    wordTwoCharacterClasses: true,
	    wordRepetitions: true,
	    wordSimilarToUsername: true,
	    wordOneSpecialChar: true,
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
	    wordSequences: "Non puoi usare 3 caratteri successivi. (Es. 'abc' o '123')"  
	},
	
    };
    
    $('#newpassword').pwstrength(options);
    
    $('#changepwd').validate(
    {
	rules: {
	    oldpassword: {
		required: true
	    },
	    newpassword: {
		required: true,
		minlength: 8
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
    }
    $('.btn_action_reload').click(function()
    {
	var url = "change-pwd.php";
	$(location).attr('href',url);
    });

});
</script>

</body>
</html>
