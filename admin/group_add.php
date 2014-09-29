<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();
$GroupHasCreated=false;

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("../login.php");
    exit;
}

$user_role = $mainactions->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
        $utils->RedirectToURL("../viewer/live-normal.php");
}

if(isset($_POST['submitted']))
{
   $GroupHasCreated=$mainactions->CreateGroup();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>JW LIS Streaming - Nuovo utente</title>
    <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css" />
    <link rel='stylesheet' type='text/css' href='../style/header.css' />
    <link rel='stylesheet' type='text/css' href='../style/admin.css' />
    <link rel="STYLESHEET" type="text/css" href="../style/pwdwidget.css" />

    <script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="../include/session.js"></script>
    
    <script type='text/javascript' src='../scripts/gen_validatorv31.js'></script>
    <script type="text/javascript" src="../scripts/pwdwidget.js"></script>      
</head>
<body>

<?php include("header.php"); ?>
</br>
<!-- Form Code Start -->
<div id='fg_membersite'>
    
<form id='register' action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
<fieldset >
<div align="left">
   <label class="login-legend">Nuovo gruppo</label>   
</div>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<div class='short_explanation'>* required fields</div>
<input type='text'  class='spmhidip' name='<?php echo $utils->GetSpamTrapInputName($mainactions->rand_key); ?>' />

<div><span class='error'><?php echo $dbactions->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='group_name' >Group Name*: </label><br/>
    <input type='text' name='group_name' id='group_name' value='<?php echo $utils->SafeDisplay('group_name') ?>' maxlength="128" /><br/>
    <span id='register_group_name_errorloc' class='error'></span>
</div>


<div class='container'>
	<label for='group_type' >Tipo di gruppo*:</label><br/>
	<select name="group_type" id="group_type">
		<option value="Congregazione">Congregazione</option>
		<option value="Gruppo">Gruppo</option>
	</select>
</div>

<div class='container'>
    <label for='group_roles' >Ruolo del gruppo*:</label><br/>
    <select name="group_role_name" id="group_role_name">
<?php
    try
    {
        /*** query the database ***/
        $result = $dbactions->GetGroupRoles();

        if (!$result)
        {
                error_log("No Results");
        }

        while($row = mysql_fetch_array($result))
        {
                $group_role_name=$row['group_role_name'];
                echo '<option value="' . $group_role_name . '">' . $group_role_name . '</option>"';
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
    var frmvalidator  = new Validator("register");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("group_name","req","Please provide the group name");
</script>

</br>
<?php   

if ($GroupHasCreated)
{
	echo "<h2>Gruppo creato con successo!</h2>";
}

?>

</body>
</html>
