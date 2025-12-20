( function( $ ) { 'use strict';

	$( document ).ready( function() {

		let questionPostType = {

			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.disabledShuffle();
				this.createTable();
				this.radioBtnClickCorrect();
				this.checkBoxClickCorrect();
				this.removeAnsOnQuesTypeChange();
				this.addAnswer( this );
				this.removeAnswer();
				this.delAnswer();
				this.collapseAnswer();
				this.expandAnswer();
				this.closeQuestionPopup();
				this.updateQuestionPopup();
				this.reorderQuestionElements();
				this.assignQuizRelation();
				this.iconQuiz();
			},				

			disabledShuffle: function() {
				let questionTypeDropdown = $( '.wpeq-question-type-dropdown' );
				let shuffleLabels = $( '.shuffle-toggle-switch label' );
				let shuffleInputs = $( 'input[name="exms_shuffle"]' );
				let disabledTypes = [ 'fill_blank', 'file_upload', 'free_choice', 'essay', 'range', 'sorting_choice' ];

				let type = questionTypeDropdown.val();
				if ( disabledTypes.includes( type ) ) {
					shuffleLabels.addClass( 'disabled' );
					shuffleInputs.prop( 'disabled', false );
				} else {
					shuffleLabels.removeClass( 'disabled' );
					shuffleInputs.prop( 'disabled', false );
				}

				questionTypeDropdown.on( 'change', function() {
					let type = $( this ).val();
					if ( disabledTypes.includes( type ) ) {
						shuffleLabels.addClass( 'disabled' );
						shuffleInputs.prop( 'disabled', false );
					} else {
						shuffleLabels.removeClass( 'disabled' );
						shuffleInputs.prop( 'disabled', false );
					}
				});

				shuffleLabels.on( 'click', function(e) {
					if ( $( this ).hasClass( 'disabled' ) ) {
						e.preventDefault();
					}
				});
			},

			handlingFormSubmission: function() {

				$( '.exms-sortable-items-wrap' ).each( function () {

					let self = $( this );
			
					if ( self.hasClass( 'ui-sortable' ) ) {
						
						self.on( 'sortupdate sortreceive sortremove', function () {
							formModified = true;
						} );
					} else {
						self.on( 'DOMSubtreeModified input change', function () {
							formModified = true;
						} );
					}
				});
			
				$( document ).on( 'click', '.exms-assign-unassign-id, .exms-sortable-ui-link', function () {
					formModified = true;
				} );
			
				$( 'form' ).on( 'submit', function () {
					if ( !formModified ) {
						$( '[name^="exms_assign_items"]' ).remove();
					}
				});
			},

			/**
             * Create table if not exist
             */
            createTable: function() {
                
                $( document ).on( 'click', '.create-tables-link', function( e ) {
                    e.preventDefault();
            
                    if ( !confirm( EXMS_QUESTION.confirmation_text ) ) {
                        return; 
                    }
                    
                    let $this = $( this );
                    $this.prop( 'disabled', true ).text( EXMS_QUESTION.processing );
        
                    let action = $( this ).data( 'action' );
                    let tables = $( this ).data( 'tables' );
            
                    $.ajax({
                        url: EXMS_QUESTION.ajaxURL,
                        type: 'POST',
                        data: {
                            action: action,
                            tables: JSON.stringify( tables ),
                            nonce: EXMS_QUESTION.create_table_nonce
                        },
                        success: function( response ) {
                            if ( response.success ) {
								$( '.exms-table-creation-message' ).removeClass( 'notice-error' ).addClass( 'notice-success');
                                $( '.exms-para-content' ).html( response.data ).show().change();
                                $( '.exms-para' ).hide().change();
                            } else {
                                let message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
    							alert( message );
                                $this.prop( 'disabled', false ).text( EXMS_QUESTION.create_table ).change();
                            }
                        }
                    });
                });
            },

			/**
			 * Delete answer
			 */
			delAnswer: function() {

				$( 'body' ).on( 'click', '.wpeq-del-ans', function() {

					$( this ).parents( '.wpeq-answer-div' ).remove();
				} );
			},

			/**
			 * Collapse answer editor box
			 */
			collapseAnswer: function() {

				$( 'body' ).on( 'click', '.exms-q-collapse', function() {

					let self = $( this );
					let ansID = self.data( 'id' );

					if( $( '.exms-ans-' + ansID ).length > 0 ) {

						$( '.exms-ans-' + ansID ).hide();
						self.removeClass( [ 'exms-q-collapse', 'exms-q-collapse-'+ansID, 'dashicons-arrow-up-alt2' ] );
						self.addClass( [ 'exms-q-expand', 'exms-q-expand-'+ansID, 'dashicons-arrow-down-alt2' ] );
					}
				} );
			},

			/**
			 * Expand answer editor box
			 */
			expandAnswer: function() {

				$( 'body' ).on( 'click', '.exms-q-expand', function() {

					let self = $( this );
					let ansID = self.data( 'id' );

					if( $( '.exms-ans-' + ansID ).length > 0 ) {

						$( '.exms-ans-' + ansID ).show();
						self.removeClass( [ 'exms-q-expand', 'exms-q-expand-'+ansID, 'dashicons-arrow-down-alt2' ] );
						self.addClass( [ 'exms-q-collapse', 'exms-q-collapse-'+ansID, 'dashicons-arrow-up-alt2' ] );
					}
				} );
			},

			/**
			 * Update answer
			 */
			updateQuestionPopup: function() {

				$( 'body' ).on( 'click', '.wpeq-popup-update', function() {

					let id = localStorage.getItem( 'wpeq-editor-id' ),
						updatedCont = tinymce.editors['wpeq-ans-editor'].getContent();
						updatedCont2 = '';

					if( tinymce.editors['wpeq-ans-editor2'] !== undefined && $( '.wpeq-ans-cont2-' + id ).length > 0 ) {
						
						updatedCont2 = tinymce.editors['wpeq-ans-editor2'].getContent();
						$( '.wpeq-ans-cont2-' + id ).val( updatedCont2 );
					}

					$( '.wpeq-ans-cont-' + id ).val( updatedCont );
					$( '.wpeq-answer-editor-popup' ).css( 'display', 'none' );
				} );
			},

			/**
			 * Close answer popup 
			 */
			closeQuestionPopup: function() {

				$( 'body' ).on( 'click', '.wpeq-popup-close', function() {

					$( '.wpeq-answer-editor-popup' ).css( 'display', 'none' );
				} );
			},

			/**
			 * Reorder question answers elements
			 */
			reorderQuestionElements: function() {

				if( $( '.wpeq-draggable' ).length > 0 ) {

					let posX,posY;

					$( '.wpeq-draggable' ).draggable({
						start: function( event, ui ) {
							posX = event.clientX;
							posY = event.clientY;
						},

						stop: function( event, ui ) {
							event.target.style.left = '0px';
							event.target.style.top = '0px';
						}
					});

					$( '.wpeq-draggable' ).droppable({

						over: function( event, ui ) {
							$( event.target ).addClass( 'wpeq-answer-div-over' );
						},
							
						out: function( event, ui ) {
							$( event.target ).removeClass( 'wpeq-answer-div-over' );
						},

						drop: function( event, ui ) {
							$( event.target ).removeClass( 'wpeq-answer-div-over' );
							if( posY < event.clientY ) {
								$( ui.draggable ).insertAfter( event.target );
							} else if( posY > event.clientY ) {
								$( ui.draggable ).insertBefore( event.target );
							}
								
						}
					});
				}
			},

			/**
			 * Empty the answers metabox on question type change
			 */
			removeAnsOnQuesTypeChange: function() {

				$( 'body' ).on( 'change', '.wpeq-question-type-dropdown', function() {
					
					let self = $( this );
					
					if( $( '.wpeq-answer-div' ).length > 0 ) {
						$( '.wpeq-answer-div' ).remove();
					}

					if( $( '.wpeq-add-answer-btn' ).prop( 'disabled' ) ) {
						$( '.wpeq-add-answer-btn' ).prop( 'disabled', false );
						$( '.wpeq-info-bar' ).css( 'display', 'none' );
					}

					if( self.val() == 'fill_blank' ) {
						$( '.wpeq-add-answer-btn' ).text( 'Insert Blank' );
					} else {
						$( '.wpeq-add-answer-btn' ).text( 'Add New Answer' );
					}

					if( (self.val() != 'fill_blank' || self.val() == 'fill_blank') && self.val() != 'essay' && self.val() != 'file_upload' ) {
						$( '.exms-instruction-message' ).remove();
					}
					
					else if( self.val() == 'essay' ) {
						$( '.wpeq-add-answer-btn' ).prop( 'disabled', true );
						$( '.exms-instruction-message' ).remove();
						let message = ` 
							<div class="exms-instruction-message" >
								<div class="exms-instruction-icon"> 
									<span class="dashicons dashicons-info-outline"></span>
								</div>
           						<div class="exms-instruction-text"> 
           							<p>The box will appear when attempting this question for writing an essay.</p> 
								</div>
						</div>`;
						let parent_div     = $( this ).closest( '.exms-question-settings-row' );
						parent_div.find( '.wpeq-add-answer-btn' ).before( message );
					}
					else if( self.val() == 'file_upload' ) {
						$( '.wpeq-add-answer-btn' ).prop( 'disabled', true );
						$( '.exms-instruction-message' ).remove();
						let message = ` 
							<div class="exms-instruction-message" >
								<div class="exms-instruction-icon"> 
									<span class="dashicons dashicons-info-outline"></span>
								</div>
           						<div class="exms-instruction-text"> 
           							<p>This question type does not support any answers.</p> 
								</div>
						</div>`;
						let parent_div     = $( this ).closest( '.exms-question-settings-row' );
						parent_div.find( '.wpeq-add-answer-btn' ).before( message );
					}
				});
			},

			/**
			 * Initialize wp editor 
			 */
			initializeWpEditor: function( elementID ) {

				wp.editor.initialize( elementID, { 
					mediaButtons: true,
				    tinymce: true,
				    quicktags: true
				});
			},

			/**
			 * Add answer to answer's metabox
			 */
			addAnswer: function( self ) {

				let count = $( '.exms-ans-count' ).length > 0 ? $( '.exms-ans-count' ).val() : 0;
				
				$( 'body' ).on( 'click', '.wpeq-add-answer-btn', function( e ) {
					
					e.preventDefault();

					let parent_div     = $(this).closest( '.exms-question-settings-row' );
					let parent_answer  = parent_div.find( '.wpeq-answer-div' );
					let ques_type      = parent_div.find( '.wpeq-question-type-dropdown' ).val();
					let rand           = Math.floor( Math.random() * 100000 );
					let count          = parent_div.find( '.exms-textarea-ans' ).length;

					if ( ques_type === 'single_choice' || ques_type === 'multiple_choice' || ques_type === 'sorting_choice' ) {

						let type = ques_type === 'single_choice' ? 'radio' : 'checkbox';
						let typeInput = `<input type="${type}" class="wpeq-custom-radio wpeq-ques-ans-${type}" name="exms_ques_ans_radio[]">`;
						if ( ques_type === 'sorting_choice' ) {
							typeInput = '';
						}
						let answerRow = `
							<div class="wpeq-answer-row exms-ans-${rand} exms-get-value wpeq-draggable" data-id="${rand}">
								<input type="hidden" class="wpeq-ques-ans-type" name="wpeq_ques_ans_type[]" value="wrong">
								<span class="wpeq-drag-icon">⋮⋮</span>
								<textarea class="exms-textarea-ans" name="exms_answers[${count}]" id="exms_answers${count}" rows="1" cols="50"></textarea>
								${ques_type !== 'sorting_choice' ? `
									<div class="wpeq-radio-wrapper">
									<label class="wpeq-radio-label">
									${typeInput}
									<span class="wpeq-radio-style"></span>
									</label>
									</div>
									` : ''}
								<span class="exms-sorting-delete dashicons dashicons-trash"></span>
							</div>
						`;
						if ( parent_answer.length > 0 ) {
							parent_answer.append( answerRow );
						} else {
							let correct_heading = "";
							if( ques_type != 'sorting_choice' ) {
								correct_heading = '<span>Correct Answer</span>';
							}
							let fullBlock = `
								<div class="wpeq-answer-div wpeq-ans-div${rand}">
									<div class="exms-answers-heading">
										<span>Answers</span>
										${correct_heading}
									</div>
									${answerRow}
								</div>
							`;
							parent_div.find( '.wpeq-add-answer-btn' ).closest( '.exms-answers-container' ).before( fullBlock );
						}

						questionPostType.initializeWpEditor( 'exms-answer-' + rand );
						count++;
					}

					/**
					 * Add fields for free choice type question 
					 */
					else if( ques_type == 'free_choice' ) {

						let answerRow = `
						<div class="wpeq-answer-div" style="border: none;">
							<textarea class="exms-free-choice-ans" name="exms_answers" rows="10" cols="50" placeholder="Answer here"></textarea>
						</div>
							<div class="exms-instruction-message" >
								<div class="exms-instruction-icon"> 
									<span class="dashicons dashicons-info-outline"></span>
								</div>
           						<div class="exms-instruction-text"> 
									<p><b>How to use the area?</b></p>
           							<p>Correct answers (one per line) (answers will be converted to lower case). If mode "Different points for each answer" is activated, you can assign points to each answer using "|". Example: One|15. The default point value is 1.</p> 
								</div>
						</div>`
						parent_div.find( '.wpeq-add-answer-btn' ).before( answerRow );
						$( this ).prop( 'disabled', true );
					}

					/**
					 * Add fields for matrix type question 
					 */
					else if( ques_type == 'matrix_sorting' ) {
						
						let count2 = count + 1;

						$( parent_div ).append( '<div class="wpeq-answer-div">' +
								'<input type="hidden" class="wpeq-ques-ans-type" name="wpeq_ques_ans_type[]" value="wrong" />' +
								'<span class="dashicons dashicons-arrow-up-alt2 exms-q-collapse exms-q-collapse-'+rand+' exms-icon" data-id="'+rand+'"></span>'+
								'<span class="dashicons dashicons-no wpeq-del-ans exms-icon" data-id="'+rand+'"></span>'+
								'<div class="exms-ans-'+rand+'">'+
									'<label class="exms-matrix-ans-label">Criteria 1</label>'+
									'<textarea id="exms-answer-'+rand+'" name="exms_answers[]" class="exms-answer-editor"></textarea>'
+
									'<label class="exms-matrix-ans-label">Criteria 2</label>'+
									'<textarea id="exms-answer-2-'+rand+'" name="exms_answers_'+count2+'" class="exms-answer-editor"></textarea>'+
								'</div>'+
							'<div>'
						);
						questionPostType.initializeWpEditor( 'exms-answer-' + rand );
						questionPostType.initializeWpEditor( 'exms-answer-2-' + rand );
						count += 2;
					}

					/**
					 * Add fields for range type question 
					 */
					else if( ques_type == 'range' ) {

						let answerRow = `<div class="wpeq-answer-div" style="border: none;">
								<div class="wpeq-range-input-title-div">Min Value</div>
								<div class="exms-q-row"><input type="number" name="exms_answers[min]" class="wpeq-range-input-fields" placeholder="min value" /></div>
								<div class="wpeq-range-input-title-div">Max Value</div>
								<div class="exms-q-row"><input type="number" name="exms_answers[max]" class="wpeq-range-input-fields" placeholder="max value" /></div>
								<div class="wpeq-range-input-title-div">Enter value including any valid format ex: value1 - value2.</div>
								<div class="exms-q-row"><input type="number" name="exms_answers[correct]" class="wpeq-range-input-fields" placeholder="value1 - value2" /></div>
							<div>`;
						parent_div.find( '.wpeq-add-answer-btn' ).before( answerRow );
						$( this ).prop( 'disabled', true );
					}

					else if( ques_type == 'fill_blank' ) {

						let answerRow = `
							<div class="wpeq-answer-div wpeq-ans-div${rand}">
								<div class="exms-answers-heading">
									<span>Add question with the {Blank}</span>
								</div>
								<div class="wpeq-answer-row exms-ans-${rand} exms-get-value" data-id="${rand}">
									<input type="hidden" class="wpeq-ques-ans-type" name="wpeq_ques_ans_type[]" value="wrong">
									<textarea class="exms-textarea-ans" name="exms_answers[${count}]" id="exms_answers${count}" rows="1" cols="50"></textarea>
								</div>
							</div>
							<div class="exms-instruction-message" >
								<div class="exms-instruction-icon"> 
									<span class="dashicons dashicons-info-outline"></span>
								</div>
           						<div class="exms-instruction-text"> 
									<p><b>How to use blanks?</b></p>
           							<p>just write the word inside curly brackets like this: {Example}</p> 
								</div>
							</div>
						`;
						parent_div.find( '.wpeq-add-answer-btn' ).before( answerRow );
						$( this ).prop( 'disabled', true );
					}
					questionPostType.reorderQuestionElements();
				});
			},

			removeAnswer: function() {
				$( 'body' ).on( 'click', '.exms-sorting-delete', function( e ) {
					e.preventDefault();

					let $row = $( this ).closest( '.wpeq-answer-row' );

					$row.stop(true, true).slideUp(200, function() {
						$row.remove();
					});
				});
			},

			/**
			 * Save values for checked checkboxes
			 */
			checkBoxClickCorrect: function() {

				$( 'body' ).on( 'click', '.wpeq-ques-ans-checkbox', function() {

					let self = $(this);
					let parentRow = self.closest( '.wpeq-answer-row' );
					let rowId = parentRow.data('id');

					if (typeof rowId !== 'undefined') {
						let inputHidden = $('.wpeq-answer-row[data-id="' + rowId + '"]').find('.wpeq-ques-ans-type');

						if (self.prop('checked')) {
							inputHidden.val('correct');
						} else {
							inputHidden.val('wrong');
						}
					}
				});
			},

			/**
			 * Save value for selected radio button
			 */
			radioBtnClickCorrect: function() {

				$( 'body' ).on( 'click', '.wpeq-ques-ans-radio', function() {

					let ques_type = $( '.wpeq-question-type-dropdown' ).val();
					let self = $( this );

					if ( ques_type !== 'multiple_choice' ) {
						let parentRow = self.closest( '.wpeq-answer-row' );
						let rowId = parentRow.data( 'id' );

						if (typeof rowId !== 'undefined') {
							let answerDiv = self.closest( '.wpeq-answer-div' );

							answerDiv.find( '.wpeq-ques-ans-type' ).val( 'wrong' );

							answerDiv.find( `.wpeq-answer-row[data-id="${rowId}"]` ).find( '.wpeq-ques-ans-type' ).val( 'correct' );
						}
					}

					/**
					 * If question type is multiple
					 */

					if( ques_type == 'multiple_choice' ) {

						if( self.prop( 'checked' ) ) {

							self.parents( '.wpeq-answer-div' ).find( '.wpeq-ques-ans-type' ).val( 'correct' );

						}else if( ! self.prop( 'checked' ) ) {

							self.parents( '.wpeq-answer-div' ).find( '.wpeq-ques-ans-type' ).val( 'wrong' );
						}
					}
				});
			},

			assignQuizRelation: function () {
				$('.exms-dropdown-btn[data-target="quizzes"]').on( 'click', function ( e ) {
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
						item.attr( 'data-name-key', 'parent' );

						let input = item.find( '.exms-assign-unassign-id' );
						input.attr( 'name', isAssigning ? 'exms_assign_items[parent][]' : 'exms_unassign_items[parent][]' );
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
			iconQuiz: function () {
				$( document ).on( 'click', '.exms-drag-icon[data-target="quizzes"]', function ( e ) {
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
					itemField.attr( 'data-name-key', 'parent' );

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
					input.attr( 'name', isAssigning ? 'exms_assign_items[parent][]' : 'exms_unassign_items[parent][]' );
					input.removeClass( 'exms-assign-parent exms-unassign-parent' ).addClass( isAssigning ? 'exms-assign-parent' : 'exms-unassign-parent' );
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
		};

		questionPostType.init();
	} );

})( jQuery );