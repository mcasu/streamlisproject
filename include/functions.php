<?php

require_once("config.php");

$dbactions = $mainactions->GetDBActionsInstance();

$fname = filter_input(INPUT_GET, 'fname');

switch ($fname) 
{
    case check_live_exists_for_publish_code:
        $publishCode = filter_input(INPUT_GET, 'publishCode');
        return CheckLiveExistsForPublishCode($dbactions, $publishCode);
    default:
        break;
}


function CheckLiveExistsForPublishCode($dbactions, $publish_code) 
{
    $live_events = $dbactions->GetLiveEventsByPublisher($publish_code);
    $live_events_number = mysql_num_rows($live_events);
    
    if ($live_events && $live_events_number > 0)
    {
        return true;
    }
    return false;
}