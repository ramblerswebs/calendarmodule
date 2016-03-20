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

// Instantiate global document object
$doc = JFactory::getDocument();

$js = <<<JS
(function ($) {
	$(document).on('click', 'input[type=submit]', function () {
                var dateMask = 0;
                dateMask = dateMask + ($('input[name=monday]').is(':checked') * 1); 
                dateMask = dateMask + ($('input[name=tuesday]').is(':checked') * 2); 
                dateMask = dateMask + ($('input[name=wednesday]').is(':checked') * 4); 
                dateMask = dateMask + ($('input[name=thursday]').is(':checked') * 8); 
                dateMask = dateMask + ($('input[name=friday]').is(':checked') * 16); 
                dateMask = dateMask + ($('input[name=saturday]').is(':checked') * 32); 
                dateMask = dateMask + ($('input[name=sunday]').is(':checked') * 64); 
                
                var gradeMask = 0;
                gradeMask = gradeMask + ($('input[name=leisurely]').is(':checked') * 1); 
                gradeMask = gradeMask + ($('input[name=easy]').is(':checked') * 2); 
                gradeMask = gradeMask + ($('input[name=moderate]').is(':checked') * 4); 
                gradeMask = gradeMask + ($('input[name=strenuous]').is(':checked') * 8); 

                var value   = $('#group').val() + '-' + $('input[name=fromdate]').val() + '-' + $('input[name=todate]').val() + '-' + dateMask + '-' + gradeMask + '-' + jQuery( "#slider-range" ).slider( "values", 0 ) + '-' + jQuery( "#slider-range" ).slider( "values", 1 )  ,
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
                            $('textarea#icsdata').val(response);
                            if ($('textarea#icsdata').val().substring(0,5) === "BEGIN")
                            {
                                $('.error').html('Your walks are being downloaded.');
                                // valid response so submit the form
                                $('form#finalstage').submit();
                            }
                            else
                            {
                                // Write the error out for the user
                                $('.error').html(response);
                            }
   }
            });
            return false;
	});
})(jQuery)
JS;

$doc->addScriptDeclaration($js);
require JModuleHelper::getLayoutPath('mod_ra_calendar_download');
