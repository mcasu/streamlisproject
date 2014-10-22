<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("/login.php");
    exit;
}

?>

<ul class="nav nav-pills">
      
      <li>
	 <a href='live-normal.php' class="active">
	       <img src="../images/event.png" height="32" width="32">
	       Live streaming
	 </a>
      </li>

      <li>
	 <a href='ondemand-normal.php' class="active">
	       <img src="../images/ondemand.png" height="32" width="32">
	       On-demand streaming
	 </a>
      </li>      
      
      <li class='dropdown pull-right'>
	   <a href='#' class="dropdown-toggle" data-toggle="dropdown">
	    <img src="../images/profile.png" height="32" width="32">
	    Profilo <span class="caret"></span>
	   </a>
	 <ul class="dropdown-menu" role="menu">
	    <li><a href='change-pwd.php'><span>Cambia password</span></a></li>
	    <li><a href='../logout.php'><span>Esci</span></a></li>
	 </ul>
      </li>
      
</ul>