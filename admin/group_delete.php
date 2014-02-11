<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

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
