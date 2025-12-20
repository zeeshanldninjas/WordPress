( function( $ ) { 'use strict'; 
	
	$( document ).ready( function() {
		
		let EXMSstripePayment = {

			init: function() {
				this.proceedSelectedPayment();
			},

			/**
			 * Proceed the selected payment type
			 */
			proceedSelectedPayment: function() {
				
				let self = this;

				if( $( '.wpeq-pay-button' ).length > 0 ) {
					$( '.wpeq-pay-button' ).on(  'click', function() {
						let id = self.data( 'id' ),
							pay_selected = false;
						$.each( $( '.wpeq-payment-type-' + id ), function( index, elem ) {
							if( $( elem ).prop( 'checked' ) ) {
								pay_selected = $( elem ).data( 'value' )
							}
						} );

						if( pay_selected == 'stripe' ) {
							self.redirectToStripeCheckout();
						}
					});
				}
			},

			/**
			 * Redirect to stripe checkout page on button click
			 */
			redirectToStripeCheckout: function() {
						
				const EXMSstripe = Stripe( EXMS_stripe.apiKey );
				EXMSstripe.redirectToCheckout({
					// Make the id field from the Checkout Session creation API response
					// available to this file, so you can provide it as parameter here
					// instead of the {{CHECKOUT_SESSION_ID}} placeholder.
					sessionId: EXMS_stripe.sessionID
				}).then( function ( result ) {
					// If `redirectToCheckout` fails due to a browser or network
					// error, display the localized error message to your customer
					// using `result.error.message`.
					alert( result.error.message );
				});
			}
		}
		EXMSstripePayment.init();
	});

} )( jQuery );