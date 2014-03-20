<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();
$UserHasCreated=false;

if(!$fgmembersite->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

$user_role = $fgmembersite->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
        $utils->RedirectToURL("../viewer/live-normal.php");
}

if(isset($_POST['submitted']))
{
   $UserHasCreated=$fgmembersite->CreateUser();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>JW LIS Streaming - Nuovo utente</title>
    <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css" />
	<link rel='stylesheet' type='text/css' href='../style/admin.css' />
    <script type='text/javascript' src='../scripts/gen_validatorv31.js'></script>
    <link rel="STYLESHEET" type="text/css" href="../style/pwdwidget.css" />
    <script src="../scripts/pwdwidget.js" type="text/javascript"></script>      
</head>
<body>

<?php include("header.php"); ?>
</br>
<!-- Form Code Start -->
<div id='fg_membersite'>
<form id='register' action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
<fieldset >
<legend>Nuovo utente</legend>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<div class='short_explanation'>* required fields</div>
<input type='text'  class='spmhidip' name='<?php echo $utils->GetSpamTrapInputName($fgmembersite->rand_key); ?>' />

<div><span class='error'><?php echo $fgmembersite->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='name' >Full Name*: </label><br/>
    <input type='text' name='name' id='name' value='<?php echo $utils->SafeDisplay('name') ?>' maxlength="50" /><br/>
    <span id='register_name_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='email' >Email Address*:</label><br/>
    <input type='text' name='email' id='email' value='<?php echo $utils->SafeDisplay('email') ?>' maxlength="50" /><br/>
    <span id='register_email_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='username' >UserName*:</label><br/>
    <input type='text' name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="50" /><br/>
    <span id='register_username_errorloc' class='error'></span>
</div>
<div class='container' style='height:80px;'>
    <label for='password' >Password*:</label><br/>
    <div class='pwdwidgetdiv' id='thepwddiv' ></div>
    <noscript>
    <input type='password' name='password' id='password' maxlength="50" />
    </noscript>    
    <div id='register_password_errorloc' class='error' style='clear:both'></div>
</div>

<div class='container'>
    <label for='groups' >Congregazione*:</label><br/>
    <select name="group_name" id="group">
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
</div>
<div class='container'>
    <label for='roles' >Tipo di utente*:</label><br/>
    <select name="user_role_name" id="user_type">
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
</div>

</br>
</br>

<div class='container'>
    <input type='submit' name='Submit' value='Crea' />
</div>

</fieldset>
</form>
<!-- client-side Form Validations:
Uses the excellent form validation script from JavaScript-coder.com-->

<script type='text/javascript'>
// <![CDATA[
    var pwdwidget = new PasswordWidget('thepwddiv','password');
    pwdwidget.MakePWDWidget();
    
    var frmvalidator  = new Validator("register");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("name","req","Please provide your name");

    frmvalidator.addValidation("email","req","Please provide your email address");

    frmvalidator.addValidation("email","email","Please provide a valid email address");

    frmvalidator.addValidation("username","req","Please provide a username");
    
    frmvalidator.addValidation("password","req","Please provide a password");

// ]]>
</script>

</br>
<?php

if ($UserHasCreated)
{
        echo "<h2>Utente creato con successo!</h2>";
	echo "A confirmation email will be send to the user account.<br/>".
	"The user must click the link in the email to complete the registration.";
}

?>

</body>
</html>
