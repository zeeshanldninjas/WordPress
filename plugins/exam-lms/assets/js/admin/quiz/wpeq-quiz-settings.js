( function( $ ) { 'use strict';

	$( document ).ready( function() {

		let EXMS_settings = {

			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.disabledField();
				this.quizTimerField();
				this.createTable();
				this.toggleQuizPriceFields();
				this.toggleQuizPointField();
				this.toggleQuizAttemptField();
				this.toggleParentPriceFields();
				this.assignQuestionRelation();
				this.iconQuestion();
			},

			/**
			 * Disabled field when all question at once is selected
			 */
			disabledField: function() {
				$( document ).on( 'change', '.exms-question-display-option', function() {
					let displaySelect = $( '.exms-question-display-option' );
					let resultSelect = $( '.exms-question-result-summary' );
					let displayType = displaySelect.val();
					
					if ( displayType === 'exms_all_at_once' ) {
						resultSelect.val( 'summary_at_end' ).prop( 'disabled', true );
						$( '.exms-disbaled-message' ).show().change();
					} else {
						resultSelect.prop( 'disabled', false );
						$( '.exms-disbaled-message' ).hide().change();
					}
				});
			},

			quizTimerField: function() {

				$( '.wpeq_quiz_timer' ).on( 'change', function() {

					let selectedVal = $( '.wpeq_quiz_timer:checked' ).val();
					if ( selectedVal === 'on' ) {
						$( '.exms-quiz-timer-fields' ).slideDown().change();
					} else {
						$( '.exms-quiz-timer-fields' ).slideUp().change();
					}
				});
			},

			/**
             * Create table if not exist
             */
            createTable: function() {
                
                $( document ).on( 'click', '.create-tables-link', function( e ) {
                    e.preventDefault();
            
                    if ( !confirm( EXMS_QUIZ.confirmation_text ) ) {
                        return; 
                    }
                    
                    let $this = $( this );
                    $this.prop( 'disabled', true ).text( EXMS_QUIZ.processing );
        
                    let action = $( this ).data( 'action' );
                    let tables = $( this ).data( 'tables' );
            
                    $.ajax({
                        url: EXMS_QUIZ.ajaxURL,
                        type: 'POST',
                        data: {
                            action: action,
                            tables: JSON.stringify( tables ),
                            nonce: EXMS_QUIZ.create_table_nonce
                        },
                        success: function( response ) {
                            if ( response.success ) {
								$( '.exms-table-creation-message' ).removeClass( 'notice-error' ).addClass( 'notice-success');
                                $( '.exms-para-content' ).html( response.data ).show().change();
                                $( '.exms-para' ).hide().change();
                            } else {
                                let message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
    							alert( message );
                                $this.prop( 'disabled', false ).text( EXMS_QUIZ.create_table ).change();
                            }
                        }
                    });
                });
            },

			/**
			 * Toggle parent post purchase type fields
			 */
			toggleParentPriceFields: function() {

				$( 'body' ).on( 'click', '.exms_purchase_type', function() {

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
						
						default:
							$( '.exms-price-row, .exms-subs-row, .exms-close-row' ).removeClass( 'exms-show' );
							break;
					}

				} );
			},

			/**
			 * Toggle reattempt field
			 */
			toggleQuizAttemptField: function() {

				if( $( '.exms-reattempts-quiz-toggle' ).length > 0 ) {

					$( '.exms-reattempts-quiz-toggle' ).on( 'click', function() {
						
						switch( $( this ).val() ) {
							
							case 'yes':
								$( '.exms-reattempt-row' ).removeClass( 'exms-hide' );
								$( '.div_reattps_opts' ).removeClass( 'exms-hide' );
								$( '.exms-reattempt-row' ).addClass( 'exms-show' );
								$( '.div_reattps_opts' ).addClass( 'exms-show' );
								break;

							case 'no':
								$( '.exms-reattempt-quiz-option' ).hide();
								$( '.exms-x-extra-field' ).addClass('exms-hide');
								$( '.exms-reattempt-row' ).addClass('exms-hide');
								$( '.exms-reattempts-number' ).val( 0 );
								$( '.exms-reattempts-quiz-opts' ).prop( 'selectedIndex',0 );
								$( '.div_reattps_opts' ).removeClass( 'exms-show' );
								$( '.exms-reattempt-row' ).removeClass( 'exms-show' );
								break;
						}

					} );				
				}

				if( $( '.exms-reattempts-quiz-opts' ).length > 0 ) {

					$( '.exms-reattempts-quiz-opts' ).on( 'change', function() {

						let self = $( this );

						if( self.val() == 'select_x_options' ) {
							return false;
						}
						
						switch( self.val() ) {

							case 'x-days':
								$( '.exms-x-extra-field' ).removeClass('exms-hide');
								$( '.exms-x-extra-field' ).html( '<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>"><div class="exms-sub-title">Number of days</div><div class="exms-reattempt-value"><input name="exms_reattempt_type_value" type="number" placeholder="Number of days" class="exms_reattempt_type_value"></div></div>' );
								break;

							case 'x-hours':
								$( '.exms-x-extra-field' ).removeClass('exms-hide');
								$( '.exms-x-extra-field' ).html( '<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>"><div class="exms-sub-title">Number of hours</div><div class="exms-reattempt-value"><input name="exms_reattempt_type_value" type="number" placeholder="Number of hours" class="exms_reattempt_type_value"></div></div>' );
								break;

							case 'x-minutes':
								$( '.exms-x-extra-field' ).removeClass('exms-hide');
								$( '.exms-x-extra-field' ).html( '<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>"><div class="exms-sub-title">Number of minutes</div><div class="exms-reattempt-value"><input name="exms_reattempt_type_value" type="number" placeholder="Number of minutes" class="exms_reattempt_type_value"></div></div>' );
								break;

							case 'x-date':
								$( '.exms-x-extra-field' ).removeClass('exms-hide');
								$( '.exms-x-extra-field' ).html( '<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>"><div class="exms-sub-title">Select date</div><div class="exms-reattempt-value"><input name="exms_reattempt_type_value" type="date" placeholder="Select date" class="exms_reattempt_type_value"></div></div>' );
								break;
							
							default:
								break;
						}

					} );	
				}
			},

			/**
			 * Toggle quiz point field
			 */
			toggleQuizPointField: function() {

				if( $( '.exms_deduct_points' ).length > 0 ) {

					$( '.exms_deduct_points' ).on( 'change', function() {

						switch( $( this ).val() ) {

							case 'yes':

								$( '.p-deduct-on-failing, .p-deduct-on-wrg-answer' ).addClass( 'exms-show' );
								break;

							case 'no':
								$( '.exms_points_duducts_f' ).val( '' );
								$( '.p-deduct-on-failing, .p-deduct-on-wrg-answer' ).removeClass( 'exms-show' );
								break;
							
							default:

								$( '.p-deduct-on-failing, .p-deduct-on-wrg-answer' ).removeClass( 'exms-show' );
								break;
						}

					} );
				}

				/**
				 * Deduct point on failing quiz
				 */
				if( $( '.exms-point-deduct-failing' ).length > 0 ) {

					$( '.exms-point-deduct-failing' ).on( 'change', function() {

						let self = $( this );
						
						switch( self.val() ) {

							case 'on':

								$( '.exms-quiz-points-deduct-row' ).addClass( 'exms-show' );
								$( '.exms-quiz-points-deduct-row' ).removeClass( 'exms-hide' );
								break;

							case 'off':

								self.parents( '.p-deduct-on-failing' ).find('.exms-quiz-points-deduct-row').find( '.exms-points-dropdown' ).prop( 'selectedIndex',0 );

								$( '.exms-quiz-points-deduct-row' ).removeClass( 'exms-show' );
								$( '.exms_points_duducts_f' ).val( '' );
								break;
							
							default:

								$( '.exms-quiz-points-deduct-row' ).removeClass( 'exms-show' );
								break;
						}
						
					} );
				}

				/**
				 * Deduct point on submitting wrong answer
				 */
				if( $( '.exms-point-deduct-wrong-answer' ).length > 0 ) {

					$( '.exms-point-deduct-wrong-answer' ).on( 'change', function() {

						let self = $( this );
						switch( self.val() ) {

							case 'on':

								$( '.exms-quiz-points-wrong-answer-row' ).addClass( 'exms-show' );
								$( '.exms-quiz-points-wrong-answer-row' ).removeClass( 'exms-hide' );
								break;

							case 'off':

								self.parents( '.p-deduct-on-wrg-answer' ).find('.exms-quiz-points-wrong-answer-row').find( '.exms-points-dropdown' ).prop( 'selectedIndex',0 );

								$( '.exms-quiz-points-wrong-answer-row' ).removeClass( 'exms-show' );
								$( '.exms_points_duducts_wrng' ).val( '' );
								break;
							
							default:

								$( '.exms-quiz-points-wrong-answer-row' ).removeClass( 'exms-show' );
								break;
						}
						
					} );
				}

				/**
				 * select quiz/question points
				 */
				if( $( '.exms-quiz-points' ).length > 0 ) {

					$( '.exms-quiz-points' ).on( 'change', function() {

						switch( $( this ).val() ) {

							case 'quiz':

								$( '.exms-quiz-points-row' ).addClass( 'exms-show' );
								$( '.exms-quiz-points-row' ).removeClass( 'exms-hide' );

								$( '.exms-question-points-row' ).removeClass('exms-show');
								$( '.exms-quiz-points-rows' ).removeClass( 'exms-hide' );
								$( '.exms-question-points-row select option' ).val( '' );
								break;

							case 'question':
								$( '.exms-quiz-points-row' ).addClass( 'exms-hide' );
								$( '.exms-quiz-points-rows' ).addClass( 'exms-hide' );
								$( '.exms-quiz-points-row' ).removeClass( 'exms-show' );

								$( '.exms-question-points-row' ).addClass('exms-show');
								$( '.exms-quiz-points-row select option' ).val( '' );

								$( '.quiz' ).val( '' );
								break;
							
							default:

								$( '.exms-quiz-points-row' ).removeClass( 'exms-show' );
								break;
						}
						
					} );				
				}
			},

			/**
			 * Toggle price and subscriptions days field according to quiz type selection
			 */
			toggleQuizPriceFields: function() {

				$( 'body' ).on( 'click', '.wpeq_quiz_type', function() {

					switch( $( this ).val() ) {

						case 'paid':
							$( '.exms-quiz-price-row' ).addClass( 'exms-show' );
							$( '.exms-quiz-subs-row' ).removeClass( 'exms-show' );
							$( '.exms-quiz-close-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', false );
							break;

						case 'subscribe':
							$( '.exms-quiz-price-row, .exms-quiz-subs-row' ).addClass( 'exms-show' );
							$( '.exms-quiz-close-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', false );
							break;

						case 'close':
							$( '.exms-quiz-price-row, .exms-quiz-close-row' ).addClass( 'exms-show' );
							$( '.exms-quiz-price-row' ).removeClass( 'exms-show' );
							$( '.exms-quiz-subs-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', true );
							break;

						case 'free':
							$( '.exms-quiz-price-row' ).removeClass( 'exms-show' );
							$( '.exms-quiz-subs-row' ).removeClass( 'exms-show' );
							$( '.exms-quiz-close-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', true );
							break;
						
						default:
							$( '.exms-quiz-price-row, .exms-quiz-subs-row, .exms-quiz-close-row' ).removeClass( 'exms-show' );
							break;
					}
				} );
			},

			assignQuestionRelation: function () {
				$('.exms-dropdown-btn[data-target="question"]').on( 'click', function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( 'type' );
					let isAssigning = type === 'assigned';

					let parentBox = self.closest( '.exms-sortable-lists' );
					let postType = parentBox.find( '.exms-sortable-items-wrap' ).data( 'post-type' );

					let sourceWrap = parentBox.find( '.exms-sortable-items-wrap[data-post-type="' + postType + '"]' );

					let targetWrapSelector = isAssigning
						? '.exms-sortable-items-wrap-right[data-post-type="' + postType + '"]'
						: '.exms-sortable-items-wrap-left[data-post-type="' + postType + '"]';

					let items = sourceWrap.find( '.exms-sortable-item' );
					if ( items.length === 0 ) return;

					let targetWrap = $( targetWrapSelector );
					let existingTargetItems = targetWrap.find( '.exms-sortable-item' );

					if ( existingTargetItems.length === 0 ) {
						targetWrap.empty();
					}

					items.each( function () {
						let item = $( this );

						item.attr( 'data-name', isAssigning ? 'exms_assign_items' : 'exms_unassign_items' );
						item.attr( 'data-name-key', 'current' );

						let input = item.find( '.exms-assign-unassign-id' );
						input.attr( 'name', isAssigning ? 'exms_assign_items[current][]' : 'exms_unassign_items[current][]' );
						input.removeClass( 'exms-assign-current exms-unassign-current' )
							.addClass( isAssigning ? 'exms-assign-current' : 'exms-unassign-current' );

						let dragIcon = item.find( '.exms-drag-icon' );
						let postTitle = item.find( '.exms-post-title' );
						let postTitleAnchor = postTitle.find( 'a' );
						let imgParent = item.find( '.exms-img-parent' );
						let avatarImg = imgParent.find( 'img' );

						dragIcon.detach();
						if ( isAssigning ) {
							dragIcon.empty().html('&#8592;');
							postTitleAnchor.before( dragIcon );
						} else {
							dragIcon.empty().html('&#8594;');
							if ( imgParent.length ) {
								avatarImg.after( dragIcon );
							} else {
								postTitle.after( dragIcon );
							}
						}
						existingTargetItems = targetWrap.find( '.exms-sortable-item' );

						if ( existingTargetItems.length > 0 ) {
							existingTargetItems.last().after( item );
						} else {
							targetWrap.append( item );
						}
					} );

					sourceWrap.empty();

					let updatedPostIds = [];
					targetWrap.find( '.exms-assign-unassign-id' ).each( function () {
						updatedPostIds.push( $( this ).val() );
					} );

					if ( isAssigning ) {
						$( '.exms-dropdown-assign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data( 'users', updatedPostIds )
							.attr( 'data-users', JSON.stringify( updatedPostIds ) );

						$( '.exms-dropdown-unassign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					} else {
						$( '.exms-dropdown-unassign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data( 'users', updatedPostIds )
							.attr( 'data-users', JSON.stringify( updatedPostIds ) );

						$( '.exms-dropdown-assign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					}

					if ( isAssigning ) {
						$( '.exms-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).hide().change();
						$( '.exms-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).show().change();
					} else {
						$( '.exms-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).hide().change();
						$( '.exms-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).show().change();
					}
				} );
			},

			/**
			 * Click on drag icon to move to the otherside
			 */
			iconQuestion: function () {
				$( document ).on( 'click', '.exms-drag-icon[data-target="question"]', function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( 'name' );
					let isAssigning = type === 'exms_unassign_items';

					let itemField = self.closest( '.exms-sortable-item' );
					let parentBox = self.closest( '.exms-sortable-lists' );
					let postType = parentBox.find( '.exms-sortable-items-wrap' ).data( 'post-type' );

					let sourceSelector = isAssigning
						? '.exms-sortable-items-wrap-left[data-post-type="' + postType + '"]'
						: '.exms-sortable-items-wrap-right[data-post-type="' + postType + '"]';

					let targetSelector = isAssigning
						? '.exms-sortable-items-wrap-right[data-post-type="' + postType + '"]'
						: '.exms-sortable-items-wrap-left[data-post-type="' + postType + '"]';

					let sourceWrap = $( sourceSelector );
					let targetWrap = $( targetSelector );
					let existingTargetItems = targetWrap.find( '.exms-sortable-item' );

					itemField.attr( 'data-name', isAssigning ? 'exms_assign_items' : 'exms_unassign_items' );
					itemField.attr( 'data-name-key', 'current' );

					let iconButton = itemField.find( '.exms-drag-icon' );
					let postTitle = itemField.find( '.exms-post-title' );
					iconButton.detach();

					if ( isAssigning ) {
						iconButton.empty().html('&#8592;');
						postTitle.prepend( iconButton );
						iconButton.data( 'name', 'exms_assign_items' );
					} else {
						iconButton.empty().html('&#8594;');
						postTitle.after( iconButton );
						iconButton.data( 'name', 'exms_unassign_items' );
					}

					let input = itemField.find( '.exms-assign-unassign-id' );
					input.attr( 'name', isAssigning ? 'exms_assign_items[current][]' : 'exms_unassign_items[current][]' );
					input.removeClass( 'exms-assign-current exms-unassign-current' ).addClass( isAssigning ? 'exms-assign-current' : 'exms-unassign-current' );
					itemField.hide().change();

					if ( existingTargetItems.length === 0 ) {
						targetWrap.empty();
					}
					targetWrap.append( itemField );
					itemField.show().change();

					let updatedPostIds = [];
					targetWrap.find( '.exms-assign-unassign-id' ).each( function () {
						updatedPostIds.push( $(this).val() );
					});

					if ( isAssigning ) {
						$( '.exms-dropdown-assign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data( 'users', updatedPostIds )
							.attr( 'data-users', JSON.stringify( updatedPostIds ) );
						$( '.exms-dropdown-unassign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]')
							.data( 'users', [] )
							.attr( 'data-users', [] );
					} else {
						$( '.exms-dropdown-unassign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data('users', updatedPostIds )
							.attr('data-users', JSON.stringify(updatedPostIds));
						$( '.exms-dropdown-assign .exms-dropdown-btn[data-post-id="' + self.data( 'post-id' ) + '"]' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					}

					if ( isAssigning ) {
						$( '.exms-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).find( itemField ).hide().change();
						$( '.exms-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).show().change();
					} else {
						$( '.exms-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).find( itemField ).hide().change();
						$( '.exms-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).show().change();
					}
				});
			},
		}
		EXMS_settings.init();
	});
})( jQuery );