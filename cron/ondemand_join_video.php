<?php

/* 
 * Copyright (C) 2015 marco.casu
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( session_status() == PHP_SESSION_NONE ) 
{
    session_start();
}

require_once("../include/config.php");

$dbactions = $mainactions->GetDBActionsInstance();
$fsactions = $mainactions->GetFSActionsInstance();

$actionsJoin = $dbactions->GetAllOnDemandActionsJoin();

if (!$actionsJoin)
{
    error_log("ERROR - ondemand_join_video.php GetAllOnDemandActionsJoin() FAILED! - " . $dbactions->GetErrorMessage());
    exit(1);
}

while($row = mysql_fetch_array($actionsJoin))
{
    $ondemandVideoList = explode(",", $row['ondemand_actions_join_list']);
    
    $ondemandVideoInfos = $dbactions->GetOndemandEventsByIds($ondemandVideoList);
    
    if (!$ondemandVideoInfos)
    {
        error_log("ERROR - ondemand_join_video.php GetOndemandEventsByIds() ACTION->[" . $row['ondemand_actions_join_id'] ."] FAILED! - " . $dbactions->GetErrorMessage());
        continue;
    }
    
    $videoToJoinNumber = mysql_num_rows($ondemandVideoInfos);

    
    echo "\nINFO - ACTION-> " . $row['ondemand_actions_join_id'] . " - COUNT-> " . $videoToJoinNumber ."\n";
    
    
    if (!file_exists($ondemand_actions_path))
    {
        mkdir($ondemand_actions_path, 0755, true);
    }
    
    $ondemandActionFilename = $ondemand_actions_path . "action-" . $row['ondemand_actions_join_id'] . ".bash";
    $bashInitHead = '#!/bin/bash';
    
    file_put_contents($ondemandActionFilename, $bashInitHead, FILE_APPEND | LOCK_EX);
    
    
}

