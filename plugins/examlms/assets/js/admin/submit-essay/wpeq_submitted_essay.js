( function( $ ) { 'use strict';

	$( document ).ready( function() {

		let WPeEssays = {
			
			init: function() {
				this.createTable();
				this.deleteSubmitEssay();
				this.editUpdateSubmitEssay();
				this.viewSubmitEssay();
				this.approveSubmitEssay();
				this.essayFiltersOnChange();
			},

			/**
             * Create table if not exist
             */
            createTable: function() {
                
                $( document ).on( 'click', '.create-tables-link', function( e ) {
                    e.preventDefault();
            
                    if ( !confirm( EXMS_SUBMIT_ESSAY.confirmation_text ) ) {
                        return; 
                    }
                    
                    let $this = $( this );
                    $this.prop( 'disabled', true ).text( EXMS_SUBMIT_ESSAY.processing );
        
                    let action = $( this ).data( 'action' );
                    let tables = $( this ).data( 'tables' );
            
                    $.ajax({
                        url: EXMS_SUBMIT_ESSAY.ajaxURL,
                        type: 'POST',
                        data: {
                            action: action,
                            tables: JSON.stringify( tables ),
                            nonce: EXMS_SUBMIT_ESSAY.create_table_nonce
                        },
                        success: function( response ) {
                            if ( response.success ) {
                                $( '.csm-para-content' ).html( response.data ).show().change();
                                $( '.csm-para' ).hide().change();
                            } else {
                                let message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
    							alert( message );
                                $this.prop( 'disabled', false ).text( EXMS_SUBMIT_ESSAY.create_table ).change();
                            }
                        }
                    });
                });
            },

			/**
			 * Change filter options to switch inputs
			 */
			essayFiltersOnChange: function() {

				if( $( '.exms-filter-options' ).length > 0 ) {

					$( '.exms-filter-options' ).on( 'change', function() {
						
						switch ( $( this ).val() ) {

							case 'user_id':
							$( '.exms-e-f-by-name' ).hide();
							$( '.exms-e-f-by-id' ).show();
							$( '.exms-e-f-by-q-id' ).hide();
							break;

							case  'user_name':
							$( '.exms-e-f-by-name' ).show();
							$( '.exms-e-f-by-id' ).hide();
							$( '.exms-e-f-by-q-id' ).hide();
							break;

							case  'quiz_id':
							$( '.exms-e-f-by-name' ).hide();
							$( '.exms-e-f-by-id' ).hide();
							$( '.exms-e-f-by-q-id' ).show();
							break;
						}
					} );
				}
			},

			/**
			 * Approved submit essay
			 */ 
			approveSubmitEssay: function() {

				if( $( '.exms-essay-approve' ).length > 0 ) {

					$( '.exms-essay-approve' ).on( 'click', function() {

						if( confirm( 'Are you sure you want to approved.?' ) ) {
							
							let self =  $(this);
							let essayId = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-essay-id' );
							let quizID = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-quiz-id' );
							let userID = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-user-id' );
							let points = parseInt( self.parents( 'tr' ).find( '.points' ).text() );

							let data = {
								'action'  		: 'exms_approve_essay_answer',
								'exms_essay_id'  : essayId,
								'exms_quiz_id'	: quizID,
								'exms_user_id'   : userID,
								'exms_points'	: points
							};

							jQuery.post( EXMS_SUBMIT_ESSAY.ajaxURL, data, function( response ) {
								location.reload();
							} );
						}
					} );				
				}
			},

			/**
			 * View Submitted essay
			 */ 
			viewSubmitEssay: function() {

				if( $( '.exms-views-essay' ).length > 0 ) {

					$( '.exms-views-essay' ).on( 'click', function() {

						let self =  $(this);
						let question = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-question' );
						let answer = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-essay-content' );

						$( '.exms-essay-ques' ).html( question );
						$( '.exms-essay-ans' ).html( answer );

					} );
				}
			},

			/**
			 * Edit/Update essay rows
			 */
			editUpdateSubmitEssay: function() {

				if( $( '.exms-edit-essay' ).length > 0 ) {

					$( '.exms-edit-essay' ).on( 'click', function() {

						let self =  $(this);
						let rowID = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-id' );
						let content = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-essay-content' );
						
						$( '.exms-essay-content' ).html( content );
						$( '.exms-essay-content' ).attr( 'data-row-id', rowID );
					} );
				}

				/**
				 * Update essay
				 */
				if( $( '.exms-update-essay' ).length > 0 ) {

					$( '.exms-update-essay' ).on( 'click', function() {

						let self = $(this);
						self.val( 'Processing...' );
						self.attr( 'disabled', 'disabled' );

						let rowID = $( '.exms-essay-content' ).attr( 'data-row-id' );
						let updateContent = $( '.exms-essay-content' ).val();

						let data = {
							'action'  			: 'exms_update_essay_rows',
							'exms_row_id'  		: rowID,
							'exms_content'		: updateContent
						};

						jQuery.post( EXMS_SUBMIT_ESSAY.ajaxURL, data, function( response ) {
							location.reload();
						} );
					} );
				}
			},

			/**
			 * Delete essays row
			 */
			deleteSubmitEssay: function() {

				/**
				 *  Delete single row
				 */
				if( $( '.exms-delete-essay' ).length > 0 ) {

					$( '.exms-delete-essay' ).on( 'click', function() {

						let self = $(this);
						let id = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-id' );
						let essayId = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-essay-id' );
						let userID = self.parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-user-id' );

						if( confirm( 'Are you sure you want to delete this row.?' ) ) {
							let data = {
								action 		: 'exms_delete_essay_rows',
								row_id 		: id,
								essay_id 	: essayId,
								user_id  	: userID
							};

							jQuery.post( EXMS_SUBMIT_ESSAY.ajaxURL, data, function( response ) {
								location.reload();
							});	
						}

					} );
				}

				/**
				 * Delete multiple rows
				 */
				if( $( '.exms-delete-multiple-essay' ).length > 0 ) {

					$( '.exms-delete-multiple-essay' ).on( 'click', function() {

						let selectedAction = $( '.exms-selected-rows' ).val();
						if( 'delete' != selectedAction ) {
							return false;
						} 

						if( confirm( 'Are you sure you want to delete this row.?' ) ) {
							
							let selectedEssayRows = $( '.exms-selected-essays' );
							let givenIDs = [];
							let essayIDs = [];
							let userID = '';
							$.each( selectedEssayRows, function( index, elem ) {
								
								if( $( elem ).prop( 'checked' ) ) {

									let selectedIDs = $( elem ).parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-id' );
									let essayID = $( elem ).parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-essay-id' );
									let user = $( elem ).parents( 'tr' ).find( '.exms-essay-user' ).attr( 'data-user-id' );
									givenIDs.push( selectedIDs );
									essayIDs.push( essayID );
									userID = user;
								}
							});
							
							let data = {
								action 				: 'exms_delete_essay_rows',
								exms_multiple_ids 	: givenIDs,
								exms_essay_ids		: essayIDs,
								exms_user_id			: parseInt( userID )
							};

							jQuery.post( EXMS_SUBMIT_ESSAY.ajaxURL, data, function( response ) {
								location.reload();
							});	
						}
					} );				
				}
			},
		}
		
		WPeEssays.init();
	});

})( jQuery );
