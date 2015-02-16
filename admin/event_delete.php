<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();
$fsactions = $mainactions->GetFSActionsInstance();

$type = $_POST['type'];
$event_id = $_POST['event_id'];

try
{
    $result = $dbactions->DeleteEventOnDemand($event_id);
    if (!$result)
    {
            //echo "Deleting On-demand Event ID ".$event_id." from database FAILED\r\n".$dbactions->GetErrorMessage();
            error_log("ERROR - Deleting On-demand Event ID ".$event_id." from database FAILED\r\n".$dbactions->GetErrorMessage());
    }
    else
    {
        //echo "Deleting On-demand Event ID ".$event_id." from database SUCCESS";
        $row = $dbactions->GetOndemandEventsById($event_id);
        $event = mysql_fetch_array($row);

        $fsactions->DeleteOnDemandVideoFromDisk($event['ondemand_app_name'], $event['ondemand_path'], $event['ondemand_filename']);
    }
}
catch(Exception $e)
{
    error_log("ERROR - Deleting On-demand Event ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage());
}