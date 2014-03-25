<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$user_id = $_POST['user_id'];
	
$result = $dbactions->DeleteUser($user_id);
if (!$result)
{
	echo "UserID ".$user_id." FAILED\r\n".$dbactions->GetErrorMessage();
}
else
{
	echo "UserID ".$user_id." SUCCESS";
}

?>
