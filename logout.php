<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
      
</html>
<?PHP
require_once("./include/config.php");

$mainactions->LogOut();

$utils = $mainactions->GetUtilsInstance();
$utils->RedirectToURL("/login.php");
exit;
