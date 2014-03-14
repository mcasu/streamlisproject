<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/membersite_config.php");

$utils = $fgmembersite->GetUtilsInstance();
$dbactions = $fgmembersite->GetDBActionsInstance();

$type = $_POST['type'];
$event_id = $_POST['event_id'];
	
$result = $dbactions->DeleteEventOnDemand($event_id);
if (!$result)
{
	echo "On-demand Event ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage();
}
else
{
	echo "On-demand Event ID ".$event_id." SUCCESS";
}

?>
