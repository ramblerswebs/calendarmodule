<?php defined('_JEXEC') or die;

/**
 * File       default.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    TBD
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU General Public License version 2, or later.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Ensure we load the full jQuery library with datepicker
JHtml::_('jquery.framework');
$document = JFactory::getDocument();
$document->addScript('http://code.jquery.com/ui/1.10.3/jquery-ui.js');
$document->addStyleSheet('http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', 'text/css');

// Add the script to enable datepicker
$document->addScript(JURI::base() . 'modules/mod_ra_calendar_download/scripts/js/datepicker.js', "text/javascript");

// Get the configuration which was entered by the administrator
$introduction = $params->get('groupSelection');
$leadingText = $params->get('leadingText');
$trailingText = $params->get('trailingText');
$buttonText = $params->get('buttonText');
$ramblers_groups = $params->get('groups');


?>
<div> <?php echo($leadingText); ?> </div>
<div> <?php echo($introduction); ?> </div>
<form>
    <select id="group" name="group">
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
    <input type="text" id="from_datepicker" name="fromdate" value="07/03/2016">
    <input type="text" id="to_datepicker" name="todate" value="21/03/2016">
    <br/>
    <input type="submit" value=" <?php echo($buttonText) ?> " />
</form>
<div> <?php echo($trailingText); ?> </div>
<div class='error'></div>
<div style="display:none">
    <form id="finalstage" action="/modules/mod_ra_calendar_download/calendar_download.php" method="POST">
        <textarea id="icsdata" name="icsdata"></textarea>
    </form>
</div>
