<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set("allow_url_fopen", true);

$file_url = filter_input(INPUT_GET, 'file_url');

header('Content-disposition: attachment; filename=adunanza.mp4');
header("Content-Type: application/force-download");
header("Content-Type: application/download");
header('Content-type: video/mp4');
header("Content-Length: " . filesize($file_url));


$fp = fopen($file_url, "r"); 
while (!feof($fp))
{
    echo fread($fp, 65536); 
    flush(); // this is essential for large downloads
}  
fclose($fp);

//readfile($file_url);