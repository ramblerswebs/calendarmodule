<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//set error handler
//set_error_handler("customError");

// Get the values within the Form Post
$output = $_POST["icsdata"];

$length = strlen($output);

header("Content-type:text/calendar");
header('Content-Disposition: attachment; filename=walks.ics"');
header('Content-Length: '.$length);
echo $output;
?>
