<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
   <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
   <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
   <meta name="viewport" content="width=device-width, initial-scale=1"/>
   <title>Conferma la tua registrazione</title>
   <link rel="stylesheet" href="/style/bootstrap.min.css">
   <link rel='stylesheet' type='text/css' href='/style/admin.css' />
   
   <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
   <script type='text/javascript' src='/js/jquery.validate.js'></script>
   <script src="/js/bootstrap.min.js"></script>
   <script type="text/javascript" src="/include/session.js"></script>
</head>

<body>

<div class="container-fluid">
   <div class="panel panel-primary pull-left">
      
      <div class="panel-heading">
	<h2 class="panel-title"><b>CONFERMA LA REGISTRAZIONE</b></h2>
      </div>
      
      <div class="panel-body">  
	 <form role="form" id='confirm' action='<?php echo $utils->GetSelfScript(); ?>' method='get' accept-charset='UTF-8'>
	    <fieldset>
	       
	       <div><span class='error'><?php echo $mainactions->GetErrorMessage(); ?></span></div>
	       
	       <div class="form-group">
		  <div class="control-group">
		      <label for='code' >Inserisci il codice di conferma:</label><br/>
		      <input required autofocus type='text' name='code' id='code' maxlength="50" /><br/>
		  </div>
	       </div>
	       
	       </br>
	       <div class="form-group btn_actions">
		   <button type="submit" name="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Conferma</button>
	       </div>
	    </fieldset>
	 </form>
      </div>

   </div>
</div>

<script type='text/javascript'>
    
jQuery(document).ready(function ()
{
    $('#confirm').validate(
    {
	rules: {
	    code: {
		required: true,
		minlength: 20
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
