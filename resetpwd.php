<?PHP
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
    <title>Reset Password</title>
    <link rel="stylesheet" href="/style/bootstrap.min.css"/>
</head>

<body>
<?php
if($success)
{
    echo '<h2>Nuova password generata con successo!</h2>';
    echo '<div class="alert alert-success" role="alert">
        <h4>Abbiamo spedito al tuo account di posta la nuova password.</h4>';
    echo '</div>';
}
else
{
    echo '<h2>Procedura di generazione nuova password fallita!</h2>';
    echo '<div class="alert alert-danger" role="alert">
        <h4>'. $mainactions->GetErrorMessage() .'</h4>';
    echo '</div>';
}
?>

</body>
</html>