<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$group_id = $_POST['group_id'];
	
$result = $dbactions->DeleteGroup($group_id);
if (!$result)
{
	echo "GroupID ".$group_id." FAILED\r\n".$dbactions->GetErrorMessage();
}
else
{
	echo "GroupID ".$group_id." SUCCESS";
}

?>
