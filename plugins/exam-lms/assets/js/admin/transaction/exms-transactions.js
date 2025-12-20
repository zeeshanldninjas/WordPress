( function( $ ) { 'use strict'; 
	$( document ).ready( function() {
		
		let EXMSTransaction = {
			init: function() {
				
				this.createTable();
				this.deleteSingleTransaction();
				this.deleteMultipleTransactions()
			},

			/**
             * Create table if not exist
             */
            createTable: function() {
                
                $( document ).on( 'click', '.create-tables-link', function( e ) {
                    e.preventDefault();
            
                    if ( !confirm( EXMS_TRANSACTION.confirmation_text ) ) {
                        return; 
                    }
                    
                    let $this = $( this );
                    $this.prop( 'disabled', true ).text( EXMS_TRANSACTION.processing );
        
                    let action = $( this ).data( 'action' );
                    let tables = $( this ).data( 'tables' );
            
                    $.ajax({
                        url: EXMS_TRANSACTION.ajaxURL,
                        type: 'POST',
                        data: {
                            action: action,
                            tables: JSON.stringify( tables ),
                            nonce: EXMS_TRANSACTION.create_table_nonce
                        },
                        success: function( response ) {
                            if ( response.success ) {
                                $( '.csm-para-content' ).html( response.data ).show().change();
                                $( '.csm-para' ).hide().change();
                            } else {
                                let message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
    							alert( message );
                                $this.prop( 'disabled', false ).text( EXMS_TRANSACTION.create_table ).change();
                            }
                        }
                    });
                });
            },

			/**
			 * Delete multiple transactions
			 */
			deleteMultipleTransactions: function() {

				$( 'body' ).on( 'click', '#doaction', function() {

					let self = $( this );
					let parent = self.parent( '.bulkactions' );
					let actionVal = parent.find( '#bulk-action-selector-top' ).val();

					if( actionVal != 'delete' ) {
						alert( 'Please select delete option to perform bulk action' );
						return false;
					}

					$.confirm({
                        title: false,
                        content: 'Are you sure you want to delete these transactions?',
                        buttons: {
                            Yes: function () {

                            	let CheckedId = [];
								$.each( $( '.exms-selected-transaction' ), function(index, elem) {

									if( $( elem ).prop( 'checked' ) ) {

										let bulkID = $( elem ).parents( 'tr' ).find( '.exms-transaction-id' ).attr( 'data-row-id' );
										CheckedId.push( bulkID );
									}
								} );
								
								let data = {
									'action'  	: 'exms_delete_multiple_transactions',
									'security'	: EXMS_TRANSACTION.security,
									'row_ids'	:  CheckedId,
								};

								jQuery.post( EXMS_TRANSACTION.ajaxURL, data, function( resp ) {

			                        let response = JSON.parse( resp );
			                        if( response.status == 'false' ) {
			                        	$.alert( response.message );
			                        } else {
			                        	$.alert( 'Transactions Deleted Successfully' );
			                        	var url = document.location.href+"&message=updated";
      									document.location = url;
			                        }
			                    } );
                            },
                            No: function () {},
                        }
                    } );
				} );
			},

			/**
			 * Delete single Transaction
			 */
			deleteSingleTransaction: function() {

				$( 'body' ).on( 'click', '.exms-delete-transaction', function() {

					let self = $( this );
					let parent = self.parents( 'tr' );
					let rowID = parent.find( '.exms-transaction-id' ).attr( 'data-row-id' );

					let data = {
						'action' 	: 'exms_delete_single_transaction',
						'security'	: EXMS_TRANSACTION.security,
						'row_id'	: rowID
					};

					$.confirm({
                        title: false,
                        content: 'Are you sure you want to delete this transaction?',
                        buttons: {
                            Yes: function () {

                                jQuery.post( EXMS_TRANSACTION.ajaxURL, data, function( resp ) {

			                        let response = JSON.parse( resp );
			                        if( response.status == 'false' ) {
			                        	$.alert( response.message );
			                        } else {
			                        	$.alert( 'Transaction Deleted Successfully' );
			                        	var url = document.location.href+"&message=updated";
      									document.location = url;
			                        }
			                    } );
                            },
                            No: function () {},
                        }
                    } );
				} );
			},
		}

		EXMSTransaction.init();
	});
} )( jQuery );