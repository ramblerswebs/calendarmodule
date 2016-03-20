/**
 * File       datepicker.js
 * Created    1/17/14 12:29 PM
 * Author     Keith Grimes | webmaster@wiltsswindonramblers.org.uk | http://wiltsswindonramblers.org.uk
 * Support    
 * License    GNU General Public License version 2, or later.
 */

jQuery(function($) {
    jQuery( "#from_datepicker" ).datepicker();
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

