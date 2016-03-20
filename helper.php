<?php defined('_JEXEC') or die;
/**
 * File       helper.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    
 * License    GNU General Public License version 2, or later.
 */
class modRaCalendarDownloadHelper
{
    function customError($errno, $errstr) {
        return "Error Raised: " . $errstr ;
    }    

    public static function getAjax()
    {
        // Ensure we have a custom Error Handler.
        set_error_handler("customError");
        
        $input = JFactory::getApplication()->input;
        $data  = $input->get('data');

        // Define the data. Received in the format <group>.<startdate>.<enddate>
        // Dates are supplied in the format ddmmyyyy
        $items = explode('-', $data) ;
        $group = $items[0];
        $startdate = $items[1];
        $enddate = $items[2];
        $dayMask = $items[3];
        $gradeMask = $items[4];
        $distanceLow = $items[5];
        $distanceHigh = $items[6];

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

            // Filter the walks to our specific dates
            $walks->filterDateRange($s_date, $e_date);

            // Filter walks based on Distance
            $walks->filterDistance($distanceLow, $distanceHigh);

            // Filter walks based on day of Week
            // create the array of weeks
            if ($dayMask & 1) {$arrayofDays[] = "Monday";}
            if ($dayMask & 2) {$arrayofDays[] = "Tuesday";}
            if ($dayMask & 4) {$arrayofDays[] = "Wednesday";}
            if ($dayMask & 8) {$arrayofDays[] = "Thursday";}
            if ($dayMask & 18) {$arrayofDays[] = "Friday";}
            if ($dayMask & 32) {$arrayofDays[] = "Saturday";}
            if ($dayMask & 64) {$arrayofDays[] = "Sunday";}

            $walks->filterDayofweek($arrayofDays); 

            // Filter walks based on walk grade
            if ($gradeMask & 1) {$arrayofGrades[] = "Leisurely";}
            if ($gradeMask & 2) {$arrayofGrades[] = "Easy";}
            if ($gradeMask & 4) {$arrayofGrades[] = "Moderate";}
            if ($gradeMask & 8) {$arrayofGrades[] = "Strenuous";}

            $walks->filterNationalGrade($arrayofGrades);

            // Now create the output
            if ($walks->totalWalks() > 0)
            {
                $events = new REventDownload();
                $eventGroup = new REventGroup() ;
                $eventGroup->addWalksArray($walks->allWalks());

                $output = $events->getText($eventGroup);
            }
            else
            {
               return "No walks returned - Please check your filter criteria";
            }
         }
         else
         {
            return "No walks returned - Please check your dates are in the format dd/mm/yyyy (e.g. 07/02/2016)";
         }

         header("Content-type:text/calendar");
         header('Content-Disposition: attachment;filename="ramblers.ics"');
         header('Content-Length: '.strlen($output));
         return $output ;
    }
}
