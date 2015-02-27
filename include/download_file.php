<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set("allow_url_fopen", true);

$file_path = filter_input(INPUT_GET, 'file_path');

// the correct way to set the filename is quoting it (double quote):
// Some browsers may work without quotation, but for sure not Firefox and as Mozilla explains, 
// the quotation of the filename in the content-disposition is according to the RFC
// http://kb.mozillazine.org/Filenames_with_spaces_are_truncated_upon_download
header('Content-disposition: attachment; filename="'.basename($file_path).'"');
header("Content-Type: application/force-download");
header('Content-Type: application/octet-stream');
header("Content-Type: application/download");
header('Content-type: video/mp4');
header("Content-Length: " . filesize($file_path));

//$fp = fopen($uri, "r"); 
//while (!feof($fp))
//{
//    echo fread($fp, 65536); 
//    flush(); // this is essential for large downloads
//}  
//fclose($fp);

readfile($file_path);