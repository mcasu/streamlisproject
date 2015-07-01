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


/*** CONVERT VIDEO TO .MP4 AND SAVE TO DISK ***/
if (!file_exists($ondemand_mp4_record_filepath.strtolower($stream_name)))
{
	mkdir($ondemand_mp4_record_filepath.strtolower($stream_name), 0755, true);
	error_log("WARNING - OnRecordDone.php - Created folder ".$ondemand_mp4_record_filepath.strtolower($stream_name));
}

$output = shell_exec($_SERVER['DOCUMENT_ROOT'].'/scripts/convert_video.bash '.$ondemand_path.strtolower($stream_name)."/".$ondemand_basename.' '.$ondemand_mp4_record_filepath.strtolower($stream_name).'/'.$ondemand_filename.'.mp4 '.$ondemand_basename);

$ondemand_mp4_fullpath = $ondemand_mp4_record_filepath.strtolower($stream_name)."/";
if (!symlink($ondemand_mp4_fullpath.$ondemand_filename.".mp4", $ondemand_mp4_record_filepath.$ondemand_filename.".mp4"))
{
	error_log('ERROR - Creazione del link simbolico ['.$ondemand_mp4_record_filepath.$ondemand_filename.'.mp4] fallita!');
}