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
        $userAdminId = filter_input(INPUT_POST, 'userAdminId');
        $userId = filter_input(INPUT_POST, 'userToResetId');
        return ResetUserPassword($mainactions, $dbactions, $userId, $userAdminId);
    case "users_delete":
        $userIds = filter_input(INPUT_POST, 'userIds');
        return DeleteUsers($dbactions, $userIds);
    case "user_update":
        $userId = filter_input(INPUT_POST, 'userId');
        $fullName = filter_input(INPUT_POST, 'fullName');
        $email = filter_input(INPUT_POST, 'email');
        $username = filter_input(INPUT_POST, 'username');
        $groupName = filter_input(INPUT_POST, 'groupName');
        $roleName = filter_input(INPUT_POST, 'roleName');
        return UpdateUser($dbactions, $userId, $fullName, $email, $username, $groupName, $roleName);        
    case "mark_ondemand_video_to_join":
        $ondemandIdList = filter_input(INPUT_POST, 'ondemandIdList');
        $userId = filter_input(INPUT_POST, 'userId');
        return MarkOndemandVideoToJoin($dbactions, $ondemandIdList, $userId);
    case "get_datatable_ondemand_actions_join":
        $userId = filter_input(INPUT_POST, 'userId');
        return GetDataTableOndemandActionsJoin($host, $uname, $pwd, $database, $userId);
    case "get_datatable_ondemand_actions_convert":
        $userId = filter_input(INPUT_POST, 'userId');
        return GetDataTableOndemandActionsConvert($host, $uname, $pwd, $database, $userId);      
    case "get_datatable_users":
        return GetDataTableUsers($host, $uname, $pwd, $database);        
    case "delete_ondemand_actions_join":
        $joinSelectedIds = filter_input(INPUT_POST, 'joinSelectedIds');
        return DeleteOndemandActionsJoin($dbactions, $joinSelectedIds);
    case "delete_ondemand_actions_convert":
        $convertSelectedIds = filter_input(INPUT_POST, 'convertSelectedIds');
        return DeleteOndemandActionsConvert($dbactions, $convertSelectedIds); 
    case "get_user_total_number":
        return GetUserTotalNumber($dbactions);
    case "get_user_logged_number":
        return GetUserLoggedNumber($dbactions);
    case "events_live_view_link":
        $eventsLiveId = filter_input(INPUT_POST, 'eventsLiveId');
        $eventsLivePlayerType = filter_input(INPUT_POST, 'eventsLivePlayerType');
        return GetEventsLiveViewLink($dbactions, $eventsLiveId, $eventsLivePlayerType);
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
        "L'utente potrà fare login qui: https://www.streamlis.it/login.php\r\n".
        "\r\n".
        "\r\n".
        "Grazie per la collaborazione,\r\n".
        $mainactions->sitename;
    
    if (!$mainactions->SendMail($mailTo, $mailSubject, $mailBody))
    {
        error_log("\ERROR - functions.php ResetUserPassword() - SendMail() FAILED!");
    }
    
    return TRUE;
}

function MarkOndemandVideoToJoin($dbactions, $ondemandIdList, $userId)
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

        if (!$dbactions->MarkOndemandVideoToJoin($ondemandIdList, $userId))
        {
            error_log("ERROR - MarkOndemandVideoToJoin() FAILED! " . $dbactions->GetErrorMessage());
            echo "1";
        }
        else
        {
            echo "0";
        }
}

function GetDataTableOndemandActionsJoin($host, $uname, $pwd, $database, $userId = NULL)
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
*  https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
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
    array( 'db' => 'ondemand_actions_join_id', 'dt' => 0 ),
    array( 'db' => 'ondemand_actions_join_list', 'dt' => 1 ),
    array( 'db' => 'ondemand_actions_join_status', 'dt' => 2,
        'formatter' => function( $d, $row ) {
            switch ($d) 
            {
                case 0:
                    return '<span class="label label-warning">SCHEDULATA</span>';
                case 1:
                    return '<span class="label label-info">IN CORSO...</span>';
                case 2:
                    return '<span class="label label-success">TERMINATA CON SUCCESSO</span>';
                case -1:
                    return '<span class="label label-danger">TERMINATA CON ERRORI</span>';
            }
        }),
    array(
        'db'        => 'ondemand_actions_join_date',
        'dt'        => 3,
        'formatter' => function( $d, $row ) {
            return strftime('%e %B %Y ore %H:%M:%S', strtotime($d));
        }),
    array(
        'db'        => 'ondemand_actions_user_id',
        'dt'        => 4
        ),                
    array(
        'db' => 'id',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            // Technically a DOM id cannot start with an integer, so we prefix
            // a string. This can also be useful if you have multiple tables
            // to ensure that the id is unique with a different prefix
            return 'row_'.$d;
        }
    )
);
 
