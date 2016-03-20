/**
 * File       ra_calendar_download.js
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    
 * License    GNU General Public License version 2, or later.
 */

jQuery(function($) {
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
    
    if ($('#group').children('option').length == 1)
    {
        $('#group').hide();
    }
    else
    {
        $('#group').show();
    }
    
    jQuery( "#from_datepicker" ).datepicker({
            beforeShow: function(input, inst) {
                            $('#ui-datepicker-div').removeClass(function() {return $('input').get(0).id;});
                            $('#ui-datepicker-div').addClass("myClass");
                    }}   
            );
    jQuery( "#to_datepicker" ).datepicker();
    jQuery( "#from_datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
    jQuery( "#to_datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
    jQuery( "#from_datepicker" ).datepicker( "setDate", "+0" );
    jQuery( "#to_datepicker" ).datepicker( "setDate", "+365" );
    jQuery.ui.slider.prototype.widgetEventPrefix = 'slider';

    jQuery( "#slider-range" ).slider(
     {
      animate: "fast",
      range: true,
      min: 0,
      max: 20,
      step: 0.5,
      values: [ 1, 15 ],
      slide: function( event, ui ) {jQuery( "#distance" ).val( ui.values[ 0 ] + " miles - " + ui.values[ 1 ] + " miles" );}
     });
    jQuery( "#distance" ).val(jQuery( "#slider-range" ).slider( "values", 0 ) + " miles - " + jQuery( "#slider-range" ).slider( "values", 1 ) + " miles" );

    $('a.more_options').click(function(event)
    { /* find all a.read_more elements and bind the following code to them */
        event.preventDefault(); /* prevent the a from changing the url */
        $(this).parents('.item').find('.download_details').toggle();
        if ($(this).parents('.item').find('.download_details').is(':hidden'))
        {    
            $('a.more_options').text("More Options...");
        }
        else
        {
            $('a.more_options').text("Less Options");
        }
    });
});

