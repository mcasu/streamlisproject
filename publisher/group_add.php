<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_publisher.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Nuova congregazione</title>
    
    <link rel="stylesheet" href="../style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />

    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type='text/javascript' src='../js/jquery.validate.js'></script>
    
    <script type="text/javascript" src="../include/session.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    
    <script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"it","siteId":1168862,"cookiePolicyId":74934126,"banner":{"textColor":"#fff","backgroundColor":"#333"}}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script>
</head>

<body>

<?php

include("../include/header_publisher.php");

if(isset($_POST['submitted']))
{
   $GroupHasCreated = $mainactions->CreateGroup(TRUE);
}

?>

<div class="container-fluid">
    <div class="panel panel-primary">
      
      <div class="panel-heading">
	<h2 class="panel-title" style="margin-top:10px;margin-left:6px;"><b>NUOVO GRUPPO</b></h2>
      </div>
      
      <div class="panel-body">
	<form role="form" id="create_group_form" action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
	<fieldset >
	
	<div class="form-group btn_actions">
	    <button type="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Crea gruppo</button>
	    <button type="reset" class="btn btn-default btn-lg btn_action_reset">Cancella tutti i campi</button>
	</div>
	</br>
	<?php

	    if (isset($GroupHasCreated) && $GroupHasCreated)
	    {
		    echo '<br/><div class="alert alert-success" role="alert">';
			echo '<h4>Gruppo creato con successo!</h4>';
			    echo '<br/>';
			    echo '<br/>';
			    echo '<button type="button" class="btn btn-success btn_action_reload">Crea un altro gruppo</button>';
		    echo '</div>';
	    }
	    elseif(isset($GroupHasCreated))
	    {
		echo '<br/><div class="alert alert-danger" role="alert">';
		    echo '<h4><b>Creazione del gruppo fallita!</b></h4>';
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
		<!-- CAMPO NOME GRUPPO -->
		<label for='group_name' >Nome gruppo:</label><br/>
		<div class="controls">
		    <input type="text" class="form-control" placeholder="Nome gruppo" name='group_name' id='group_name' value='<?php echo $utils->SafeDisplay('group_name') ?>' maxlength="128" /><br/>
		</div>
	    </div>
	</div>
	
	<br/>
	<br/>
	<div class="form-group btn_actions">
	    <button type="submit" class="btn btn-primary btn-lg btn_action_create" style="margin-left:10px;margin-right:4px;">Crea gruppo</button>
	    <button type="reset" class="btn btn-default btn-lg btn_action_reset">Cancella tutti i campi</button>
	</div>
    
	</fieldset>
	</form>
    </div>
</div>

<script type='text/javascript'>
    
jQuery(document).ready(function ()
{
    jQuery.validator.addMethod("match", function(value, element, params) {  
            return this.optional(element) || (value === params[0] || value === params[1]);
        } 
        //jQuery.validator.format("Please enter the correct value for {0} + {1}")
    );
    
    $('#create_group_form').validate(
    {
	rules: {
	    group_name: {
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
	$('select').attr('disabled',true);
    }
    $('.btn_action_reload').click(function()
    {
	var url = "group_add.php";        
	$(location).attr('href',url);
    });
    
});
</script>

    <?php include("../include/footer.php"); ?>
</body>
</html>