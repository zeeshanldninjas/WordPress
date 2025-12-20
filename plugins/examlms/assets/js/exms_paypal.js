( function( $ ) { 'use strict'; 
	$( document ).ready( function() {
		
		let EXMSpaypal = {
			init: function() {
				// this.openPaypalPopup();
				// this.proceedSelectedPayment();
				this.createOrderWithPaypal();
			},

			/**
			 * Create order with PayPal for Course purchases
			 */
			createCourseOrderWithPaypal: function() {

				// Check if PayPal button container exists
				if( ! $( '#exms-paypal-button-container' ).length ) {
					return false;
				}

				let courseID = $( '#exms-course-id' ).val();
				let price = $( '#exms-course-price' ).val();
				let courseTitle = $( '#exms-course-title' ).val();
				let payeeEmail = $( '#exms-paypal-payee' ).val();
				let userID = EXMS.user_id || 0;

				if( ! courseID || ! price || ! payeeEmail ) {
					console.error( 'Missing required data for PayPal payment' );
					return false;
				}

				paypal.Buttons( {
					createOrder: ( data, actions ) => {
						return actions.order.create( {
							purchase_units: [ {
								amount: {
									value: price
								},
								payee: {
									email_address: payeeEmail
								},
								description: 'Purchase of ' + courseTitle
							} ]
						} );
					},

					onApprove: ( data, actions ) => {
						return actions.order.capture().then( function( orderData ) {

							let ajaxData = {
								'action'     : 'exms_save_course_paypal_transactions',
								'security'   : EXMS.security,
								'user_id'    : userID,
								'course_id'  : courseID,
								'price'      : price,
								'order_data' : orderData
							};

							jQuery.post( EXMS.ajaxURL, ajaxData, function( resp ) {

								let response = JSON.parse( resp );
								if( response.status == 'false' || response.status == 'error' ) {
									alert( response.message || 'Payment failed. Please try again.' );
								} else {
									alert( 'Payment completed successfully! You are now enrolled in the course.' );
									location.reload( true );
								}
							} ).fail( function() {
								alert( 'Payment processing failed. Please contact support.' );
							} );

						} );
					},

					onError: function( err ) {
						console.error( 'PayPal error:', err );
						alert( 'Payment failed. Please try again or contact support.' );
					}
				} ).render( '#exms-paypal-button-container' );
			},

			/**
			 * Create order with paypal
			 */
			createOrderWithPaypal: function() {

				let body = $( 'body' );
				let parent = body.find( '.exms-quiz-pay-button' );
				let postID = parseInt( parent.attr( 'data-post-id' ) );
				if( ! postID ) {
					return false;
				}
				
				let userID = parseInt( parent.attr( 'data-user-id' ) );
				let price = parseInt( parent.attr( 'data-price' ) );
				let payeeEmail = parent.attr( 'data-payee-email' );
				let quizType = parent.attr( 'data-quiz-type' );
				let subDays = parseInt( parent.attr( 'data-subs-days' ) );

				paypal.Buttons( {
		            createOrder: ( data, actions) => {
		              	return actions.order.create( {
		                	purchase_units: [ {
		                  		amount: {
		                    		value: price
		                  		},
		                  		payee: {
							      	email_address: payeeEmail
							    },
		                	} ]
		              	} );
		            },

		            onApprove: ( data, actions ) => {
		              	return actions.order.capture().then( function( orderData ) {

		              		let data = {
								'action' 	: 'exms_save_paypal_transactions',
								'security'	: EXMS.security,
								'user_id'	: userID,
								'post_id'	: postID,
								'price'		: price,
								'quiz_type' : quizType,
								'sub_days'	: subDays,
								'order_data': orderData
							};

							jQuery.post( EXMS.ajaxURL, data, function( resp ) {

		                        let response = JSON.parse( resp );
		                        if( response.status == 'false' ) {
		                        	$.alert( response.message );
		                        } else {
		                        	$.alert( 'Payment Complete Successfully' );
		                        	location.reload( true );
		                        }
		                    } );

		                	// console.log('Capture result', orderData, JSON.stringify( orderData, null, 2 ) );
		                	// const transaction = orderData.purchase_units[0].payments.captures[0];
		                	// alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
		              	} );
		            }
	          	} ).render( '#paypal-button-container' );
			},

			/**
			 * Proceed the selected payment type
			 */
			proceedSelectedPayment: function() {
				if( $( '.wpeq-pay-button' ).length > 0 ) {
					$( '.wpeq-pay-button' ).on(  'click', function() {
						let id = $( this ).data( 'id' ),
							pay_selected = false;
						$.each( $( '.wpeq-payment-type-' + id ), function( index, elem ) {
							if( $( elem ).prop( 'checked' ) ) {
								pay_selected = $( elem ).data( 'value' )
							}
						} );

						if( pay_selected == 'paypal' ) {
							$( '.wpeq-paypal-submit-' + id ).trigger( 'click' );
						} else if( pay_selected == 'paypal_exp' ) {
							
							EXMS_paypal.quizPrice = $( '.wpeq-quiz-price-' + id ).val();
							EXMS_paypal.quizTitle = $( '.wpeq-quiz-title-' + id ).val();
							EXMS_paypal.quizId = $( '.wpeq-quiz-id-' + id ).val();

							if( $( '.wpeq-popup' ).length > 0 ) {
								$( '.wpeq-popup' ).css( 'display', 'block' );
								$( '.wpeq-popup-close' ).on( 'click', function() {
									$( '.wpeq-popup' ).css( 'display', 'none' );
								} );	
							}
						} else if ( ! pay_selected ) {
							alert( 'Please select any payment option.' );
						}
					});
				}
			},

			/**
		 	 * Open payal payment popup
			 */
			openPaypalPopup: function() {

				if( EXMS_paypal.hasExpress ) {
					try {
						paypal.Buttons({

							createOrder: function(data, actions) {
							    // This function sets up the details of the transaction, including the amount and line item details.
							    return actions.order.create({
								    purchase_units: [{
								      amount: {
								        value: EXMS_paypal.quizPrice,
								      },
								      payee: {
								      	email_address: EXMS_paypal.payeeEmail
								      },
								      description: 'Purchase of ' + EXMS_paypal.quizTitle
								    }]
								});
							},
							onApprove: function(data, actions) {
							      	// This function captures the funds from the transaction.
							  	return actions.order.capture().then(function(details) {
							        // This function shows a transaction success message to your buyer.
							    	
									$( '.wpeq-popup' ).css( 'display', 'none' );

							    	let data = {
							    		action 		: 'wpeq_payment_complete',
							    		order_id	: details.id,
							    		quizId 		: EXMS_paypal.quizId
							    	};

							    	$.post( EXMS.ajaxURL, data );
							  	});
							}

						}).render( '.wpeq-paypal-button-container' );	
					} catch( errors ) { 
						// console.error( errors );
					}	
				} 
			}
		}
		EXMSpaypal.init();

		// Make EXMSpaypal available globally for course PayPal button
		window.EXMSpaypal = EXMSpaypal;
	});

	// Expose course PayPal initialization globally (outside document ready)
	window.initCoursePayPalButton = function() {
		// Check if PayPal SDK is loaded
		if (typeof paypal === 'undefined') {
			console.error('PayPal SDK not loaded yet');
			return false;
		}
		
		// Check if EXMSpaypal is available
		if (typeof window.EXMSpaypal !== 'undefined' && typeof window.EXMSpaypal.createCourseOrderWithPaypal === 'function') {
			window.EXMSpaypal.createCourseOrderWithPaypal();
		} else {
			console.error('EXMSpaypal.createCourseOrderWithPaypal not available');
		}
	};

} )( jQuery );
