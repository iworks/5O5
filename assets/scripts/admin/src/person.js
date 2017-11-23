jQuery( document ).ready(function($) {
    $( function() {
        $('.iworks-add-boat').on( 'click', function() {
            var $el = $('#iworks-boats-list');
            var template = wp.template( 'iworks-person-boat' );
            $el.append( template( { name: "World" } ) );

            return false;
        });
    } );
});