// SQL server connection information
$sql_details = array(
    'user' => $uname,
    'pass' => $pwd,
    'db'   => $database,
    'host' => $host
);
 
$where = empty($userId) ? NULL : 'ondemand_actions_user_id = ' . $userId;
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
//require( 'ssp.php' );
 
echo json_encode(
    SSP::complex( $_POST, $sql_details, $table, $primaryKey, $columns, $where)
);
}

function GetDataTableOndemandActionsConvert($host, $uname, $pwd, $database, $userId = NULL)
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
*  https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'ondemand_actions_convert';
 
// Table's primary key
$primaryKey = 'ondemand_actions_convert_id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier.
$columns = array(
    array( 'db' => 'ondemand_actions_convert_id', 'dt' => 0 ),
    array( 'db' => 'ondemand_actions_convert_list', 'dt' => 1 ),
    array( 'db' => 'ondemand_actions_convert_status', 'dt' => 2,
        'formatter' => function( $d, $row ) {
            switch ($d) 
            {
                case 0:
                    return '<span class="label label-warning">SCHEDULATA</span>';
                case 1:
                    return '<span class="label label-info">IN CORSO...</span>';
                case 2:
                    return '<span class="label label-success">TERMINATA CON SUCCESSO</span>';
                case -1:
                    return '<span class="label label-danger">TERMINATA CON ERRORI</span>';
            }
        }),
    array(
        'db'        => 'ondemand_actions_convert_date',
        'dt'        => 3,
        'formatter' => function( $d, $row ) {
            return strftime('%e %B %Y ore %H:%M:%S', strtotime($d));
        }),
    array(
        'db'        => 'ondemand_actions_user_id',
        'dt'        => 4
        ),                
    array(
        'db' => 'id',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            // Technically a DOM id cannot start with an integer, so we prefix
            // a string. This can also be useful if you have multiple tables
            // to ensure that the id is unique with a different prefix
            return 'row_'.$d;
        }
    )
);
 
// SQL server connection information
$sql_details = array(
    'user' => $uname,
    'pass' => $pwd,
    'db'   => $database,
    'host' => $host
);
 
$where = empty($userId) ? NULL : 'ondemand_actions_user_id = ' . $userId;
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::complex( $_POST, $sql_details, $table, $primaryKey, $columns, $where)
);
}

function DeleteOndemandActionsJoin($dbactions, $joinSelectedIds)
{
    $joinIdsArray = explode(",",$joinSelectedIds);
    
    //error_log("INFO - join ids: " . $joinSelectedIds);
    
    if (!$dbactions->DeleteOnDemandActionsJoin($joinIdsArray))
    {
        error_log("ERROR - functions.php DeleteOndemandActionsJoin() FAILED! " . $dbactions->GetErrorMessage());
        return FALSE;
    }
    
    if (!$dbactions->ResetOndemandVideoActionsJoin($joinIdsArray))
    {
        error_log("ERROR - functions.php ResetOndemandVideoActionsJoin() FAILED! " . $dbactions->GetErrorMessage());
        return FALSE;
    }
    
    return TRUE;
}

function GetDataTableUsers($host, $uname, $pwd, $database)
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
*  https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'users';
 
