<?PHP

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
if ($result == '-1')
{
    // clean failed
    $mainactions->HandleError("FAILED to clean the logins older than " . $seconds . " seconds.");
    echo "1 - FAILED [". $result . "]\n" . $mainactions->GetErrorMessage() . "\n" . $dbactions->GetErrorMessage()."\n";
    exit(1);
}
else
{
    //clean successful
    echo "0 - SUCCESS - Pulito [".$result."] utenti.\n";
    exit(0);
}

?>