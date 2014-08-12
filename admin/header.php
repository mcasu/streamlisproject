<head>
   <link rel='stylesheet' type='text/css' href='../style/header.css' />
   <script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>
   <script type="text/javascript" src="../include/check_browser_closed.js"></script>

<script type="text/javascript">
$(document).ready(function()
{
//wireUpEvents();

/*$("a.top").click(function()
{
	$(this).parent().addClass("active");
});*/
   
});
</script>

</head>

<div id='headermenu'>
<ul>
   <li><a href='dashboard.php'><span>Dashboard</span></a></li>
   
   <li class='has-sub'><a href='#'><span>Eventi</span></a>
      <ul>
         <li><a href='events_live.php'><span>Live</span></a></li>
	 <li><a href='events_ondemand.php'><span>OnDemand</span></a></li>
      </ul>
   </li>
   
   <li class='has-sub'><a href='#'><span>Monitor</span></a>
      <ul>
         <li><a href='stats.php'><span>Statistiche Nginx</span></a></li>
	 <li><a href='graphs.php'><span>Grafici</span></a></li>
      </ul>
   </li>
   <li class='has-sub'><a href='groups.php'><span>Congregazioni</span></a>
      <ul>
         <li><a href='group_add.php'><span>Aggiungi congregazione</span></a></li>
      </ul>
   </li>
   <li class='has-sub'><a href='users.php'><span>Utenti</span></a>
      <ul>
         <li><a href='user_add.php'><span>Aggiungi utente</span></a></li>
      </ul>
   </li>
   <li class='has-sub'><a href='links_manage.php'><span>Relazioni</span></a>
   </li>
   <li class='has-sub'>
	<a href='#'>Profilo<img src="../images/user.png" height="32" width="32"></a>
      <ul>
         <li><a href='../change-pwd.php'><span>Cambia password</span></a></li>
         <li><a href='../logout.php'><span>Esci</span></a></li>
      </ul>
   </li>
</ul>  
   
</div>
<div class="logo">
   <img src="../images/logo_flat.png" alt="JW LIS Streaming">
</div>




