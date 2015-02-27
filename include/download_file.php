<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set("allow_url_fopen", true);
define("CHUNK_SIZE", 1024*8); // Size (in bytes) of tiles chunk

// Read a file and display its content chunk by chunk
function readfile_chunked($filename, $retbytes = TRUE) 
{
    $buffer = "";
    $cnt =0;

    $handle = fopen($filename, "rb");
    if ($handle === false) 
    {
        return false;
    }
    while (!feof($handle)) 
    {
        $buffer = fread($handle, CHUNK_SIZE);
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) {
          $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) 
    {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}

  
$file_path = filter_input(INPUT_GET, 'file_path');

// The correct way to set the filename is quoting it (double quote):
// Some browsers may work without quotation, but for sure not Firefox and as Mozilla explains, 
// the quotation of the filename in the content-disposition is according to the RFC
// http://kb.mozillazine.org/Filenames_with_spaces_are_truncated_upon_download
header('Content-disposition: attachment; filename="'.basename($file_path).'"');
//header('Content-Type: application/octet-stream');
header("Content-Length: " . filesize($file_path));
header('Content-Transfer-Encoding: chunked'); //changed to chunked
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-type: video/mp4');


set_time_limit(0);
$file = fopen($file_path,"rb");
while(!feof($file))
{
	print(fread($file, CHUNK_SIZE));
	ob_flush();
	flush();
}