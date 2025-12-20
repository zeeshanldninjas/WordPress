( function( $ ) { 'use strict';

	$( document ).ready( function() {
		
		let EXMS_Theme = {
			
			init: function() {
				console.log(EXMS.answer_summary);
				console.log(EXMS.result_summary);
				this.submitQuizAnswer();
				this.showNextQuestion();
				this.draggableSortingTypeAnswers();
				this.setMatrixAnswerOnTable();
				this.showRangeInputValue();
				this.startTheQuiz();
				this.hintsForQuizAnswers();
				this.addRangeSlider();
				this.expandCollapePostItems();
				this.instructorTablePaginations();
				this.unenrollAssignQuizUsers();
				this.exms_payment_tabs_switch();
			},
			/**
			 * Expand or collape post items
			 */
			exms_payment_tabs_switch: function() {
				$(".payment-button-container").click(function (){
                    $(".exms-pop-outer").fadeIn("slow");
                });
				$( 'body' ).on( 'click', '.exms-payment-tablinks', function() {
					$('.exms-payment-tabcontent').css('display', 'none');
					$( '#' + $( this ).data( 'id' ) ).css('display', 'block');
				});
			},
			/**
			 * Expand or collape post items
			 */
			expandCollapePostItems: function() {

				$( 'body' ).on( 'click', '.exms-expand-post', function() {

					let self = $( this );
					let parent = self.parent( '.exms-post-link-wrap' );

					if( self.find( '.exms-expand-icon' ).hasClass( 'dashicons-arrow-down' ) ) {
						self.find( '.exms-expand-icon' ).removeClass( 'dashicons-arrow-down' );
						self.find( '.exms-expand-icon' ).addClass( 'dashicons-arrow-up' );
						self.find( '.exms-expand-text' ).html( 'Collapse' );

					} else {
						self.find( '.exms-expand-icon' ).removeClass( 'dashicons-arrow-up' );
						self.find( '.exms-expand-icon' ).addClass( 'dashicons-arrow-down' );
						self.find( '.exms-expand-text' ).html( 'Expand' );
					}

					parent.find( '.exms-child-post-list-counts' ).css( 'padding-bottom', '10px' );
					parent.siblings( '.exms-display-content' ).slideToggle();
					parent.siblings( '.exms-progress-structures' ).slideToggle();

				} );
			},

			/**
			 * Add range slider for range type answer
			 */
			addRangeSlider: function() {

				let rangeSelect = $( '#exms-slider-range' );
				let minValue = parseInt( $( rangeSelect ).attr( 'data-min-value' ) );
				let maxValue = parseInt( $( rangeSelect ).attr( 'data-max-value' ) );

				$( rangeSelect ).slider({
				    range: true,
				    min: minValue,
				    max: maxValue,
				    values: [ minValue, maxValue ],
				    slide: function( event, ui ) {
				        $( '#exms-range-number' ).val( ui.values[ 0 ] + ' - ' + ui.values[ 1 ] );
				    }
			    });
			    $( '#exms-range-number' ).val( $( '#exms-slider-range' ).slider( 'values', 0 ) + ' - ' + $( '#exms-slider-range' ).slider( 'values', 1 ) );
			},

			/**
			 * Display hints for quiz answer 
			 */
			hintsForQuizAnswers: function() {

				if( $( '.exms-answer-hint' ).length > 0 ) {

					$( '.exms-answer-hint' ).on( 'click', function() {

						$(this).parents( '.exms-hint-main' ).find( '.exms-hint' ).toggle( 'slide', { direction: 'left' }, 500 );
					} );
				}
			},

			data : [],

			/**
		 	 * Initialize quiz timer
			 */
			initializeTimer: function( timerElem, type, timerText ) {

				let timerElement = $( timerElem );

				if( timerElem.length > 0 ) {

					let self = this;
					let id = timerElem.data( 'id' );
					let time = timerElem.data( 'time' );
					let splittedTime = ! self.data['quiz-'+id] ? time.split( ':' ) : self.data['quiz-'+id].split( ':' );
					let hours = splittedTime[0] ? Number( splittedTime[0] ) : 0;
					let mins = splittedTime[1] ? Number( splittedTime[1] ) : 0;
					let secs = splittedTime[2] ? Number( splittedTime[2] ) : 0;
					let dHours = 0;
					let dMins = 0;
					let dSecs = 0;
					let totalSecs = ( ( ( hours * 60 ) + mins ) * 60 ) + secs;

					if( hours <= 0 && mins <= 0 && secs <= 0 ) {

						return false;
					} 

					let x = setInterval( function() {

						if( secs <= 0 && ( mins > 0 || hours > 0 ) ) {

							secs = 59;

							if( mins > 0 ) {

								mins--;
							
							} else if( mins <= 0 && hours > 0 ) {

								mins = 59;
								hours--;
							}

						} else if( secs > 0 ) {

							secs--;
							self.data['quiz-sub-secs-'+id] = self.data['quiz-sub-secs-'+id] ? self.data['quiz-sub-secs-'+id] + 1 : 1;
						}

						dHours = hours < 10 ? '0' + hours : hours;
						dMins = mins < 10 ? '0' + mins : mins;
						dSecs = secs < 10 ? '0' + secs : secs;

						self.data['quiz-'+id] = hours+':'+mins+':'+secs;

						let timerWidth = ( self.data['quiz-sub-secs-'+id] / totalSecs ) * 100;
						let totalPer = 100 - timerWidth;
						// timerElem.html( dHours + ':' + dMins + ':' + dSecs );
						timerText.html( dHours + ':' + dMins + ':' + dSecs );
						timerElem.css( 'width', totalPer+'%' );

						// if( totalPer < 40 ) {
						// 	timerElem.css( 'backgroundColor', 'rgba(230, 81, 0, 0.4)' );
						// 	timerElem.parent( '.exms-timer-cover' ).css( 'background', '#ff9a81' );

						// } 
						if( totalPer <= 25 ) {
							timerElem.css( 'backgroundColor', '#DB4B40' );
							timerElem.parent( '.exms-timer-cover' ).css( 'background', '#ea7f7f' );
						}

						if( secs <= 0 && mins <= 0 && hours <= 0 ) {

							clearInterval( x );
							let submitBtn = $( '.exms-sub-btn-' + id );

							if( 'quiz' == type ) {

								submitBtn.addClass( 'exms-finish-quiz' );
							
								if( self.data.length <= 0 ) {

									self.data = true;								
								}
								self.finishQuiz( self, submitBtn );	

							} else if( 'question' == type ) {

								let currentQues = $( '.exms-active-ques' );
								let NextQuestionIndex = ( Number ) ( currentQues.data( 'index' ) + 1 );

								if( $( '.exms-ques-' + NextQuestionIndex ).length > 0 ) {

									$( '.exms-next-ques' ).trigger( 'click' );

								} else if( ! $( '.exms-ques-' + NextQuestionIndex ).length > 0 ) {

									if( self.data.length <= 0 ) {

										self.data = true;								
									}
									$( '.exms-next-ques' ).addClass( 'exms-finish-quiz' );
									self.finishQuiz( self, $( '.exms-next-ques' ) );	
								}
							}
						}

					}, 1000 );

					timerElem.attr( 'data-interval-id', x );
					self.data['quiz-interval-'+id] = x;
				}
			},

			/**
			 * Run quiz timer
			 */
			startTheQuiz: function() {

				/**
				 * checked input type radio
				 */
				$( 'body' ).on( 'click', '.exms-answer-row', function() {
						
					let self = $( this );

					if( self.children().is( ":checked" ) ) {
						self.children().prop( "checked", false );
					} else {
						self.children().prop( "checked", true );
					}
				} );	

				if( $( '.exms-str-quiz' ).length > 0 ) {

					$( '.exms-str-quiz' ).on( 'click', function() {

						let self = $( this );
						let id = self.data( 'id' );
						$( '.exms-quiz-box-'+id ).show();
						self.hide();

						let currentQues = $( '.exms-active-ques' );
						let quesID = currentQues.data( 'id' );

						if( $( '.exms-timer-' + quesID ).length > 0 ) {

							EXMS_Theme.initializeTimer( $( '.exms-timer-' + quesID ), 'question', $( '.exms-timer-text-' + quesID ) );
						
						} else if( $( '.exms-timer-' + id ).length > 0 ) {

							EXMS_Theme.initializeTimer( $( '.exms-timer-' + id ), 'quiz', $( '.exms-timer-text-' + id ) );
						}
					} );
				}
			},

			/**
			 * added load more functionality in Instrcutor shortcode
			 */
			instructorTablePaginations: function() {

				$( 'body' ).on( 'click', '.exms-ins-paginate', function() {

					$( '.exms-inst-loader' ).show();
					let self = $( this );
					let parent = self.parents( '.exms-ins-paginations' );
					let target = self.attr( 'data-target' );
					let page = parseInt( parent.attr( 'data-page' ) );
					let limit = parseInt( parent.attr( 'data-limit' ) );
					let currentPage = parseInt( parent.find( '.exms-inst-current-page' ).text() );
					let totalPage = parseInt( parent.find( '.exms-inst-total-page' ).text() );

					let pageCount = '';
                	if( 'back' == target ) {
                		pageCount = page - limit;
                		currentPage = totalPage ? currentPage - 1 : '';

                	} else if( 'next' == target ) {
                		pageCount = page + limit;
                		currentPage = totalPage && totalPage > currentPage ? currentPage + 1 : '';
                	}

					let data = {
						'action' 	: 'exms_ins_paginations',
						'security'	: EXMS.security,
						'page'		: pageCount,
						'limit'		: limit,
						'target'	: target
					};

					jQuery.post( EXMS.ajaxURL, data, function( resp ) {

                        let response = JSON.parse( resp );
                        if( response.status == 'false' ) {
                        	$.alert( response.message );
                        } else {
                        	
                        	parent.attr( 'data-page', pageCount );
                        	parent.find( '.exms-inst-current-page' ).text( currentPage );

                        	if( response.content ) {
                        		$( '.exms-inst-table-body' ).html( response.content );
                        		
                        		if( 'back' == target ) {
                        			parent.find( '.exms-ins-next-btn' ).css( 'visibility', 'visible' );
                        		} else if( 'next' == target ) {
                        			parent.find( '.exms-ins-back-btn' ).css( 'visibility', 'visible' );
                        		}
                        	}

                        	if( 'back' == target && pageCount == 0 ) {
		                		parent.find( '.exms-ins-back-btn' ).css( 'visibility', 'hidden' );
		                	}

		                	if( 'next' == target && currentPage == totalPage ) {
		                		parent.find( '.exms-ins-next-btn' ).css( 'visibility', 'hidden' );
		                	}

		                	$( '.exms-inst-loader' ).hide();
                        }
                    } );
				} );
			},

			/**
			 * Unenroll user to quiz 
			 */
			unenrollAssignQuizUsers: function() {

				$( 'body' ).on( 'click', '.exms-inst-unenroll', function() {

					let self = $( this );
					let quizID = parseInt( self.attr( 'data-quiz-id' ) );
					let userID = parseInt( self.attr( 'data-user-id' ) );

					$.confirm({
                        title: false,
                        content: 'Are you sure you want to Unenroll?',
                        buttons: {
                            Yes: function () {

                            	let data = {
									'action' 	: 'exms_unassing_user_to_quiz',
									'security'	: EXMS.security,
									'quiz_id'	: quizID,
									'user_id'	: userID
								};

								jQuery.post( EXMS.ajaxURL, data, function( resp ) {

			                        let response = JSON.parse( resp );
			                        if( response.status == 'false' ) {
			                        	$.alert( response.message );
			                        } else {
			                        	$.alert( 'Successfully Unenrolled Student to this quiz' );
			                        	location.reload( true );
			                        }
			                    } );
                            },
                            No: function () {},
                        }
                    } );
				} );
			},

			/**
			 * Finish quiz
			 */
			finishQuiz: function( self, btn ) {

				if( $( '.exms-finish-quiz' ).length > 0 && self.data ) {
					
					$( '.exms-timer-main' ).hide();
					$( '.exms-quiz-loader' ).hide();
					let res = localStorage.getItem( 'quizResults' );

					let parentPostID = 0;
					let results = new RegExp( '[\?&]' + 'parent_posts' + '=([^&#]*)' ).exec( window.location.href );
			        if( results ) {
			        	parentPostID = decodeURI( results[1] ) || 0;
			        }

					let data = {
						'action'		: 'exms_quiz_completed',
						'data'			: self.data,
						'quiz_id'		: btn.data( 'quizid' ),
						'parent_posts'	: parentPostID
					};
					let quizBox = btn.parents( '.exms-quiz-box' );
					let btnRandID = btn.data('id');

					if( $( '.exms-timer-' + btnRandID ).length > 0 && self.data['quiz-interval-' + btnRandID] ) {
						
						clearInterval( self.data['quiz-interval-' + btnRandID] );
					}

					$.post( EXMS.ajaxURL, data, function( resp ) {
						
						if( resp.html ) {

							var essayQuesLength = 0;
						    $.each( resp.question_type, function( index, elem ) {
							    if( elem === 'essay' ) {
							     	essayQuesLength++;
							    }  
						 	} );
						    
						    let essayMsg = essayQuesLength ? essayQuesLength + ' Essay(s) Pending' : '';

							quizBox.find( '.exms-quiz-badges' ).html( resp.badges_html );
							quizBox.find( '.exms-sub-ques-resp' ).html( '' );
							quizBox.find( '.exms-sub-ques-resp' ).removeAttr( 'style' );
							quizBox.find( '.exms-sub-ques-resp' ).after( '<div class="exms-quiz-results">'+resp.html+ '<p>'+essayMsg+'</p>' + '</div>' );
							quizBox.find( '.exms-quiz-message' ).html( resp.quiz_message );
							quizBox.find( '.exms-show-correct-answer' ).html( '' );
							quizBox.find( '.exms-show-correct-answer' ).attr( 'style', '' );
							quizBox.find( 'button, .exms-question-box' ).hide();		
							$( '.exms-quiz-results' ).after( resp.cert_html );

							if(EXMS.answer_summary == 'yes' ) {
								$( '.exms-question-box' ).css( 'display', 'block' ).removeClass('exms-hide').addClass('exms-quiz-end-style-question');
							}

							$(".exms-quiz-status-correct").css('display', 'none');
							$(".exms-quiz-status-incorrect").css('display', 'none');
						}

					}, 'json' );				
				}
			},

			/**
			 * Show range input value
			 */
			showRangeInputValue: function() {

				if( $( '.exms-range-input' ).length > 0 ) {

					$( '.exms-range-input' ).on( 'input', function() {

						let currentQues = $( '.exms-active-ques' );
						let numBox = currentQues.find( '.exms-range-num' );

						if( numBox.length > 0 ) {

							numBox.html( $( this ).val() );
						} 
					} );
				}
			},

			/**
			 * Add selected matrix answer to table
			 */
			setMatrixAnswerOnTable: function() {

				$( 'body' ).on( 'click', '.exms-matrix-ans', function() {

					let self = $( this );
					let answerParent = self.parents( '.exms-matrix-tcol' );
					let currentQues = $( '.exms-active-ques' );
					let tableCols = currentQues.find( '.exms-matrix-tcol' );
					let matrixAnswer = self;

					if( answerParent.length <= 0 && tableCols.length > 0 ) {

						$.each( tableCols, function( index, elem ) {

							if( $( elem ).html() == '' ) {

								$( elem ).html( matrixAnswer );
								return false;
							}
						} );
				
					} else if( answerParent.length > 0 && $( '.exms-matrix-boxes-row' ).length > 0 ) {

						$( '.exms-matrix-boxes-row' ).append( matrixAnswer );
					}
				} );
			},
			/**
			 * Display result of a quiz question
			 */
			resultAfterEachQuestion: function(resp){
				//exms-question-answer-submit
				var questionbox = $( '.exms-question-box-' + resp.question_id );
				var answers = resp.answers;
				var correct_answer = resp.show_correct_answer;
				switch(resp.question_type) {
					case "fill_blank":
						
						if( resp.passed == true ) {
							questionbox.find( '.exms-question-answer-submit' ).html( '<div class="exms-fill-blank-correct-answer">('+resp.show_correct_answer+')</div>' );
							questionbox.find( '.exms-ques-blanks' ).addClass( 'exms-multiple-choice-correct-answer' );
						} else {
							questionbox.find( '.exms-question-answer-submit' ).html( '<div class="exms-fill-blank-incorrect-answer">('+resp.show_correct_answer+')</div>' );
							questionbox.find( '.exms-ques-blanks' ).addClass( 'exms-multiple-choice-incorrect-answer' );
						}
						break;
					case "multiple_choice":
						$.each( questionbox.find( '.exms-answer-sel' ), function( index, elem ) {
							var ans = answers[index];
							//if( ( $(elem).prop('checked') == true && ans.type == 'correct' ) || ( $(elem).prop('checked') == false && ans.type == "wrong" ) ) {
							if( ans.type == 'correct' ) {								
								$(elem).parent().addClass( 'exms-multiple-choice-correct-answer' );
							} else {
								$(elem).parent().addClass( 'exms-multiple-choice-incorrect-answer' );
							}
						} );
						break;
					case 'single_choice':
						$.each( questionbox.find( '.exms-answer-sel' ), function( index, elem ) {
							var ans = answers[index];
							//if( ( $(elem).prop('checked') == true && ans.type == 'correct' ) || ( $(elem).prop('checked') == false && ans.type == "wrong" ) ) {
							if( ans.type == 'correct' ) {
								$(elem).parent().addClass( 'exms-multiple-choice-correct-answer' );
							} else {
								$(elem).parent().addClass( 'exms-multiple-choice-incorrect-answer' );
							}
						} );
						break;
					case 'sorting_choice':
						
						$.each( questionbox.find( '.exms-answer-sel' ), function( index, elem ) {
							var ans = correct_answer[index];
							if( ( ans == $(elem).val() ) ) {
								$(elem).parent().addClass( 'exms-multiple-choice-correct-answer' );
							} else {
								$(elem).parent().addClass( 'exms-multiple-choice-incorrect-answer' );
							}
						} );
						break;
					case 'matrix_sorting':
						
						$.each( questionbox.find( '.exms-matrix-table tr' ), function( index, elem ) {
							var ans = correct_answer[index];
							console.log( $(elem).find('.exms-matrix-ans').html() + ' == ' + $(elem).find('.exms-matrix-fcol').html() )
							if( $.trim( $(elem).find('.exms-matrix-ans').html() ) == $.trim( $(elem).find('.exms-matrix-fcol').html()) ) {
								$(elem).addClass( 'exms-multiple-choice-correct-answer' );
							} else {
								$(elem).addClass( 'exms-multiple-choice-incorrect-answer' );
							}
							
						} );
						break;
					case 'free_choice':
						
						if( resp.passed == true ) {
							questionbox.find( '.exms-question-answer-submit' ).html( '<div class="exms-fill-blank-correct-answer">('+resp.show_correct_answer+')</div>' );
							questionbox.find( '.exms-fc-textarea' ).addClass( 'exms-multiple-choice-correct-answer' );
						} else {
							questionbox.find( '.exms-question-answer-submit' ).html( '<div class="exms-fill-blank-incorrect-answer">('+resp.show_correct_answer+')</div>' );
							questionbox.find( '.exms-fc-textarea' ).addClass( 'exms-multiple-choice-incorrect-answer' );
						}
						
						break;
					case "range":
						if( resp.passed == true ) {
							questionbox.find( '.exms-question-answer-submit' ).html( '<div class="exms-fill-blank-correct-answer">('+resp.show_correct_answer+')</div>' );
							questionbox.find( '#exms-slider-range' ).addClass( 'exms-multiple-choice-correct-answer' );
							questionbox.find( 'p' ).addClass( 'exms-multiple-choice-correct-answer' );
						} else {
							questionbox.find( '.exms-question-answer-submit' ).html( '<div class="exms-fill-blank-incorrect-answer">('+resp.show_correct_answer+')</div>' );
							questionbox.find( '#exms-slider-range' ).addClass( 'exms-multiple-choice-incorrect-answer' );
							questionbox.find( 'p' ).addClass( 'exms-multiple-choice-incorrect-answer' );
						}
						break;
				}
			},
			/**
			 * Display next quiz question
			 */
			moveNextQuestion: function() {
				
				let self = $( '.exms-next-ques' );
				let randID = self.data( 'id' );
				let currentQues = $( '.exms-active-ques' );
				let maxQues = currentQues.parents( '.exms-quiz-box' ).data( 'max' );
				let NextQuestionIndex = ( Number ) ( currentQues.data( 'index' ) + 1 );

				if( NextQuestionIndex >= maxQues ) {

					$( '.exms-submit-answer' ).html( 'Finish' );
					$( '.exms-submit-answer' ).addClass( 'exms-finish-quiz' );
				}

				$( '.exms-active-ques' ).addClass( 'exms-hide' );
				$( '.exms-question-box' ).removeClass( 'exms-active-ques' );
				$( '.exms-ques-' + NextQuestionIndex ).addClass( 'exms-active-ques' );
				$( '.exms-ques-' + NextQuestionIndex ).removeClass( 'exms-hide' );
				self.addClass( 'exms-hide' );
				$( '.exms-submit-answer' ).removeClass( 'exms-hide' );
				$( '.exms-submit-answer' ).removeAttr( 'disabled' );
				$( '.exms-sub-ques-resp' ).html('');
				$( '.exms-sub-ques-resp' ).attr( 'style', '' );
				$( '.exms-show-correct-answer' ).html('');
				$( '.exms-show-correct-answer' ).attr( 'style', '' );

				if( $( '.exms-ques-' + NextQuestionIndex ).length > 0 ) {

					let id = $( '.exms-ques-' + NextQuestionIndex ).data( 'id' );
					EXMS_Theme.initializeTimer( $( '.exms-timer-' + id ), 'question', $( '.exms-timer-text-' + id ) );
				} 

				if( $( '.exms-timer-' + randID ).length > 0 ) {

					EXMS_Theme.initializeTimer( $( '.exms-timer-' + randID ), 'quiz', $( '.exms-timer-text-' + randID ) );
				}

				if( EXMS.question_correct_incorrect == 'yes' ) {
					$( '.exms-quiz-status-correct' ).css( 'display', 'none' );
					$( '.exms-quiz-status-incorrect' ).css( 'display', 'none' );
				}
			},
			/**
			 * Display next quiz question
			 */
			showNextQuestion: function() {

				$( 'body' ).on( 'click', '.exms-next-ques', function() {

					let self = $( this );
					let randID = self.data( 'id' );
					let currentQues = $( '.exms-active-ques' );
					let maxQues = currentQues.parents( '.exms-quiz-box' ).data( 'max' );
					let NextQuestionIndex = ( Number ) ( currentQues.data( 'index' ) + 1 );

					if( NextQuestionIndex >= maxQues ) {

						$( '.exms-submit-answer' ).html( 'Finish' );
						$( '.exms-submit-answer' ).addClass( 'exms-finish-quiz' );
					}

					$( '.exms-active-ques' ).addClass( 'exms-hide' );
					$( '.exms-question-box' ).removeClass( 'exms-active-ques' );
					$( '.exms-ques-' + NextQuestionIndex ).addClass( 'exms-active-ques' );
					$( '.exms-ques-' + NextQuestionIndex ).removeClass( 'exms-hide' );
					self.addClass( 'exms-hide' );
					$( '.exms-submit-answer' ).removeClass( 'exms-hide' );
					$( '.exms-submit-answer' ).removeAttr( 'disabled' );
					$( '.exms-sub-ques-resp' ).html('');
					$( '.exms-sub-ques-resp' ).attr( 'style', '' );
					$( '.exms-show-correct-answer' ).html('');
					$( '.exms-show-correct-answer' ).attr( 'style', '' );

					if( $( '.exms-ques-' + NextQuestionIndex ).length > 0 ) {

						let id = $( '.exms-ques-' + NextQuestionIndex ).data( 'id' );
						EXMS_Theme.initializeTimer( $( '.exms-timer-' + id ), 'question', $( '.exms-timer-text-' + id ) );
					} 

					if( $( '.exms-timer-' + randID ).length > 0 ) {

						EXMS_Theme.initializeTimer( $( '.exms-timer-' + randID ), 'quiz', $( '.exms-timer-text-' + randID ) );
					}
					
					if( EXMS.question_correct_incorrect == 'yes' ) {
						$( '.exms-quiz-status-correct' ).css( 'display', 'none' );
						$( '.exms-quiz-status-incorrect' ).css( 'display', 'none' );
					}
				} );
			},

			/**
			 * Submit quiz answer
			 */
			submitQuizAnswer: function() {

				if( $( '.exms-submit-answer' ).length > 0 ) {

					if( localStorage.getItem( 'quizResults' ) ) {

						localStorage.removeItem( 'quizResults' );
					}

					$( '.exms-submit-answer' ).on( 'click', function() { 

						//$( '.exms-quiz-loader' ).show();
						if( EXMS.question_correct_incorrect == 'yes' ) {
							$( '.exms-quiz-status-correct' ).css( 'display', 'none' );
							$( '.exms-quiz-status-incorrect' ).css( 'display', 'none' );
						}
						let self = $( this );
						self.parents(".exms-quiz-box :input").prop("disabled", true).css( {'filter':'alpha(opacity=60)', 'zoom':'1', 'opacity':'0.6'} );
						self.parents(".exms-quiz-box button").prop("disabled", true).css( {'filter':'alpha(opacity=60)', 'zoom':'1', 'opacity':'0.6'} );

						let quizBox = self.parents( '.exms-quiz-box' );
						let currentQues = quizBox.find( '.exms-active-ques' );
						let randID = self.data( 'id' );
						let type = currentQues.data( 'type' );
						let quesID = currentQues.data( 'id' );
						let quizID = currentQues.data( 'qid' );
						let answers = [];
						let activeQuestionChoices = currentQues.find( '.exms-answer-sel' );
						let btn = self;

						if( 'single_choice' == type || 'multiple_choice' == type || 'sorting_choice' == type ) {

							let activeQuestionChoices = currentQues.find( '.exms-answer-sel' );
							$.each( activeQuestionChoices, function( index, elem ) {

								if( $( elem ).prop( 'checked' ) || 'sorting_choice' == type ) {

									answers.push( $( elem ).val() );
								}
							} );

						} else if( 'fill_blank' == type ) {

							let submittedBlanks = currentQues.find( '.exms-ques-blanks' );
							
							$.each( submittedBlanks, function( index, elem ) {

								answers.push( $( elem ).val() );
							} );

						} else if( 'matrix_sorting' == type ) {

							let submittedBlanks = currentQues.find( '.exms-matrix-tcol .exms-matrix-ans' );
							let submittedAns = [];

							$.each( submittedBlanks, function( index, elem ) {

								answers.push( $( elem ).html() );
							} );

						} else if( 'range' == type && currentQues.find( '#exms-range-number' ).length > 0 ) {

							answers.push( currentQues.find( '#exms-range-number' ).val() );
		
						} else if( ( 'free_choice' == type || 'essay' == type ) && currentQues.find( '.exms-fc-textarea' ).length > 0 ) {

							answers.push( currentQues.find( '.exms-fc-textarea' ).val() );

						} else if( 'file_upload' == type && currentQues.find( '.exms-file-upd' ).length > 0 && '' != currentQues.find( '.exms-file-upd' ).val() ) {

							$.ajax( {
						        type: 'POST',
						        url: EXMS.ajaxURL,
						        data: new FormData( currentQues.find( '.exms-fu-form' )[0] ),
						        contentType: false,
						        processData: false,
						        dataType: 'json',
						        success: function( resp ) {
									
									self.parents(".exms-quiz-box :input").prop("disabled", false).css( {'filter':'alpha(opacity=100)', 'zoom':'1', 'opacity':'1'} );
									self.parents(".exms-quiz-box button").prop("disabled", false).css( {'filter':'alpha(opacity=100)', 'zoom':'1', 'opacity':'1'} );

						         	if( quizBox.find( '.exms-sub-ques-resp' ).length > 0 ) {

										quizBox.find( '.exms-sub-ques-resp' ).html( resp.response );

										if( '' != resp.response ) {

											$( '.exms-sub-ques-resp' ).css( 'padding', '1em' );
										}

										if( false == resp.passed ) {
											
											$( '.exms-sub-ques-resp' ).css({
												background: '#DB4B40',
												color: '#ffffff'
											});
										} else {
											
											$( '.exms-sub-ques-resp' ).css({
												background: '#47B77C',
												color: '#ffffff'
											});
										}

										$( '.exms-show-correct-answer' ).html( '' );
										EXMS_Theme.data.push( resp );
									}

									if( quizBox.find( '.exms-next-ques' ).length > 0 && EXMS.result_summary == 'result_after_each_question' ) {
										quizBox.find( '.exms-next-ques' ).removeClass( 'exms-hide' );
										quizBox.find( '.exms-submit-answer' ).addClass( 'exms-hide' );
										EXMS_Theme.resultAfterEachQuestion(resp);
									} else {
										EXMS_Theme.moveNextQuestion();
									}

									if( btn.hasClass( 'exms-finish-quiz' ) ) {

										EXMS_Theme.finishQuiz( EXMS_Theme, btn );
									}
						        },
						    } );
						    return false;
						}

						if( answers.length <= 0 ) {
							self.parents(".exms-quiz-box :input").prop("disabled", false).css( {'filter':'alpha(opacity=100)', 'zoom':'1', 'opacity':'1'} );
							self.parents(".exms-quiz-box button").prop("disabled", false).css( {'filter':'alpha(opacity=100)', 'zoom':'1', 'opacity':'1'} );

							quizBox.find( '.exms-sub-ques-resp' ).html( 'Please Select An Answer.' );
							return false;

						} else {

							quizBox.find( '.exms-sub-ques-resp' ).html( '' );
							quizBox.find( '.exms-sub-ques-resp' ).attr( 'style', '' );
						}

						if( $( '.exms-timer-' + quesID ).length > 0 ) {

							let intervalID = $( '.exms-timer-'+quesID ).data( 'interval-id' );
							clearInterval( intervalID );

						} else if( $( '.exms-timer-' + randID ).length > 0 && self.data['quiz-interval-' + randID] ) {
							
							clearInterval( self.data['quiz-interval-' + randID] );
						}
						let data = {
							action: 'exms_submit_quiz_answer',
							quiz_id: quizID,
							question_id: quesID,
							answers: answers
						};

						btn.attr( 'disabled', 'true' );
						//exms-quiz-status
						$.post( EXMS.ajaxURL, data, function( resp ) {
							
							self.parents(".exms-quiz-box :input").prop("disabled", false).css( {'filter':'alpha(opacity=100)', 'zoom':'1', 'opacity':'1'} );
							self.parents(".exms-quiz-box button").prop("disabled", false).css( {'filter':'alpha(opacity=100)', 'zoom':'1', 'opacity':'1'} );
							if( resp.show_answer_type == 'on' ) {
								let currentQues = $( '.exms-active-ques' );
								let QuestionIndex = currentQues.data( 'index' );
								if( $( '.exms-ques-' + QuestionIndex ).length > 0 ) {
									switch( resp.question_type ) {
										case "single_choice":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( 'input[type="radio"]' ), function( key, value ) {
												if( resp.show_correct_answer == $(value).val() ) {
													$(value).parent().addClass('exms-ques-correct');
												} else {
													$(value).parent().addClass('exms-ques-incorrect');
												}
											});
											break;
										case "multiple_choice":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( 'input[type="checkbox"]' ), function( key, value ) {
												$.each( resp.answers , function( key2, value2 ) {
													if( value2.answer == $(value).val() ) {
														if( value2.type == 'correct' ) {
															$(value).parent().addClass('exms-ques-correct');
														} else {
															$(value).parent().addClass('exms-ques-incorrect');
														}
													}
												});
											});
											break;
										case "free_choice":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( '.exms-fc-textarea' ), function( key, value ) { //
												if( resp.passed == true ) {
													$(value).addClass('exms-ques-correct').after('<span class="exms-ques-correct">('+resp.show_correct_answer+')</span>');
												} else {
													$(value).addClass('exms-ques-incorrect').after('<span class="exms-ques-incorrect">('+resp.show_correct_answer+')</span>');
												}
											});
											break;
										case "fill_blank":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( 'input[type="text"].exms-ques-blanks' ), function( key, value ) {
												if( resp.show_correct_answer == $(value).val() ) {
													$(value).addClass('exms-ques-correct').after('<span class="exms-ques-correct">('+resp.show_correct_answer+')</span>');
												} else {
													 $(value).addClass('exms-ques-incorrect').after('<span class="exms-ques-incorrect">('+resp.show_correct_answer+')</span>');
												}
											});
											break;
										case "sorting_choice":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( 'input[type="hidden"].exms-answer-sel' ), function( key, value ) {
												var correct_seq = resp.show_correct_answer;
												if( correct_seq[key] == $(value).val() ) {
													$(value).addClass('exms-ques-correct').after('<span class="exms-ques-correct">('+correct_seq[key]+')</span>');
												} else {
													 $(value).addClass('exms-ques-incorrect').after('<span class="exms-ques-incorrect">('+correct_seq[key]+')</span>');
												}
											});
											
											break;
										case "matrix_sorting":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( '.exms-matrix-table tr' ), function( key, value ) {
												var selected_answer = $.trim($(value).find('th:eq(1) .exms-matrix-ans').html());
												var correct_answer = $.trim(resp.show_correct_answer[key][1]);
												if( selected_answer == correct_answer ) {
													$(value).addClass('exms-ques-correct').after('<span class="exms-ques-correct">('+correct_answer+')</span>');
												} else {
													$(value).addClass('exms-ques-incorrect').after('<span class="exms-ques-incorrect">('+correct_answer+')</span>');
												}
											});
											break;
										case "range":
											$.each( $( '.exms-ques-' + QuestionIndex ).find( '#exms-range-number' ), function( key, value ) { //
												if( resp.passed == true ) {
													$(value).addClass('exms-ques-correct').after('<span class="exms-ques-correct">('+resp.show_correct_answer+')</span>');
												} else {
													$(value).addClass('exms-ques-incorrect').after('<span class="exms-ques-incorrect">('+resp.show_correct_answer+')</span>');
												}
											});
											break;
									} 
								}
							}
							
							if( quizBox.find( '.exms-sub-ques-resp' ).length > 0 ) {

								if( 'essay' != resp.question_type ) {

									quizBox.find( '.exms-sub-ques-resp' ).html( resp.response );
									
									if( '' != resp.response ) {
										$( '.exms-sub-ques-resp' ).css( 'padding', '1em' );
									}
								}

								if( false == resp.passed ) {
											
									$( '.exms-sub-ques-resp' ).css({
										background: '#DB4B40',
										color: '#ffffff'
									});
								} else {
									
									$( '.exms-sub-ques-resp' ).css({
										background: '#47B77C',
										color: '#ffffff'
									});
								}
								EXMS_Theme.data.push( resp );
							}

							if( quizBox.find( '.exms-next-ques' ).length > 0 && EXMS.result_summary == 'result_after_each_question' ) {
								quizBox.find( '.exms-next-ques' ).removeClass( 'exms-hide' );
								quizBox.find( '.exms-submit-answer' ).addClass( 'exms-hide' );
								EXMS_Theme.resultAfterEachQuestion(resp);
							} else {
								EXMS_Theme.moveNextQuestion();
							}

							if( btn.hasClass( 'exms-finish-quiz' ) ) {

								EXMS_Theme.finishQuiz( EXMS_Theme, btn );
							}

							/**
							 * Show correct answer when user answer is wrong
							 */
							if( false == resp.passed && 'on' == resp.show_answer_type ) {

								if( ! resp.show_correct_answer ) {

									let correctAnswer = 'Correct answer (s) is : <br><b>' + resp.show_correct_answer + ' ';
									if( 'matrix_sorting' == resp.question_type ) {

										let matrixAnswer = [];
										$.each( resp.show_correct_answer, function( index, elem ) {
											 	
											matrixAnswer.push( elem.join( ' = ' ) );
										});

										matrixAnswer = 'Correct answer (s) is : <br>' + matrixAnswer;
										$( '.exms-show-correct-answer' ).html( matrixAnswer.replace( /,/g, ', ' ) );
										$( '.exms-show-correct-answer' ).css({
											border: '2px solid #47B77C',
											padding: '10px',
											borderRadius: '6px'
										});

									} else if( 'essay' == resp.question_type ) {
										$( '.exms-show-correct-answer' ).html( 'This response will be reviewed and graded after submission.' );
										$( '.exms-show-correct-answer' ).css({
											border: '2px solid #47B77C',
											padding: '10px',
											borderRadius: '6px'
										});

									} else {

										$( '.exms-show-correct-answer' ).html( correctAnswer.split( ',' ).join( '<br />' ) );
										$( '.exms-show-correct-answer' ).css({
											border: '2px solid #47B77C',
											padding: '10px',
											borderRadius: '6px'
										});
									}
								}
							}

							if( EXMS.question_correct_incorrect == 'yes' ) {
								if( false == resp.passed ) {
									$( '.exms-quiz-status-correct' ).css( 'display', 'none' );
									$( '.exms-quiz-status-incorrect' ).css( 'display', 'block' );
								} else {
									$( '.exms-quiz-status-correct' ).css( 'display', 'block' );
									$( '.exms-quiz-status-incorrect' ).css( 'display', 'none' );
								}
							}
						}, 'json' );
					} )	;				
				}
			},

			/**
			 * Make sorting type answers draggable
			 */
			draggableSortingTypeAnswers: function() {

				let dragDest = '',
					dropDest = '';

				$( '.exms-sorting-ans' ).draggable( {

					revert : true,

					start: function( event, ui ) {

						dragDest = event.clientY;
					}

				} );

			    $( '.exms-sorting-ans-droppable' ).droppable( {

				      tolerance: 'touch',

				      drop: function( event, ui ) {
				      	
				      	dropDest = event.clientY;

				      	if( dragDest < dropDest ) {

				      		$( ui.draggable ).insertAfter( event.target );

				      	} else if( dragDest > dropDest ) {

				      		$( ui.draggable ).insertBefore( event.target );
				      	}
				      }
				} );
			},
		}

		EXMS_Theme.init();
	});
})( jQuery );


