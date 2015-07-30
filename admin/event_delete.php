<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_admin.php");

$utils = $mainactions->GetUtilsInstance();
$dbactions = $mainactions->GetDBActionsInstance();
$fsactions = $mainactions->GetFSActionsInstance();

$type = $_POST['type'];
$event_id = $_POST['event_id'];

try
{
    $result = $dbactions->GetOndemandEventById($event_id);
        
    if (!$result)
    {
        error_log("ERROR - GetOndemandEventById ID ".$event_id." FAILED\r\n".$dbactions->GetErrorMessage());
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