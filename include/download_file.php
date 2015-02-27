<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$file_url = filter_input(INPUT_GET, 'file_url');

header('Content-disposition: attachment; filename=adunanza.mp4');
header('Content-type: video/mp4');
readfile($file_url);