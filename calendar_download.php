<?php

/**
 * File       calendar_download.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    
 * License    GNU General Public License version 2, or later.
 */

// Get the values within the Form Post
$output = $_POST["icsdata"];

$length = strlen($output);

header("Content-type:text/calendar");
header('Content-Disposition: attachment; filename=walks.ics"');
header('Content-Length: '.$length);
echo $output;
?>
