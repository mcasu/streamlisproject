<?PHP
require_once("./include/config.php");

$utils = $mainactions->GetUtilsInstance();

if(isset($_POST['submitted']))
{
   $PasswordResetted = $mainactions->EmailResetPasswordLink();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
   <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
   <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
   <meta name="viewport" content="width=device-width, initial-scale=1"/>
   <title>Genera nuova password</title>
   
   <link rel="stylesheet" href="/style/bootstrap.min.css">
   <link rel='stylesheet' type='text/css' href='/style/admin.css' />
   <script type="text/javascript" src="/js/jquery-1.11.0.min.js"></script>
   <script type='text/javascript' src='/js/jquery.validate.js'></script>
   <script src="/js/bootstrap.min.js"></script>
</head>
<body>
   
<div class="container-fluid">
   <div class="panel panel-default pull-left">
      
      <div class="panel-heading text-center" style="background-color: #333;">
	 <h1 class="panel-title">Richiedi una nuova password</h1>
      </div>
   
      <div class="panel-body">
         <form role="form" id='login_resetpwd' action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
            <fieldset>
               <input type='hidden' name='submitted' id='submitted' value='1'/>

               <?php
               
                  if(isset($PasswordResetted) && $PasswordResetted)
                  {
                     echo '<br/><div class="alert alert-success" role="alert">';
                        echo '<h4>Richiesta di nuova password generata con successo!</h4>';
                            echo 'Abbiamo spedito al tuo account di posta una mail con il link per attivare la nuova password.<br/>';
                            echo 'Per completare la procedura devi aprire la mail e cliccare sul link.';
                            echo '<p>';
                                echo '<a href="/login.php">Vai alla pagina di login.</a>';
                            echo '</p>';
                    echo '</div>';
                  }
                  elseif(isset($PasswordResetted))
                  {
                     echo '<div class="alert alert-danger alert-dismissible" role="alert">';
                        echo '<h4>Richiesta di nuova password fallita!</h4>';
                        echo '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;';
                           echo '</span><span class="sr-only">Close</span>';
                        echo '</button>'. $mainactions->GetErrorMessage();
                     echo '</div>';
                  }
               ?>
      
               <div class="form-group">
                  <div class="control-group">
                      <!-- CAMPO USERNAME -->
                      <label for='username' >Inserisci il tuo nome utente:</label><br/>
                      <div class="controls">
                          <input required autofocus type="text" class="form-control" placeholder="Nome utente" name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="128" /><br/>
                      </div>
                  </div>
               </div>
               <div class="alert alert-info" role="alert">
                  <h5>Cliccando sul tasto <i><b>Mandami la nuova password</b></i> ti verrà inviata una mail con un link per fare il reset della password.</h5>
               </div>
               </br>
               </br>
               <div class="form-group btn_actions">
                   <button type="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Mandami la nuova password</button>
               </div>
            </fieldset>
         </form>
         
      </div>
   </div>
</div>

<script type='text/javascript'>
    
jQuery(document).ready(function ()
{
    $('#login_resetpwd').validate(
    {
	rules: {
	    username: {
		required: true,
		minlength: 4
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
});

</script>

</body>
</html>
