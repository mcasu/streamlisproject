<nav class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed pull-left" style="margin-left: 4px;" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            
            <ul class="nav navbar-nav">

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
                  
                 <li>
                     <a href='conference.php' class="active">
                           <img src="../images/register.png" height="32" width="32">
                           Risposte
                     </a>
                  </li> 

                <li class="dropdown pull-right">
                    <a href='#' class="dropdown-toggle" data-toggle="dropdown">
                    <img src="../images/profile.png" height="32" width="32">
                        <b>
                            <?php 
                            switch($mainactions->GetSessionUserRole())
                            {
                                case "1":
                                    echo '<span class="label label-success">' . $mainactions->UserFullName() . '</span>';
                                    break;
                                case "2":
                                    echo '<span class="label label-default">' . $mainactions->UserFullName() . '</span>';
                                    break;
                                case "3":
                                    echo '<span class="label label-warning">' . $mainactions->UserFullName() . '</span>';
                                    break;
                            }
                            ?>
                        </b> 
                    <span class="caret"></span>
                    </a>
                   <ul class="dropdown-menu" role="menu">
                      <li><a href='change-pwd.php'><span>Cambia password</span></a></li>
                      <li><a href='../logout.php'><span>Esci</span></a></li>
                   </ul>
                </li>
            </ul>
            
        </div>
    </div>
</nav>

<p><h4 style="margin-left:4px;">La tua congregazione è <b><?= $mainactions->UserGroupName(); ?></b></h4></p>
<br/>