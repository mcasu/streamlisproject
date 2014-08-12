<?PHP
require_once("./include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(isset($_POST['submitted']))
{
   if($mainactions->Login())
   {      
	$user_role = $mainactions->GetSessionUserRole();	
	if ($user_role && $user_role=="1")
	{
        	$utils->RedirectToURL("admin/dashboard.php");
	}
	else
	{
        	$utils->RedirectToURL("viewer/live-normal.php");
	}
   }
}																									
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>JW LIS Streaming - Login</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css" />
      <script type='text/javascript' src='scripts/gen_validatorv31.js'></script>
</head>
<body>

<div id='fg_membersite' align="center">
<div align=center>
	<h2 align="center">Benvenuto su JW LIS Streaming</h2>
	<img src="images/logo_flat.png" alt="JW LIS Streaming" align="center" height="56" width="60"> 
</div>

<form id='login' action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
<fieldset>

<div align="left">
   <label class="login-legend">Login</label>   
</div>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<div><span class='error'><?php echo $dbactions->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='username' >Nome utente:</label><br/>
    <input type='text' name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="128" /><br/>
    <span id='login_username_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='password' >Password:</label><br/>
    <input type='password' name='password' id='password' maxlength="128" /><br/>
    <span id='login_password_errorloc' class='error'></span>
</div>

</br>
<div class='container'>
    <input class="login" align="center" type='submit' name='Submit' value='ENTRA' />
</div>
<div class="pwd-reset"><a href='reset-pwd-req.php'>Password dimenticata?</a></div>
</fieldset>
</form>

<script type='text/javascript'>
// <![CDATA[

    var frmvalidator  = new Validator("login");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();

    frmvalidator.addValidation("username","req","Per favore inserire il nome utente.");
    
    frmvalidator.addValidation("password","req","Per favore inserire la password");

// ]]>
</script>
</div>

</body>
</html>
