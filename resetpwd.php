<?PHP
session_start();
require_once("./include/config.php");

$success = false;
if($mainactions->ResetPassword())
{
    $success=true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it-IT" lang="it-IT">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Stream LIS - Reset Password</title>
    <link rel="stylesheet" href="/style/bootstrap.min.css"/>
</head>

<body>

<div class="container-fluid">
    <?php
    if($success)
    {
        echo '<div class="alert alert-success" role="alert">';
            echo '<h2>Nuova password generata con successo!</h2>';
            echo '<h5>Abbiamo spedito al tuo account di posta la nuova password.</h5>';
            echo '<br/>';
            echo '<p>';
                echo '<a href="/login.php">Vai alla pagina di login.</a>';
            echo '</p>';
        echo '</div>';
    }
    else
    {
        
        echo '<div class="alert alert-danger" role="alert">';
            echo '<h2>Procedura di generazione nuova password fallita!</h2>';
            echo '<h5>'. $mainactions->GetErrorMessage() .'</h5>';
            echo '<br/>';
            echo '<p>';
                echo '<a href="/login.php">Vai alla pagina di login.</a>';
            echo '</p>';
        echo '</div>';
    }
    ?>
</div>
</body>
</html>