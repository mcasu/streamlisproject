<?PHP

require_once("./include/config.php");

$dbactions = $mainactions->GetDBActionsInstance();

if ($mainactions->CheckLogin())
{
    // session not expired
    echo "0";
}
else
{
    //session expired
    $username = $mainactions->UserName();
    $dbactions->SetUserLoginStatus($username, false);
    echo "1";
}

?>