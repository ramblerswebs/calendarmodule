<?php defined('_JEXEC') or die;

/**
 * File       mod_ra_calendar_download.php
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    https://github.com/Joomla-Ajax-Interface/Hello-Ajax-World-Module/issues
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU General Public License version 2, or later.
 */

// Include the helper.
require_once __DIR__ . '/helper.php';

// Instantiate global document object
$doc = JFactory::getDocument();

$js = <<<JS
(function ($) {
	$(document).on('click', 'input[type=submit]', function () {
                // Define the information for the call
                var value   = $('#group').val() + '.' + $('input[name=fromdate]').val() + '.' + $('input[name=todate]').val() ,
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
