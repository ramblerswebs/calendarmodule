<?php defined('_JEXEC') or die;

/**
 * File       default.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    
 * License    GNU General Public License version 2, or later.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Ensure we load the full jQuery library with datepicker
JHtml::_('jquery.framework');
$document = JFactory::getDocument();
$document->addScript('http://code.jquery.com/ui/1.10.3/jquery-ui.js');
$document->addStyleSheet('http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', 'text/css');
$document->addStyleSheet(JURI::base() . 'modules/mod_ra_calendar_download/scripts/css/ra_calendar_download.css', 'text/css');

// Add the script to enable datepicker
$document->addScript(JURI::base() . 'modules/mod_ra_calendar_download/scripts/js/ra_calendar_download.js', "text/javascript");

// Get the configuration which was entered by the administrator
$leadingText = $params->get('leadingText');
$trailingText = $params->get('trailingText');
$buttonText = $params->get('buttonText');
$ramblers_groups = $params->get('groups');
$class = $params->get('moduleclass_sfx');

?>
<div class="ra_calendar_download">
    <div class="leadingtext textdescription"> <?php echo($leadingText); ?> </div>
    <form>
        <span class="item">
            <div class="groupselection">
                <select id="group" name="group" style="margin-top:5px">
                    <?php
                        // Now we need to add the groups into the list.
                        $count = count($ramblers_groups);
                        for ($i = 0; $i < $count; $i++) {
                            $current_group = $ramblers_groups[$i];
                            $group_parts = explode(':',$current_group);
                            echo('<option value="' . $group_parts[0] . '">' . $group_parts[1] . '</option>');
                        }
                    ?>
                </select>
            </div>
            <input type="submit" class="button" value="<?php echo($buttonText) ?>" />
            <a href="#" class="more_options" style="text-align: right">More Options...</a>
            <span class="download_details" style="display:none">
                <br/><br/>
                <label for="from_datepicker">Date Duration</label>
                <input type="text" id="from_datepicker" name="fromdate" value="07/03/2016">
                <input type="text" id="to_datepicker" name="todate" value="21/03/2016">
                <label>Walking Days & Grades</label>
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr border="0">
                        <td border="0">
                            <input type="checkbox" name="monday" checked="true" value="1">Monday</input><br/>
                            <input type="checkbox" name="tuesday" checked="true" value="2">Tuesday</input><br/>
                            <input type="checkbox" name="wednesday" checked="true" value="4">Wednesday</input><br/>
                            <input type="checkbox" name="thursday" checked="true" value="8">Thursday</input><br/>
                            <input type="checkbox" name="friday" checked="true" value="16">Friday</input><br/>
                            <input type="checkbox" name="saturday" checked="true" value="32">Saturday</input><br/>
                            <input type="checkbox" name="sunday" checked="true" value="64">Sunday</input>
                        </td>
                        <td border="0">
                            <input type="checkbox" name="leisurely" checked="true" value="1">Leisurely</input><br/>
                            <input type="checkbox" name="easy" checked="true" value="2">Easy</input><br/>
                            <input type="checkbox" name="moderate" checked="true" value="4">Moderate</input><br/>
                            <input type="checkbox" name="strenuous" checked="true" value="8">Strenuous</input><br/>
                        </td>
                    </tr>
                </table>
                <div>
                  <label for="distance">Walk Distance:</label>
                  <input type="text" id="distance" readonly style="border:0">
                </div>
                <div id="slider-range"></div>
            </span>
        </span>
    </form>
    <div class="trailingtext textdescription"> <?php echo($trailingText); ?> </div>
    <div class='error'></div>
    <div style="display:none">
        <form id="finalstage" action="<?php echo JURI::root() ?>modules/mod_ra_calendar_download/calendar_download.php" method="POST">
            <textarea id="icsdata" name="icsdata"></textarea>
        </form>
    </div>
</div>