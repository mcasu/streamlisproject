<head>
   <link rel='stylesheet' type='text/css' href='../style/header.css' />
   <script type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>

<script type="text/javascript">
$(document).ready(function()
{

/*$("a.top").click(function()
{
	$(this).parent().addClass("active");
});*/
   
});
</script>

</head>

<div id='cssmenu'>
<ul>
   <li class='has-sub'><a class="top" href='home-admin.php'><span>Home</span></a>
  </li>
   <li class='has-sub'><a class="top" href='#'><span>Eventi</span></a>
      <ul>
         <li class='last'><a href='events_live.php'><span>Live</span></a></li>
	 <li class='last'><a href='events_ondemand.php'><span>OnDemand</span></a></li>
      </ul>
   </li>
   <li class='has-sub'><a class="top" href='#'><span>Monitor</span></a>
      <ul>
         <li class='last'><a href='stats.php'><span>Statistiche Nginx</span></a></li>
	 <li class='last'><a href='graphs.php'><span>Grafici</span></a></li>
      </ul>
   </li>
   <li class='has-sub'><a class="top" href='groups.php'><span>Congregazioni</span></a>
      <ul>
         <li class='last'><a href='group_add.php'><span>Aggiungi congregazione</span></a></li>
      </ul>
   </li>
   <li class='has-sub last'><a class="top" href='users.php'><span>Utenti</span></a>
      <ul>
         <li class='last'><a href='user_add.php'><span>Aggiungi utente</span></a></li>
      </ul>
   </li>
   <li class='has-sub last'><a class="top" href='links_manage.php'><span>Relazioni</span></a>
   </li>
   <li class='has-sub last'>
	<a class="top" href='#'>Profilo<img src="../images/user.png" align="right" hspace="4" height="32" width="32"></a>
      <ul>
         <li><a href='../change-pwd.php'><span>Cambia password</span></a></li>
         <li class='last'><a href='../logout.php'><span>Esci</span></a></li>
      </ul>
   </li>
	<li> 
	<img src="../images/logo.png" alt="JW LIS Streaming" align="center" hspace="20" height="48" width="48">
	</li>
</ul>
</div>
