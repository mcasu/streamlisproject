<?PHP

if ( session_status() == PHP_SESSION_NONE ) 
{
    session_start();
}

require_once("./include/config.php");

$dbactions = $mainactions->GetDBActionsInstance();

if (!isset($_POST['seconds']))
{
    $seconds = '10800'; // default value 10800 seconds = 3 hours
}
else
{
    $seconds = $_POST['seconds'];
}

$result = $dbactions->CleanLoginOlderThan($seconds);

error_log("INFO - Result-> " . $result);

if ($result == 'FALSE')
{
    // clean failed
    $mainactions->HandleError("FAILED to clean the logins older than " . $seconds . " seconds.");
    
    echo date("Y-m-d H:i:s") . " - FAILED - Pulito [".$result."] utenti.\n" . $mainactions->GetErrorMessage() . "\n" . $dbactions->GetErrorMessage()."\n";
}
else
{
    //clean successful
    echo date("Y-m-d H:i:s") . " - SUCCESS - Pulito [".$result."] utenti.\n";
}
