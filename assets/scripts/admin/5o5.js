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
    var boats = [];
    var data = {
        action: 'iworks_5o5_boats_list',
        nonce: iworks_5o5.nonces.iworks_5o5_nonce_single_boat,
        user_id: iworks_5o5.user_id
    };
    $.post(ajaxurl, data, function(response) {
        if ( response.success ) {
            boats = response.data;
        }
    });
    $( function() {
        $('.iworks-add-boat').on( 'click', function() {
            var $el = $('#iworks-boats-list');
            var id = Date.now();
            var template = wp.template( 'iworks-person-boat' );
            $el.append( template( {
                name: "World",
                id: id
            } ) ).ready( function() {
                var parent = $('#iworks-boat-single-'+id);
                $('select', parent).select2({
                    data: boats
                });
            });
            return false;
        });
    } );
});

jQuery( document ).ready(function($) {
    $('select.iworks-select2').select2();
});
