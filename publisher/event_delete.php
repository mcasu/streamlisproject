<?php include("../check_login.php"); ?>

<?PHP
require_once($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();
$fsactions = $mainactions->GetFSActionsInstance();

$type = $_POST['type'];
$event_id = $_POST['event_id'];

try
{
    $result = $dbactions->GetOndemandEventsById($event_id);
        
    if (!$result)
    {
        error_log("ERROR - GetOndemandEventsById ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage());
        return;
    }

    $event = mysql_fetch_array($result);
    $result = $fsactions->DeleteOnDemandVideoFromDisk($event['ondemand_app_name'], $event['ondemand_path'], $event['ondemand_filename']);
    
    if (!$result)
    {
        error_log("ERROR - DeleteOnDemandVideoFromDisk ID ".$event_id." FAILED!");
        return;
    }
    
    $result = $dbactions->DeleteEventOnDemand($event_id);
    if (!$result)
    {
            error_log("ERROR - Deleting On-demand Event ID ".$event_id." from database FAILED\r\n".$dbactions->GetErrorMessage());
            return;
    }
}
catch(Exception $e)
{
    error_log("ERROR - Deleting On-demand Event ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage());
}