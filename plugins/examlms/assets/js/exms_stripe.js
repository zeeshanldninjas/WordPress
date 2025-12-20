
(function ($, window) {
    'use strict';
    $(document).ready(function () {
        function cardValidation() {
            var valid = true;
            var name = jQuery('#name').val();
            var email = jQuery('#email').val();
            var cardNumber = jQuery('#card-number').val();
            var month = jQuery('#month').val();
            var year = jQuery('#year').val();
            var cvc = jQuery('#cvc').val();

            jQuery("#exms-stripe-error-message").html("").hide();

            if (name.trim() == "") {
                valid = false;
            }
            if (email.trim() == "") {
                valid = false;
            }
            if (cardNumber.trim() == "") {
                valid = false;
            }

            if (month.trim() == "") {
                valid = false;
            }
            if (year.trim() == "") {
                valid = false;
            }
            if (cvc.trim() == "") {
                valid = false;
            }

            if (valid == false) {
                jQuery("#exms-stripe-error-message").html("All Fields are required").show();
            }

            return valid;
        }
        //set your publishable key
        

        //callback to handle the response from stripe
        function stripeResponseHandler(status, response) {
            
            if (response.error) {
                //enable the submit button
                jQuery("#submit-btn").show();
                jQuery("#cslitelms-loader").css("display", "none");
                //display the errors on the form
                jQuery("#exms-stripe-error-message").html(response.error.message).show();
            } else {
                //get token id
                
                var token = response['id'];
                //insert the token into the form
                jQuery("#frmStripePayment").append("<input type='hidden' name='token' value='" + token + "' />");
                //submit form to the server
                jQuery("#frmStripePayment").submit();
            }
        }
        $('.submit-btn-action').on('click', function(e){
            e.preventDefault();
            stripePay(e);
        });
        window.stripePay = function (e) {
            e.preventDefault();
            if (EXMSS.stripe_api_key != '') {
                
                //Stripe.setPublishableKey('pk_test_51IpdyWAiLaIEgYsTABnplBvyWLQLn9aQK5PaDfSMMeQVR2MzWp2RhgRLHiWeTN4RtvdFl2efuFxugMWgLpJhhJYG00ALOSRFgH');
                Stripe.setPublishableKey(EXMSS.stripe_api_key);
        
                var valid = cardValidation();

                if (valid == true) {
                    //jQuery("#submit-btn").hide();
                    jQuery("#loader").css("display", "inline-block");
                    Stripe.createToken({
                        number: jQuery('#card-number').val(),
                        cvc: jQuery('#cvc').val(),
                        exp_month: jQuery('#month').val(),
                        exp_year: jQuery('#year').val()
                    }, stripeResponseHandler);

                    //submit from callback
                    return false;
                }
            } else {
                jQuery("#exms-stripe-error-message").html(EXMSS.stripe_api_key).show();
            }
        }
        
    });
})(jQuery, window);
( function( $ ) { 'use strict'; 
	$( document ).ready( function() {
		
		let EXMSStripe = {
			init: function() {
				
				this.open_popup();
                this.close_popup();
			},
            open_popup: function() {
                // $("#stripe-button-container").click(function (){
                //     $(".exms-pop-outer").fadeIn("slow");
                // });
                $(".payment-button-container").click(function (){
                    $(".exms-pop-outer").fadeIn("slow");
                });
                
            },
            close_popup: function() {
                $(".exms-close").click(function (){
                    $(".exms-pop-outer").fadeOut("slow");
                });
            }
            
		}
		EXMSStripe.init();
	});
} )( jQuery );