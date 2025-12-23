( function( $ ) {
    'use strict';
    $( document ).ready(function() { 

        let EXMS_ATTENDANCE_FRONTEND = { 

            /**
             *  Initialize the functionality
             */
            init: function() {
                this.groupOnChange();
                this.courseOnChange();
            },

            /**
             * attendance course on change
             */
            courseOnChange: function() {

                $( document ).on( 'change', '#exms-std-attendance-select-course', function() {

                    $( '.exms-std-attendance-ui' ).slideDown();
                } );
            },

            /**
             * attendance group on change
             */
            groupOnChange: function() {

                $( document ).on( 'change', '#exms-std-attendance-select-group', function() {

                    let groupID = $( '#exms-std-attendance-select-group' ).val();

                    let data = {
                        action     : 'attendance_group_on_change',
                        nonce      : EXMS_REPORT.security,
                        group_id   : groupID
                    };

                    $.ajax( {
                        url   : EXMS_REPORT.ajaxURL,
                        type  : 'POST',
                        data  : data,
                        dataType: 'json',
                        success: ( response ) => {
                            $( '#exms-std-attendance-select-course' ).html( response.content );
                        },
                    } );
                } );
            },
        };
        EXMS_ATTENDANCE_FRONTEND.init();
    });
})( jQuery );
