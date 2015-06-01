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
    case "users_resetpwd":
        $userId = filter_input(INPUT_POST, 'userId');
        $userAdminId = filter_input(INPUT_POST, 'userAdminId');
        return ResetUserPassword($mainactions, $dbactions, $userId, $userAdminId);
    case "mark_ondemand_video_to_join":
        $ondemandIdList = filter_input(INPUT_POST, 'ondemandIdList');
        return MarkOndemandVideoToJoin($dbactions, $ondemandIdList);
    case "get_datatable_ondemand_actions_join":
        return GetDataTableOndemandActionsJoin($host, $uname, $pwd, $database);
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
    try 
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
                //error_log("EVENTO [" .$pe_first['event_call']. "] - DATE_NOW: ". $date_now->format("Y-m-d H:i:s") . " DATE_EVENT: " . $date_event->format("Y-m-d H:i:s"));
                
                if (!PlayDoneEventFound($player_events, $pe_first))
                {
                    $players_counter++;
                }
            }
        }
        //error_log("INFO - TIME_NOW: " . $time_now . " player count: " . $players_counter . "\n" .$dbactions->getErrorMessage());
    } 
    catch (Exception $ex) 
    {
        $players_counter = "ND";
        error_log("\nINFO - player count: " . $players_counter . "\n" .$ex->getMessage());
    }
    
    echo $players_counter;
}

function PlayDoneEventFound($player_events, $event)
{
    $play_done_found = false;
    foreach ($player_events as $pe) 
    {
        //error_log("EVENTO [" .$pe['event_call']. "] - " . $pe['event_time'] . " - " .$pe['nginx_id']);
       
        $date_event_play_done = new DateTime($pe['event_date'] . ' ' . $pe['event_time']);
        $date_event_play = new DateTime($event['event_date'] . ' ' . $event['event_time']);
        
        if ($pe['nginx_id'] === $event['nginx_id'] && 
                $pe['event_call'] === "play_done" &&
                $pe['client_addr'] === $event['client_addr'] &&
                $date_event_play_done > $date_event_play)
        {
            //error_log("PLAY DONE FOUND: " . $pe['event_time'] . " - " .$pe['nginx_id'] . " - " .$pe['event_call']);
            $play_done_found = true;
            break;
        }
    }

    return $play_done_found;
}

function ResetUserPassword($mainactions, $dbactions, $userId, $userAdminId)
{
    // Get the user data
    $userData = array();
    if(!$dbactions->GetUserById($userId,$userData))
    {
        return FALSE;
    }

    // Generate new password
    $passwordNew = $mainactions->GenerateRandomPassword(8);
            
    // Save the passwordi into the database
    if (empty($passwordNew) || !$dbactions->ChangePasswordInDB($userData,$passwordNew))
    {
        return FALSE;
    }
    $userAdminData = array();
    
    if (!$dbactions->GetUserById($userAdminId, $userAdminData))
    {
        return FALSE;
    }
    
    $mailTo = array();
    $mailTo[] = array("email" => $mainactions->admin_email, "name" => "admin");
    $mailTo[] = array("email" => $userAdminData['email'], "name" => $userAdminData['name']);
    
    $mailSubject = $mainactions->sitename . " - Reset password utente ". $userData['name'];
    
    $mailBody = "Ciao caro fratello, \r\n\r\n".
        "La password dell'utente ". $userData['name'] . " è stata cambiata. ".
        "Di seguito puoi vedere le sue nuove credenziali:\r\n".
        "\r\n".
        "Username: ".$userData['username']."\r\n".
        "Password: $passwordNew\r\n".
        "\r\n".
        "L'utente potrà fare login qui: http://www.streamlis.it/login.php\r\n".
        "\r\n".
        "\r\n".
        "Grazie per la collaborazione,\r\n".
        $mainactions->sitename;
    
    if (!$mainactions->SendMail($mailTo, $mailSubject, $mailBody))
    {
        error_log("\ERROR - functions.php SendMail() FAILED!");
    }
    
    return TRUE;
}

function MarkOndemandVideoToJoin($dbactions, $ondemandIdList)
{
        $ondemandListArray = explode(",",$ondemandIdList);
        $found = 0;
        foreach ($ondemandListArray as $ondemandId) 
        {
            $result = $dbactions->CheckIfOndemandVideoIsMarked($ondemandId);
            if (!$result)
            {
                $found = -1;
                error_log("ERROR - CheckIfOndemandVideoIsMarked() FAILED! " . $dbactions->GetErrorMessage());
                break;
            }

            $found += mysql_num_rows($result);
        }

        if ($found < 0)
        {
            echo "1";
            return;
        }

        if ($found > 0)
        {
            echo "2";
            return;
        }

        if (!$dbactions->MarkOndemandVideoToJoin($ondemandIdList))
        {
            error_log("ERROR - MarkOndemandVideoToJoin() FAILED! " . $dbactions->GetErrorMessage());
            echo "1";
        }
        else
        {
            echo "0";
        }
}

function GetDataTableOndemandActionsJoin($host, $uname, $pwd, $database)
{
    /*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'ondemand_actions_join';
 
// Table's primary key
$primaryKey = 'ondemand_actions_join_id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier.
$columns = array(
    array( 'db' => 'ondemand_actions_join_id', 'dt' => "ID OPERAZIONE" ),
    array( 'db' => 'ondemand_actions_join_list',  'dt' => "ONDEMAND VIDEO DA UNIRE" ),
    array( 'db' => 'ondemand_actions_join_status',   'dt' => "STATO OPERAZIONE" )
);
 
// SQL server connection information
$sql_details = array(
    'user' => $uname,
    'pass' => $pwd,
    'db'   => $database,
    'host' => $host
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);
}

function GetOndemandActionsJoin($dbactions)
{
    try
    {
        $actionsJoin = $dbactions->GetAllOnDemandActionsJoin();

        if (!$actionsJoin)
        {
            error_log("ERROR - Publisher functions.php GetOndemandActionsJoin() - ".$dbactions->GetErrorMessage());
        }

        echo '<table class="table table-hover" id="ondemand_actions_join_table">';
        echo '<thead>';
            echo '<tr class="head">';
                echo '<th></th>';
                echo '<th>ID OPERAZIONE</th>';
                echo '<th>ONDEMAND VIDEO DA UNIRE</th>';
                echo '<th>STATO OPERAZIONE</th>';
            echo '</tr>'; 
        echo '</thead>';
        
        echo '<tbody>';
        while ($row = mysql_fetch_array($actionsJoin))
        {
            $values[0]=$row['ondemand_actions_join_id'];
            $values[1]=$row['ondemand_actions_join_list'];
            $values[2]=$row['ondemand_actions_join_status'];

            echo '<tr class="actions_join_table" id="' .$values[1].'">';
                echo '<td><input type="radio" name="actions_join_selected" /></td>';
                echo '<td>' . $values[0] . '</td>';
                echo '<td>' . $values[1] . '</td>';
                echo '<td>';
                    if ($values[2] == 0)
                    {
                        echo '<span class="label label-warning">Schedulata</span>';
                    }
                    elseif ($values[2] == 1)
                    {
                        echo '<span class="label label-info">In corso</span>';
                    }
                    else if ($values[2] == 2)
                    {
                        echo '<span class="label label-success">Terminata</span>';
                    }
                echo '</td>';                                
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } 
    catch (Exception $e) 
    {
        error_log('ERROR - Publisher functions.php GetOndemandActionsJoin() - '.$e->getMessage());
    }
}