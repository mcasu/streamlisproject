<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

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
