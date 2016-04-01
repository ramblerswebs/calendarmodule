<?php defined('_JEXEC') or die;

/**
 * File       mod_ra_calendar_download.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    
 * License    GNU General Public License version 2, or later.
 */

// Include the helper.
require_once __DIR__ . '/helper.php';

// Get the configuration which was entered by the administrator
$ID = $params->get('id');


$js = <<<JS
jQuery(function ($) {

    /*
     * Now add some jQuery to support the div event for More Options. 
     * This needs to ensure it hides/shows the correct div for extra details. 
     */
    $('#more_options').click(function(event)
    { /* find all a.read_more elements and bind the following code to them */
        event.preventDefault(); /* prevent the a from changing the url */
        $('#download_details').toggle();
        if ($('#download_details').is(':hidden'))
        {    
            $('#more_options').text("More Options...");
        }
        else
        {
            $('#more_options').text("Less Options");
        }
    });
   
    /*
     * Create a function to handle the click event of the form.
     * This function needs to collate the information and submit to an Ajax call to the server. 
     * The response is the ICS information or a message.
     * Once the response is recieved it is stored in a form and submitted to calendar_download.php
     * which returns if with the correct html headers set. 
     */

    $(document).on('click', 'input[id=submit]', function () {
            var dateMask = 0;
            dateMask = dateMask + ($('input[name=monday]').is(':checked') * 1); 
            dateMask = dateMask + ($('input[name=tuesday]').is(':checked') * 2); 
            dateMask = dateMask + ($('input[name=wednesday]').is(':checked') * 4); 
            dateMask = dateMask + ($('input[name=thursday]').is(':checked') * 8); 
            dateMask = dateMask + ($('input[name=friday]').is(':checked') * 16); 
            dateMask = dateMask + ($('input[name=saturday]').is(':checked') * 32); 
            dateMask = dateMask + ($('input[name=sunday]').is(':checked') * 64); 

            var gradeMask = 0;
            gradeMask = gradeMask + ($('input[name=easyaccess]').is(':checked') * 1); 
            gradeMask = gradeMask + ($('input[name=easy]').is(':checked') * 2); 
            gradeMask = gradeMask + ($('input[name=leisurely]').is(':checked') * 4); 
            gradeMask = gradeMask + ($('input[name=moderate]').is(':checked') * 8); 
            gradeMask = gradeMask + ($('input[name=strenuous]').is(':checked') * 16); 
            gradeMask = gradeMask + ($('input[name=technical]').is(':checked') * 32); 

            var value   = $('#group').val() + '-' + 
                          $('input[name=fromdate]').val() + '-' + 
                          $('input[name=todate]').val() + '-' + 
                          dateMask + '-' + 
                          gradeMask + '-' + 
                          jQuery( "#slider-range" ).slider( "values", 0 ) + '-' + 
                          jQuery( "#slider-range" ).slider( "values", 1 )  ,
                request = {
                                    'option' : 'com_ajax',
                                    'module' : 'ra_calendar_download',
                                    'data'   :  value,
                                    'format' : 'raw'
                };

            // This is the Ajax Call to Get the response and deal with it. 
            $.ajax({
                type   : 'POST',
                data   : request,
                success: function (response) {
                        // Update the field which will be submitted
                        $('textarea#icsdata').val(response);
                        if ($('textarea#icsdata').val().substring(0,5) === "BEGIN")
                        {
                            // Can you determine how many walks have been downloaded?
        
                            // Tell the user their walks are being downloaded
                            $('#error').html('Your walks are being downloaded.');
                            // valid response so submit the form
                            $('form#finalstage').submit();
                        }
                        else
                        {
                            // Write the error out for the user
                            $('#error').html(response);
                        }
                    }
        });
        return false;
    });

   /*
   ** Hide the select option if there is only one entry
   */
        
   if ($('#group').children('option').length == 1)
    {
        $('#group').hide();
    }
    else
    {
        $('#group').show();
    }

    /*
     * Now format the datepicker displays
     * Not sure we need the code under here.
    */
    jQuery( "#to_datepicker" ).datepicker();
    jQuery( "#from_datepicker" ).datepicker();
    jQuery( "#from_datepicker" ).datepicker( "setDate", "+0" );
    jQuery( "#from_datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
    jQuery( "#to_datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
    jQuery( "#to_datepicker" ).datepicker( "setDate", "+365" );

    /*
     * Now customise the distance slider
     * The first line ensures it is displayed correctly.
     */
    jQuery.ui.slider.prototype.widgetEventPrefix = 'xx-slider';
    jQuery( "#slider-range" ).slider(
     {
      animate: "fast",
      range: true,
      min: 0,
      max: 30,
      step: 0.5,
      values: [ 1, 25 ],
      slide: function( event, ui ) {jQuery( "#distance" ).val( ui.values[ 0 ] + " miles - " + ui.values[ 1 ] + " miles" );}
     });
    jQuery( "#distance" ).val(jQuery( "#slider-range" ).slider( "values", 0 ) + " miles - " + jQuery( "#slider-range" ).slider( "values", 1 ) + " miles" );

});
JS;

/* Now replace some of the ID values to ensure it is a unique instance */
$js = str_ireplace('fromdate', $ID . '_fromdate', $js );
$js = str_ireplace('to_datepicker', $ID . '_to_datepicker', $js );
$js = str_ireplace('from_datepicker', $ID . '_from_datepicker', $js );
$js = str_ireplace('todate', $ID . '_todate', $js );
$js = str_ireplace('#slider-range', '#' . $ID . '_slider-range', $js );
$js = str_ireplace('#slider-range', '#' . $ID . '_slider-range', $js );
$js = str_ireplace('#distance', '#' . $ID . '_distance', $js );
$js = str_ireplace('#icsdata', '#' . $ID . '_icsdata', $js );
$js = str_ireplace('#finalstage', '#' . $ID . '_finalstage', $js );
$js = str_ireplace('#group', '#' . $ID . '_group', $js );
$js = str_ireplace('#error', '#' . $ID . '_error', $js );
$js = str_ireplace('#download_details', '#' . $ID . '_download_details', $js );
$js = str_ireplace('#more_options', '#' . $ID . '_more_options', $js );

$js = str_ireplace('=monday', '=' . $ID . '_monday', $js );
$js = str_ireplace('=tuesday', '=' . $ID . '_tuesday', $js );
$js = str_ireplace('=wednesday', '=' . $ID . '_wednesday', $js );
$js = str_ireplace('=thursday', '=' . $ID . '_thursday', $js );
$js = str_ireplace('=friday', '=' . $ID . '_friday', $js );
$js = str_ireplace('=saturday', '=' . $ID . '_saturday', $js );
$js = str_ireplace('=sunday', '=' . $ID . '_sunday', $js );

$js = str_ireplace('=easyaccess', '=' . $ID . '_easyaccess', $js );
$js = str_ireplace('=easy', '=' . $ID . '_easy', $js );
$js = str_ireplace('=leisurely', '=' . $ID . '_leisurely', $js );
$js = str_ireplace('=moderate', '=' . $ID . '_moderate', $js );
$js = str_ireplace('=strenuous', '=' . $ID . '_strenuous', $js );
$js = str_ireplace('=technical', '=' . $ID . '_technical', $js );

$js = str_ireplace('id=submit', 'id=' . $ID . '_submit', $js );
$js = str_ireplace('xx-slider', 'xx-' . $ID . 'slider', $js );

// Instantiate global document object
$doc = JFactory::getDocument();

$doc->addScriptDeclaration($js);
require JModuleHelper::getLayoutPath('mod_ra_calendar_download');
