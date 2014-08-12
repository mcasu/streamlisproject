<?PHP

require_once("./include/config.php");

$dbactions = $mainactions->GetDBActionsInstance();

if (!isset($_POST['seconds']))
{
    $seconds = '10800'; // default value 10800 seconds
}
else
{
    $seconds = $_POST['seconds'];
}


if (!$dbactions->CleanLoginOlderThan($seconds))
{
    // clean failed
    $mainactions->HandleError("FAILED to clean the logins older than " . $seconds . " seconds.");
    echo "1";
}
else
{
    //clean successful
    echo "0";
}

?>