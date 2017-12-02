/*! 5O5 Class inventory - v1.0.0
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
    var iworks_5o5_people_list = [];
    var data = {
        action: 'iworks_5o5_persons_list',
        _wpnonce: iworks_5o5.nonces.iworks_5o5_persons_list_nonce,
        user_id: iworks_5o5.user_id
    };
    $.post(ajaxurl, data, function(response) {
        if ( response.success ) {
            iworks_5o5_people_list = response.data;
            $('select', $('#iworks-crews-list') ).select2({
                data: iworks_5o5_people_list
            });
        }
    });
    $( function() {
        $('.iworks-add-crew').on( 'click', function() {
            var $el = $('#iworks-crews-list');
            var id = Date.now();
            var template = wp.template( 'iworks-boat-crew' );
            $el.append( template( {
                id: id
            } ) ).ready( function() {
                var parent = $('#iworks-crew-'+id);
                $('select', parent).select2({
                    data: iworks_5o5_people_list
                });
            });
            return false;
        });
    } );
});

jQuery( document ).ready(function($) {
});

jQuery( document ).ready(function($) {
    $('select.iworks-select2').select2();
});
