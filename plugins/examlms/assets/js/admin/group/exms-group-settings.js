( function( $ ) { 'use strict';

	$( document ).ready( function() {

		let EXMS_group_settings = {

			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.createTable();
                this.groupType();
			},

            groupType: function() {
                $( 'body' ).on( 'click', '.exms_group_type', function() {

					switch( $( this ).val() ) {

						case 'paid':
							$( '.exms-price-row' ).addClass( 'exms-show' );
							$( '.exms-subs-row' ).removeClass( 'exms-show' );
							$( '.exms-close-row' ).removeClass( 'exms-show' );
							break;

						case 'subscribe':
							$( '.exms-price-row, .exms-subs-row' ).addClass( 'exms-show' );
							$( '.exms-close-row' ).removeClass( 'exms-show' );
							break;

						case 'close':
							$( '.exms-price-row, .exms-close-row' ).addClass( 'exms-show' );
							$( '.exms-price-row' ).removeClass( 'exms-show' );
							$( '.exms-subs-row' ).removeClass( 'exms-show' );
							break;

						case 'free':
							$( '.exms-price-row' ).removeClass( 'exms-show' );
							$( '.exms-subs-row' ).removeClass( 'exms-show' );
							$( '.exms-close-row' ).removeClass( 'exms-show' );
							break;
						
						default:
							$( '.exms-price-row, .exms-subs-row, .exms-close-row' ).removeClass( 'exms-show' );
							break;
					}
				} );
            },

			/**
             * Create table if not exist
             */
            createTable: function() {
                
                $( document ).on( 'click', '.create-tables-link', function( e ) {
                    e.preventDefault();
            
                    if ( !confirm( EXMS_GROUP.confirmation_text ) ) {
                        return; 
                    }
                    
                    let $this = $( this );
                    $this.prop( 'disabled', true ).text( EXMS_GROUP.processing );
        
                    let action = $( this ).data( 'action' );
                    let tables = $( this ).data( 'tables' );
            
                    $.ajax({
                        url: EXMS_GROUP.ajaxURL,
                        type: 'POST',
                        data: {
                            action: action,
                            tables: JSON.stringify( tables ),
                            nonce: EXMS_GROUP.create_table_nonce
                        },
                        success: function( response ) {
                            if ( response.success ) {
                                $( '.exms-table-creation-message' ).removeClass( 'notice-error' ).addClass( 'notice-success');
                                $( '.exms-para-content' ).html( response.data ).show().change();
                                $( '.exms-para' ).hide().change();
                            } else {
                                let message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
    							alert( message );
                                $this.prop( 'disabled', false ).text( EXMS_GROUP.create_table ).change();
                            }
                        }
                    });
                });
            },
		}
		EXMS_group_settings.init();
	});
})( jQuery );