<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

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


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Home page</title>
      <link rel="STYLESHEET" type="text/css" href="../style/fg_membersite.css">
</head>
<body>
<?php include("header.php"); ?>
<br/>
<div align="right" id='fg_membersite_content'>
<b><?= $mainactions->UserFullName(); ?></b>, Welcome back!</div>

<div><p>La tua congregazione e' <b><?= $mainactions->UserGroupName(); ?></b>.</p></div>


</body>
</html>
