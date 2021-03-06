<?PHP
session_start();
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
     //error_log("INFO - Login.php User logged->[" . $mainactions->UserName() . "] ROLE->[" . $user_role . "]");
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
    <meta name="google-site-verification" content="35f4R1fOBfkiDv-s7lKoBtDb55-bBAN__eh6gCk6xxQ" />
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Login</title>
    <link rel="stylesheet" href="//www.streamlis.it/style/bootstrap.min.css">
    <link rel='stylesheet' type='text/css' href='//www.streamlis.it/style/admin.css' />
    <script type="text/javascript" src="//www.streamlis.it/js/jquery-1.11.0.min.js"></script>
    <script type='text/javascript' src='//www.streamlis.it/js/jquery.validate.js'></script>
    <script src="//www.streamlis.it/js/bootstrap.min.js"></script>

    <script type="text/javascript"> var _iub = _iub || []; _iub.csConfiguration = {"lang":"it","siteId":1168862,"cookiePolicyId":74934126,"banner":{"textColor":"#fff","backgroundColor":"#333"}}; </script><script type="text/javascript" src="//cdn.iubenda.com/cookie_solution/safemode/iubenda_cs.js" charset="UTF-8" async></script> 
</head>

<body>
   
<div class="container-fluid">
   <div class="panel panel-default">
      
      <div class="panel-heading text-center" style="background-color: #333;">
	 <h1 class="panel-title" style="vertical-align: middle; font-size: 1.5em;">Benvenuto in Stream LIS</h1>
      </div>
   
      <div class="panel-body">
	    
<!--           <script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script>
          <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;
                </span><span class="sr-only">Close</span>
            </button>
            <h4 class="text-justify">Gentile Utente, </br></br>ti informiamo che abbiamo aggiornato il documento relativo alla "Privacy Policy", in ottemperanza alla prossima entrata in vigore della nuova normativa europea in materia di protezione dei dati personali UE 2016/679 (GDPR).</br>
         Il documento è consultabile alla pagina  <a href="https://www.iubenda.com/privacy-policy/74934126" class="iubenda-black iubenda-embed" title="Privacy Policy"><b>Privacy Policy</b></a> del nostro sito web.</br></br>
         Saluti fraterni dal team StreamLIS :)</h4></div> -->
         
	 <div class="col-sm-6 col-md-4 col-md-offset-4">
	     <div class="panel panel-default">
		<div class="panel-body">
		   <p class="text-center">
		      <img src="https://www.streamlis.it/images/logo_flat.png" alt="Stream LIS" height="56" width="60">
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
<!--				  <label class="pull-right">
				     <a href='reset-pwd-req.php'>Password dimenticata?</a>
				  </label>-->
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
      <?php include("include/footer.php"); ?>
   </div> <!-- FINE DIV PANEL -->
</div> <!-- FINE DIV CONTAINER-FLUID -->
   
<!-- GeoTrust QuickSSL [tm] Smart  Icon tag. Do not edit. -->
<script language="javascript" type="text/javascript" src="//smarticon.geotrust.com/si.js"></script>
<!-- end  GeoTrust Smart Icon tag -->

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
