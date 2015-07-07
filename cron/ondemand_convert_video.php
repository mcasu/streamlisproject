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

$actionsConvert = $dbactions->GetAllOnDemandActionsConvert();

if (!$actionsConvert)
{
    error_log("ERROR - ondemand_convert_video.php GetAllOnDemandActionsConvert() FAILED! - " . $dbactions->GetErrorMessage());
    exit(1);
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
        
        $ondemandVideoInfos = $dbactions->GetOndemandEventsByIds($ondemandVideoList);

        if (!$ondemandVideoInfos)
        {
            throw new Exception("GetOndemandEventsByIds() FAILED! - " . $dbactions->GetErrorMessage());
        }        
        
        $videoToConvertNumber = mysql_num_rows($ondemandVideoInfos);

        if ($videoToConvertNumber < 1)
        {
            throw new Exception("GetOndemandEventsByIds() ritorna 0 record (forse i video selezionati sono stati cancellati??)");
        }
        
        while($ondemandVideo = mysql_fetch_array($ondemandVideoInfos))
        {
            $streamName = $ondemandVideo['ondemand_publish_code'];
            $videoBasename = basename($ondemandVideo['ondemand_filename'], '.flv');
            $videoFlvFilename = $ondemandVideo['ondemand_filename'];
            $videoMp4Filename = $videoBasename . ".mp4";
            $videoMp4Dir = $ondemand_mp4_record_filepath.strtolower($streamName);
            $videoFlvDir = $ondemand_flash_record_filepath.strtolower($streamName);

            // SE LA CARTELLA DEL VIDEO ONDEMAND MP4 NON ESISTE, LA CREO
            if (!file_exists($videoMp4Dir))
            {
                mkdir($videoMp4Dir, 0755, true);
                error_log("WARNING - ondemand_convert_video.php Created folder [".$videoMp4Dir."]");
            }

            $docRoot = getenv("DOCUMENT_ROOT");
            
            if (file_exists($videoMp4Dir.'/'.$videoMp4Filename))
            {
                error_log("WARNING - ondemand_convert_video.php Il file [".$ondemand_mp4_record_filepath.$videoMp4Filename."] esiste gia'.");
                continue;
            }
            
            // ESEGUO LA CONVERSIONE DAL .FLV A .MP4 TRAMITE LO SCRIPT BASH
            $output = shell_exec($docRoot.'/scripts/convert_video.bash '.$videoFlvDir."/".$videoFlvFilename.' '.$videoMp4Dir.'/'.$videoMp4Filename.' '.$videoFlvFilename);

            // CREO IL LINK SIMBOLICO AL FILE MP4
            if (is_link($ondemand_mp4_record_filepath.$videoMp4Filename))
            {
                error_log("WARNING - ondemand_convert_video.php Il link [".$ondemand_mp4_record_filepath.$videoMp4Filename."] esiste gia'.");
                unlink($ondemand_mp4_record_filepath.$videoMp4Filename);
            }
            if (!symlink($videoMp4Dir."/".$videoMp4Filename, $ondemand_mp4_record_filepath.$videoMp4Filename))
            {
                throw new Exception('Creazione del link simbolico ['. $ondemand_mp4_record_filepath.$videoMp4Filename .'] fallita!');
            }
        }
        
        // IMPOSTO LO STATO DELL'OPERAZIONE A 2 - TERMINATA CON SUCCESSO
        $dbactions->SetOndemandActionsConvertStatus($row['ondemand_actions_convert_id'], 2);        
    
    } 
    catch (Exception $e) 
    {
        error_log("ERROR - ondemand_convert_video.php ACTION-> " . $row['ondemand_actions_convert_id'] . " - " . $e->getMessage());
        // IMPOSTO LO STATO DELL'OPERAZIONE A 0 - SCHEDULATA
        $dbactions->SetOndemandActionsConvertStatus($row['ondemand_actions_convert_id'], -1);
        
        continue;
    }
    
}






