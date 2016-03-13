/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(function($) {
    jQuery( "#from_datepicker" ).datepicker();
    jQuery( "#to_datepicker" ).datepicker();
    jQuery( "#from_datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
    jQuery( "#to_datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
    jQuery( "#from_datepicker" ).datepicker( "setDate", "+0" );
    jQuery( "#to_datepicker" ).datepicker( "setDate", "+365" );
} );
