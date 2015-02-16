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
            return;
    }
    else
    {
        error_log("INFO - Eseguo GetOndemandEventsById()...");
        //echo "Deleting On-demand Event ID ".$event_id." from database SUCCESS";
        $result = $dbactions->GetOndemandEventsById($event_id);
        
        if (!$result)
        {
            error_log("ERROR - GetOndemandEventsById ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage());
            return;
        }
        
        error_log("INFO - Eseguo mysql_fetch_array()...");
        $event = mysql_fetch_array($result);

        error_log("INFO - Eseguo DeleteOnDemandVideoFromDisk()...");
        $fsactions->DeleteOnDemandVideoFromDisk($event['ondemand_app_name'], $event['ondemand_path'], $event['ondemand_filename']);
    }
}
catch(Exception $e)
{
    error_log("ERROR - Deleting On-demand Event ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage());
}