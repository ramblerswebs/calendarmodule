<?php defined('_JEXEC') or die;

/**
 * File       helper.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    TBD
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU General Public License version 2, or later.
 */
class modRaCalendarDownloadHelper
{
	public static function getAjax()
	{
		$input = JFactory::getApplication()->input;
		$data  = $input->get('data');
                
                // Define the data. Received in the format <group>.<startdate>.<enddate>
                // Dates are supplied in the format ddmmyyyy
                $items = explode('.', $data) ;
                $group = $items[0];
                $startdate = $items[1];
                $enddate = $items[2];
                
                $s_date = DateTime::createFromFormat('dmY', $startdate);
                $e_date = DateTime::createFromFormat('dmY', $enddate);
                
                // First get the data from the Ramblers Site
                 $url = "http://www.ramblers.org.uk/api/lbs/walks?groups=" . $group ;

                 // Get the JSON information
                 $walkdata = file_get_contents($url);

                 // echo $walkdata ;
                 if ($walkdata != "")
                 {
                     $contents = json_decode($walkdata);
                     unset($walkdata);

                     $walks = new RJsonwalksWalks($contents);
                     unset($contents);

                     $items = $walks->allWalks();

                     $output = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:WILTSSWINDONRAMBLERSCALENDAR\nMETHOD:PUBLISH\n" ;

                     foreach ($items as $walk) {
                         // Only include the walk if it is within the range required
                         if (($walk->walkDate >= $s_date) and ($walk->walkDate <= $e_date))
                         {
                             // First get the information to display from the walk information returned
                             // When does the walk start?
                             $startTime = $walk->walkDate->format('d F Y') . " " . $walk->startLocation->time->format('H:i');
                             // Assuming 2 miles/hour how long will the walk take
                             $durationFullMins = round($walk->distanceMiles * 60 / 2);
                             $durationMins = $durationFullMins % 60;
                             $durationHours = ($durationFullMins - $durationMins) / 60 ;
                             $intervalFormat = "PT". $durationFullMins . "M";
                             $interval = new DateInterval($intervalFormat);
                             // Now calculate when the walk will finish
                             $finishTime = $walk->walkDate->format('d F Y') . " " . $walk->startLocation->time->add($interval)->format('H:i');
                             // Create the description for the walk
                             $description =  strip_tags($walk->description) . " " . CR .
                                             $walk->contactName . " (" . $walk->telephone1 . " " . $walk->telephone2 . ")" .CR .
                                             $walk->distanceMiles . " miles - " . $walk->nationalGrade ;

                             // If there are additional notes, include these
                             if ($walk->additionalNotes != '') {
                                 $description = $description . " - " . strip_tags($walk->additionalNotes);
                             }
                             if ($walk->startLocation->exact) {
                                 if ($walk->startLocation->description != '') {
                                     $startLocation = $walk->startLocation->description . ' (' . $walk->startLocation->gridref . ')';
                                 }
                                 else {
                                     $startLocation = $walk->startLocation->gridref;
                                 }
                             }
                             else {
                                 $startLocation = ".";
                             }

                             // Ensure there are no special characters in the code.
                             $description = preg_replace('/[^A-Za-z0-9\-.:]/', ' ', htmlspecialchars_decode($description)) ;

                             // Now create the event
                             $event = new EVENT( $walk->id ,
                                                 $startTime,
                                                 $finishTime,
                                                 $walk->title,
                                                 $description,
                                                 $startLocation,
                                                 $walk->detailsPageUrl);
                             $output = $output . $event->data;
                             unset($walk);

                             // increment the number of walks
                             $included_walks = $included_walks + 1;
                         }
                         if ($included_walks <= 0)
                         {
                            return "No walks returned - Please check your dates are in the format dd/mm/yyyy (e.g. 07/02/2016)";
                         }
                     }
                 }
                 else
                 {
                    return "No walks returned - Please check your dates are in the format dd/mm/yyyy (e.g. 07/02/2016)";
                 }

                 $output = $output."END:VCALENDAR\n";
                 $length = strlen($output);

                 // http://migration.local/index.php/ne-wiltshire?option=com_ajax&module=ical&method=getWalks&format=raw
                 header("Content-type:text/calendar");
                 header('Content-Disposition: attachment;filename="ramblers.ics"');
                 header('Content-Length: '.strlen($output));
                 return $output ;
	}
}

class EVENT {
    var $data;
    var $name;
    function EVENT($id, $start,$end,$name,$description,$location,$url) {
        $this->name = $name;
        $this->data = "BEGIN:VEVENT\n";
        $this->data = $this->data . "ORGANIZER:webmaster@wiltsswindonramblers.org.uk" . "\n" ;
        $this->data = $this->data . "STATUS:CONFIRMED" . "\n" ;
        $this->data = $this->data . "DTSTART:".date("Ymd\THis",strtotime($start))."\n";
        $this->data = $this->data . "DTEND:".date("Ymd\THis",strtotime($end))."\n";
        $this->data = $this->data . "LOCATION:".$location."\n";
        $this->data = $this->data . "TRANSP:TRANSPARENT\n";
        $this->data = $this->data . "SEQUENCE:" . date("YmdHis") . "\n";
        $this->data = $this->data . "UID:" . $id .  "\n";
        $this->data = $this->data . "DTSTAMP:".date("Ymd\THis\Z")."\n";
        $this->data = $this->data . "SUMMARY:".$name."\n";
        $this->data = $this->data . "DESCRIPTION:".$description."\n";
        $this->data = $this->data . "PRIORITY:1\n";
        $this->data = $this->data . "URL;VALUE=URI:".$url."\n";
        $this->data = $this->data . "CLASS:PUBLIC\n";
        $this->data = $this->data . "END:VEVENT\n";

    }

    function escapeString($string) {
        return preg_replace('/([\,;])/','\\\$1', $string);
    }

    function save() {
        file_put_contents($this->name.".ics",$this->data);
    }
    function show() {
        header("Content-type:text/calendar");
        header('Content-Disposition: attachment; filename="'.$this->name.'.ics"');
        Header('Content-Length: '.strlen($this->data));
        Header('Connection: close');
        echo $this->data;
    }
}
