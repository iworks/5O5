/*! 5O5 Class inventory - v0.0.1
 * https://iworks.pl/
 * Copyright (c) 2017; * Licensed GPLv2+
 */
jQuery( document ).ready(function($) {
    $( function() {
        $( ".iworks-5o5-row .datepicker" ).each( function() {
            var format = $(this).data('date-format') || 'yy-mm-dd';
            $(this).datepicker({ dateFormat: format });
        });
    } );
});

jQuery( document ).ready(function($) {
    $('select.iworks-select2').select2();
});
