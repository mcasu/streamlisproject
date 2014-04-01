<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();

$type = $_POST['type'];
$event_id = $_POST['event_id'];
	
$result = $dbactions->DeleteEventOnDemand($event_id);
if (!$result)
{
	echo "Deleting On-demand Event ID ".$event_id." from database FAILED\r\n".$dbactions->GetErrorMessage();
	error_log("Deleting On-demand Event ID ".$event_id." from database FAILED\r\n".$dbactions->GetErrorMessage());
}
else
{
	echo "Deleting On-demand Event ID ".$event_id." from database SUCCESS";
	
}

?>
