<?php

require_once("config.php");

$dbactions = $mainactions->GetDBActionsInstance();

$fname = filter_input(INPUT_POST, 'fname');
if (!isset($fname) || !$fname)
{
    $fname = filter_input(INPUT_GET, 'fname');
}

switch ($fname) 
{
    case "check_live_exists_for_publish_code":
        $publishCode = filter_input(INPUT_POST, 'publishCode');
        return CheckLiveExistsForPublishCode($dbactions, $publishCode);
    case "get_stream_selector_container":
        $publishCode = filter_input(INPUT_GET, 'publishCode');
        return GetStreamSelectorContainer($dbactions, $publishCode);
    case "get_current_live_players_number":
        $stream_name = filter_input(INPUT_GET, 'streamName');
        return GetCurrentLivePlayersNumber($dbactions, $stream_name);
    default:
        break;
}


function CheckLiveExistsForPublishCode($dbactions, $publish_code) 
{
    $live_events = $dbactions->GetLiveEventsByPublisher($publish_code);
    $live_events_number = mysql_num_rows($live_events);
    
    if ($live_events && $live_events_number > 0)
    {
        echo "true";
    }
    else    
    {
        echo "false";
    }
}


function GetStreamSelectorContainer($dbactions, $publish_code) 
{
        echo '<label id="streamSelectorLabel" for="streams">Live stream disponibili:</label><br/>';
        echo '<select id="streamSelector" class="form-control" name="stream_name">';
            $live_events = $dbactions->GetLiveEventsByPublisher($publish_code);
            while($row = mysql_fetch_array($live_events))
            {
                $live_id=$row['live_id'];
                $app_name=$row['app_name'];
                $stream_name=$row['stream_name'];
                $live_date=$row['live_date'];
                $live_time=$row['live_time'];
                $client_addr=$row['client_addr'];
                $live_date_formatted = strftime("%A %d %B %Y", strtotime($row['live_date']));

                $group_name = $dbactions->GetGroupNameByPublishCode($publish_code);

                echo '<option id="'. $app_name . '" value="' . $stream_name . '">Adunanza di <b>' . $group_name . '</b> del <b>' . $live_date_formatted . '</b></option>"';
            }
        echo '</select>';
        echo '<br/>';
}

function GetCurrentLivePlayersNumber($dbactions, $stream_name)
{
    $today_players = $dbactions->GetTodayLastLivePlayersNumber($stream_name);
    $player_events = array();
    // Pushing record to array
    while($row = mysql_fetch_array($today_players))
    {        
        array_push($player_events, $row);
    }
    
    $players_counter = 0;
    $time_now = date('H:i:s');
    foreach ($player_events as $pe_first)
    {       
        $date_now = new DateTime($pe_first['event_date'] . ' ' . $time_now);
        $date_event = new DateTime($pe_first['event_date'] . ' ' . $pe_first['event_time']);
        
        if ( ($pe_first['event_call'] === 'play') && ($date_now > $date_event) )
        {
            if (!PlayDoneEventFound($player_events, $pe_first))
            {
                $players_counter++;
            }
        }
    }
    
    return $players_counter;
}

function PlayDoneEventFound($player_events, $event)
{
    $play_done_found = false;
    foreach ($player_events as $pe) 
    {
        if ($pe['nginx_id'] === $event['nginx_id'] && 
                $pe['event_call'] === "play_done" &&
                $pe['client_addr'] === $event['client_addr'])
        {
            $play_done_found = true;
        }
    }

    return $play_done_found;
}