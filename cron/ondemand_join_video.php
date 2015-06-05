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

// PRIMA DI ESEGUIRE CREO LA CARTELLA DI LAVORO SE NON ESISTE O LA SVUOTO
if (!file_exists($ondemand_actions_path))
{
    mkdir($ondemand_actions_path, 0755, true);
}
else
{
    $fsactions->deleteAll($ondemand_actions_path, true);
}

// PRIMA DI ESEGUIRE RIMUOVO I VECCHI FILE DI LOG join_*.log
array_map('unlink', glob("/var/log/nginx/join_*.log"));

while($row = mysql_fetch_array($actionsJoin))
{
    $ondemandVideoList = explode(",", $row['ondemand_actions_join_list']);
    
    $ondemandVideoInfos = $dbactions->GetOndemandEventsByIds($ondemandVideoList);
    
    if (!$ondemandVideoInfos)
    {
        error_log("ERROR - ondemand_join_video.php GetOndemandEventsByIds() ACTION->[" . $row['ondemand_actions_join_id'] ."] FAILED! - " . $dbactions->GetErrorMessage());
        continue;
    }

    try
    {
        $videoToJoinNumber = mysql_num_rows($ondemandVideoInfos);

        $docRoot = getenv("DOCUMENT_ROOT");
        $ondemandActionFilename = $docRoot . "/scripts/" . "action-" . $row['ondemand_actions_join_id'] . ".bash";
        $bashInitHead = '#!/bin/bash';

        file_put_contents($ondemandActionFilename, $bashInitHead, LOCK_EX);
       
        $mkfifoCommandLine = "\n\n";
        $catCommandLine = "cat ";
        for ($i = 1; $i <= $videoToJoinNumber ; $i++) 
        {
            $fifoFilename = $ondemand_actions_path . "fifo-" . $row['ondemand_actions_join_id'] . "-" . $i .".v";
            
            $mkfifoCommandLine .= "/usr/bin/mkfifo " . $fifoFilename . "\n";
            $catCommandLine .= $fifoFilename . " ";
        }
        $mkfifoCommandLine .= "/usr/bin/mkfifo " . $ondemand_actions_path . "fifo-" . $row['ondemand_actions_join_id'] . "-all.v\n";
        
        $fifoFilenameAll = $ondemand_actions_path . "fifo-" . $row['ondemand_actions_join_id'] . "-all.v";
        $catCommandLine .= "> " . $fifoFilenameAll . " & ";
        
        file_put_contents($ondemandActionFilename, $mkfifoCommandLine, FILE_APPEND | LOCK_EX);
        file_put_contents($ondemandActionFilename, "\n\n", FILE_APPEND | LOCK_EX);
        
        $count = 1;
        $avconvCommandLineInit = '';
        $videoFileNameArray = array();
        while($ondemandVideo = mysql_fetch_array($ondemandVideoInfos))
        {
            $videoFilenameSrc = $ondemandVideo['ondemand_path'] . $ondemandVideo['ondemand_filename'];
            $videoFilenameDst = $ondemand_actions_path . $ondemandVideo['ondemand_filename'];
            
            if (!copy($videoFilenameSrc, $videoFilenameDst))
            {
                error_log("ERROR - ondemand_join_video.php - ACTIONS-> " . $row['ondemand_actions_join_id'] . " - SRC-> [" . $videoFilenameSrc . "] DST-> [" . $videoFilenameDst . "]");
            }
            
            $fifoFilename = $ondemand_actions_path . "fifo-" . $row['ondemand_actions_join_id'] . "-" . $count .".v";
            
            // Memorizzo i nomi dei files da unire.
            $videoFileNameArray[] = $ondemandVideo['ondemand_filename'];
            
            if ($count == 1)
            {
                $avconvCommandLineInit .= '/usr/bin/avconv -i ' . $videoFilenameDst . ' -an -f yuv4mpegpipe - > ' . $fifoFilename .' < /dev/null & ';
            }
            else
            {
                $avconvCommandLineInit .= '{ /usr/bin/avconv -i ' . $videoFilenameDst . ' -an -f yuv4mpegpipe - < /dev/null | tail -n +2 > ' . $fifoFilename . ' ; } & ';
            }
            
            $count++;
        }
        
        $videoFilenameAll = $ondemand_actions_path . $row['ondemand_actions_join_id'] . '-all.flv';
        $avconvCommandLineFin = '/usr/bin/avconv -f yuv4mpegpipe -i ' . $fifoFilenameAll . ' -vcodec libx264 -profile:v main -y ' . $videoFilenameAll;
        
        $avconvCommandLine = $avconvCommandLineInit . $catCommandLine . $avconvCommandLineFin . " > /var/log/nginx/" . $row['ondemand_actions_join_id'] . ".log 2>&1";
        
        file_put_contents($ondemandActionFilename, $avconvCommandLine, FILE_APPEND | LOCK_EX);
       
        // IMPOSTO I PERMESSI DI ESECUZIONE 
        chmod($ondemandActionFilename, 0755);
        
        //ESEGUO AVCONV PER UNIRE I VIDEO
        $output = shell_exec($ondemandActionFilename);
        
        echo "\nINFO - ACTION-> " . $row['ondemand_actions_join_id'] . " - EXEC-> " . $ondemandActionFilename ."\n" . $output . "\n";
        
        if (!file_exists($videoFilenameAll))
        {
            error_log("ERROR - ondemand_join_video.php - ACTIONS-> " . $row['ondemand_actions_join_id'] . " - Il file [" . $videoFilenameAll . "] non esiste!");
            continue;
        }
        
        // CANCELLO I FILE ORIGINALI CHE SONO STATI UNITI
        foreach ($videoFileNameArray as $filename) 
        {
            $videoFilenameToDelete = $ondemand_actions_path . $filename;
            
            if (file_exists($videoFilenameToDelete))
            {
                unlink($videoFilenameToDelete);
            }
        }
        
        //ESEGUO YAMDI PER AGGIUNGERE L'INDICE AL VIDEO UNITO FINALE E LO SALVO CON IL NOME DEL PRIMO VIDEO.
        $yamdiCommandLine='/usr/bin/yamdi -i ' . $videoFilenameAll . ' -o ' . $ondemand_actions_path . $videoFileNameArray[0];
        system($yamdiCommandLine, $retval);
        
        
    } 
    catch (Exception $e) 
    {
        error_log("ERROR - ondemand_join_video.php - ACTIONS-> " . $row['ondemand_actions_join_id'] . " - " . $e->getMessage());
        continue;
    }
}


