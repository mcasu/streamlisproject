<?PHP
require_once("./include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

if(isset($_POST['submitted']))
{
   if($fgmembersite->Login())
   {
	$user_role = $fgmembersite->GetSessionUserRole();	
	if ($user_role && $user_role=="1")
	{
        	$utils->RedirectToURL("admin/");
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


<div align=center>
	<h2 align="center">Benvenuto su JW LIS Streaming</h2>
	<img src="images/logo.png" alt="JW LIS Streaming" align="center" height="48" width="48"> 
</div>
<div id='fg_membersite' align="center">
<form id='login' action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
<fieldset >
<legend>Login</legend>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<!--<div class='short_explanation'>* required fields</div>-->

<div><span class='error'><?php echo $dbactions->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='username' >Nome utente:</label><br/>
    <input type='text' name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="50" /><br/>
    <span id='login_username_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='password' >Password:</label><br/>
    <input type='password' name='password' id='password' maxlength="50" /><br/>
    <span id='login_password_errorloc' class='error'></span>
</div>

</br>
<div class='container'>
    <input align="center" type='submit' name='Submit' value='ENTRA' />
</div>
<div class='short_explanation'><a href='reset-pwd-req.php'>Password dimenticata?</a></div>
</fieldset>
</form>

<script type='text/javascript'>
// <![CDATA[

    var frmvalidator  = new Validator("login");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();

    frmvalidator.addValidation("username","req","Please provide your username");
    
    frmvalidator.addValidation("password","req","Please provide the password");

// ]]>
</script>
</div>

</body>
</html>
