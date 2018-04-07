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

require_once("../include/config.php");

$dbactions = $mainactions->GetDBActionsInstance();
$fsactions = $mainactions->GetFSActionsInstance();


$actionsConvertId = filter_input(INPUT_POST, 'acid');
if(!isset($actionsConvertId) || empty($actionsConvertId)) 
{
    $actionsConvert = $dbactions->GetAllOnDemandActionsConvert();

    if (!$actionsConvert)
    {
        error_log("ERROR - ondemand_convert_video.php GetAllOnDemandActionsConvert() FAILED! - " . $dbactions->GetErrorMessage());
        exit(1);
    }
else
{
    $actionsConvert = $dbactions->GetOnDemandActionsConvertById($actionsConvertId);

    if (!$actionsConvert)
    {
        error_log("ERROR - ondemand_convert_video.php GetOnDemandActionsConvertById() FAILED! - " . $dbactions->GetErrorMessage());
        exit(1);
    }
    
}

while($row = mysql_fetch_array($actionsConvert))
{
    // CONTROLLO LO SATO DELL'OPERAZIONE - SE E' DIVERSO DA 0 ALLORA LA IGNORO
    $result = $dbactions->CheckAndUpdateActionsConvertStatus($row['ondemand_actions_convert_id']);

    if ($result == 2)
    {
        error_log("ERROR - ondemand_convert_video.php ACTION-> " . $row['ondemand_actions_convert_id'] . " - CheckAndUpdateActionsConvertStatus() FAILED! - " . $dbactions->GetErrorMessage());
        continue;
    }

    if ($result == 1)
    {
        error_log("WARNING - ondemand_convert_video.php ACTION-> " . $row['ondemand_actions_convert_id'] . " - Operazione gia' in corso.");
        continue;
    }

    //error_log("INFO - ondemand_convert_video.php ACTION->[" . $row['ondemand_actions_convert_id'] . "] - GO!!!");

    try
    {
        // RECUPERO LA LISTA DI VIDEO ONDEMAND DA CONVERTIRE
        $ondemandVideoList = explode(",", $row['ondemand_actions_convert_list']);

        $errorsCount = $mainactions->ConvertOnDemandVideos($ondemandVideoList, $ondemand_mp4_record_filepath, $ondemand_flash_record_filepath);

        if ($errorsCount == 0)
        {
            // IMPOSTO LO STATO DELL'OPERAZIONE A 2 - TERMINATA CON SUCCESSO
            $dbactions->SetOndemandActionsConvertStatus($row['ondemand_actions_convert_id'], 2);        
        }
        else
        {
            throw new Exception("[". $errorsCount ."] operazioni di conversione video fallite!");
        }

    } 
    catch (Exception $e) 
    {
        error_log("ERROR - ondemand_convert_video.php ACTION-> " . $row['ondemand_actions_convert_id'] . " - " . $e->getMessage());
        // IMPOSTO LO STATO DELL'OPERAZIONE A -1 - TERMINATA CON ERRORI
        $dbactions->SetOndemandActionsConvertStatus($row['ondemand_actions_convert_id'], -1);

        continue;
    }

}








