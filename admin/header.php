<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

if(!$mainactions->CheckLogin())
{
    $utils->RedirectToURL("/login.php");
    exit;
}

$user_role = $mainactions->GetSessionUserRole();
if (!$user_role || $user_role!="1")
{
	$utils->RedirectToURL("/viewer/live-normal.php");
}

?>

<ul class="nav nav-pills">
      
      <li>
	 <a href='dashboard.php' class="active">
	       <img src="../images/dashboard.png" height="32" width="32">
	       Dashboard
	 </a>
      </li>
      
      <li class='dropdown'>
	 <a class="dropdown-toggle" data-toggle="dropdown" href="#">
	    <img src="../images/event.png" height="32" width="32">
	    Eventi <span class="caret"></span>
	 </a>
	 <ul class="dropdown-menu" role="menu">
	    <li><a href='events_live.php'>Live</a></li>
	    <li><a href='events_ondemand.php'>OnDemand</a></li>
            <li><a href='conference.php'>Conference</a></li>
	 </ul>
      </li>
      
      <li class='dropdown'>
	 <a href='#' class="dropdown-toggle" data-toggle="dropdown">
	    <img src="../images/monitor.png" height="32" width="32">
	    Monitor <span class="caret"></span>
	    
	 </a>
	 <ul class="dropdown-menu" role="menu">
	    <li><a href='stats.php'>Statistiche Nginx</a></li>
	    <li><a href='graphs.php'>Grafici</a></li>
	 </ul>
      </li>
      
      <li class='dropdown'>
	 <a href='groups.php' class="dropdown-toggle" data-toggle="dropdown">
	    <img src="../images/group.png" height="32" width="32">
	    Congregazioni <span class="caret"></span>
	 </a>
	 <ul class="dropdown-menu" role="menu">
	    <li><a href='groups.php'><span>Visualizza congregazioni</span></a></li>
	    <li><a href='group_add.php'><span>Aggiungi congregazione</span></a></li>
	 </ul>
      </li>
      
      <li class='dropdown'>
	 <a href='users.php' class="dropdown-toggle" data-toggle="dropdown">
	    <img src="../images/user.png" height="32" width="32">
	    Utenti <span class="caret"></span>
	 </a>
	 <ul class="dropdown-menu" role="menu">
	    <li><a href='users.php'><span>Visualizza utenti</span></a></li>
	    <li><a href='user_add.php'><span>Aggiungi utente</span></a></li>
	 </ul>
      </li>
      
      <li>
	 <a href='links_manage.php'>
	    <img src="../images/link.png" height="32" width="32">
	    Relazioni
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