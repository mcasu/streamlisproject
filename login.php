<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$userIsLogged = FALSE;
$is_submit = FALSE;

if(isset($_POST['submitted']))
{
    $is_submit = TRUE;
    $userIsLogged = $mainactions->Login();
}
else
{
    $userIsLogged = $mainactions->CheckLogin();
}

if($userIsLogged)
{      
     $user_role = $mainactions->GetSessionUserRole();
     error_log("INFO - User logged->[" . $mainactions->UserName() . "] ROLE->[" . $user_role . "]");
     if (!empty($user_role))
     {
         switch ($user_role) 
         {
             case "1": // admin
                 $utils->RedirectToURL("/admin/dashboard.php");
                 break;
             case "2": // viewer
                 $utils->RedirectToURL("/viewer/live-normal.php");
                 break;
             case "3": // publisher
                 $utils->RedirectToURL("/publisher/dashboard.php");
                 break;
             default:
                 break;
         }
     }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
   <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
   <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
   <meta name="viewport" content="width=device-width, initial-scale=1"/>
   <title>Stream LIS - Login</title>
   <link rel="stylesheet" href="/style/bootstrap.min.css">
   <link rel='stylesheet' type='text/css' href='/style/admin.css' />
   <script type="text/javascript" src="/js/jquery-1.11.0.min.js"></script>
   <script type='text/javascript' src='/js/jquery.validate.js'></script>
   <script src="/js/bootstrap.min.js"></script>
</head>

<body>
   
<div class="container-fluid">
   <div class="panel panel-default">
      
      <div class="panel-heading text-center" style="background-color: #333;">
	 <h1 class="panel-title" style="vertical-align: middle; font-size: 1.5em;">Benvenuto in Stream LIS</h1>
      </div>
   
      <div class="panel-body">
	    
	 <div class="col-sm-6 col-md-4 col-md-offset-4">
	     <div class="panel panel-default">
		<div class="panel-body">
		   <p class="text-center">
		      <img src="images/logo_flat.png" alt="Stream LIS" height="56" width="60">
		   </p>
		   <br/>
		   
		   <?php
		      if ($is_submit == TRUE && isset($userIsLogged) && $userIsLogged == false)
		      {
			 echo '<div class="alert alert-danger alert-dismissible" role="alert">';
			    echo '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;';
			       echo '</span><span class="sr-only">Close</span>';
			    echo '</button>'. $dbactions->GetErrorMessage();
			 echo '</div>';
		      }
		   ?>
		   
		   <form role="form" id='login_form' action='<?php echo $utils->GetSelfScript(); ?>' method='post'>
		      <fieldset>
			 
			 <input type='hidden' name='submitted' id='submitted' value='1'/>
			 
			    <div class="form-group">
			       <div class="control-group">
				   <!-- CAMPO USERNAME -->
				   <label for='username' >Nome utente:</label><br/>
				   <div class="controls">
				       <input required autofocus type="text" class="form-control" placeholder="Nome utente" name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="128" /><br/>
				   </div>
			       </div>
			       <div class="control-group">
				   <!-- CAMPO PASSWORD -->
				   <label for='password' >Password:</label><br/>
				   <div class="controls">
				       <input required type="password" class="form-control" placeholder="Password" name='password' id='password' maxlength="128"/><br/>
				   </div>
				  <label class="pull-right">
				     <a href='reset-pwd-req.php'>Password dimenticata?</a>
				  </label>
			       </div>
			   </div>
			 
			 <br/>
			 <br/>
			 
			 <button class="btn btn-lg btn-primary btn-block" type="submit">
			     <b>ENTRA</b></button>
			 
		      </fieldset>
		   </form>
		</div> <!-- FINE DIV PANEL-BODY INTERNO -->
	     </div>
	 </div>
      </div> <!-- FINE DIV PANEL-BODY ESTERNO -->
   </div> <!-- FINE DIV PANEL -->
</div> <!-- FINE DIV CONTAINER-FLUID -->
   

<script type='text/javascript'>

$('#login_form').validate(
    {
	rules: {
	    username: {
		required: true,
		minlength: 4
	    },
	    password: {
		required: true,
		minlength: 4 /* lasciato a 4 per retrocompatibilità - a regime portare a 8 */
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

</script>

</body>
</html>