// Table's primary key
$primaryKey = 'id_user';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier.
$columns = array(
    array( 'db' => '`u`.id_user', 'dt' => 0 , 'field' => 'id_user'),
    array( 'db' => '`u`.name', 'dt' => 1 , 'field' => 'name'),
    array( 'db' => '`u`.email', 'dt' => 2 , 'field' => 'email'),
    array( 'db' => '`u`.username', 'dt' => 3 , 'field' => 'username'),                
    array( 'db' => '`g`.group_name', 'dt' => 4 , 'field' => 'group_name'),   
    array( 'db' => '`u`.user_role_id', 'dt' => 5 ,
            'formatter' => function( $d, $row ) {
            switch ($d) 
            {
                case 1:
                    return '<span class="label label-success">Admin</span>';
                case 2:
                    return '<span class="label label-default">Viewer</span>';
                case 3:
                    return '<span class="label label-warning">Publisher</span>';
            }
        }, 'field' => 'user_role_id'),
    array( 'db' => 'user_logged', 'dt' => 6 , 'field' => 'user_logged'),
    array(
        'db' => '`u`.id',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            // Technically a DOM id cannot start with an integer, so we prefix
            // a string. This can also be useful if you have multiple tables
            // to ensure that the id is unique with a different prefix
            return 'row_'.$d;
        }, 'field' => 'id')
);
 
// SQL server connection information
$sql_details = array(
    'user' => $uname,
    'pass' => $pwd,
    'db'   => $database,
    'host' => $host
);
 
$join = "FROM `{$table}` AS `u` INNER JOIN `groups` AS `g` ON (`u`.`user_group_id` = `g`.`group_id`)";
//$where = empty($userId) ? NULL : 'ondemand_actions_user_id = ' . $userId;
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
//require( 'ssp.class.php' );
require( 'ssp.php' );
 
echo json_encode(
    SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $join)
);
}

function DeleteOndemandActionsConvert($dbactions, $convertSelectedIds)
{
    $convertIdsArray = explode(",",$convertSelectedIds);
    
    //error_log("INFO - convert ids: " . $convertSelectedIds);
    
    if (!$dbactions->DeleteOnDemandActionsConvert($convertIdsArray))
    {
        error_log("ERROR - functions.php DeleteOndemandActionsConvert() FAILED! " . $dbactions->GetErrorMessage());
        return FALSE;
    }
    
    if (!$dbactions->ResetOndemandVideoActionsConvert($convertIdsArray))
    {
        error_log("ERROR - functions.php ResetOndemandVideoActionsConvert() FAILED! " . $dbactions->GetErrorMessage());
        return FALSE;
    }
    
    return TRUE;
}

function GetUserTotalNumber($dbactions)
{
    $num = $dbactions->GetUserTotalNumber();    
    
    if (!$num)
    {
        echo "Non disponibile";
    }
    else
    {
        echo $num;
    }
}

function GetUserLoggedNumber($dbactions)
{
    $num = $dbactions->GetUserLoggedNumber();    
    
    if (!$num)
    {
        echo "Non disponibile";
    }
    else
    {
        echo $num;
    }    
}

function DeleteUsers($dbactions, $userIds)
{
    if (!$dbactions->DeleteUsers($userIds))
    {
        error_log("ERROR - functions.php DeleteUsers() FAILED! " . $dbactions->GetErrorMessage());
        return FALSE;
    }
    
    return TRUE;
}

function UpdateUser($dbactions, $userId, $fullName, $email, $username, $groupName, $roleName)
{
    if (!$dbactions->UpdateUser($userId, $fullName, $email, $username, $groupName, $roleName))
    {
        error_log("ERROR - functions.php UpdateUser() FAILED! " . $dbactions->GetErrorMessage());
        return FALSE;
    }
    
    return TRUE;
}

function GetEventsLiveViewLink($dbactions, $eventsLiveId, $eventsLivePlayerType)
{
    $result = $dbactions->GetLiveEventsById($eventsLiveId);
    if (!$result)
    {
        error_log("ERROR - functions.php GetLiveEventsById() FAILED! " . $dbactions->GetErrorMessage());
        echo "false";
        return false;
    }
    $row = mysql_fetch_array($result);
    
    $link = "https://" . filter_input(INPUT_SERVER, 'SERVER_NAME');
    switch (strtolower($eventsLivePlayerType))
    {
        case "player_desktop":
            $link .= "/players/jwplayer/";
            break;
        case "player_smartphone":
            $link .= "/players/flowplayer/";
            break;
        case "player_iphone":
            $link .= "/players/html5/";
            break;
    }
    
    $link .= "watch.php?t=" . $row['live_token'];
    
    echo $link;
    return true;
}