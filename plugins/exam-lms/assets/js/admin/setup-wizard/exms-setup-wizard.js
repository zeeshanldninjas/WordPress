( function( $ ) { 'use strict';

	$( document ).ready( function() {

		let EXMSSetupWizard = {
			
			/**
			 * Initialize functions on load
			 */
			init: function() {

				this.buttonDisplay();
				this.setupWizardStartUP();
				this.setupFormValidate();
				this.removeValidationMessage();
				this.backToPrev();
				this.nextPage();
				this.addNewPostTypeTitle();
				this.removePostTypeTitle();
				this.addNewPostType();
				this.addNewCustomPostType();
				this.deleteNewCustomPostType();
				this.deletePostType();
				this.expandCoursePostType();
				this.paymentButtons();
				this.savePaymentSettings();
				this.editPostType();
				this.renameStructurePostType();
				this.live_validate_payment();
			},	
			
			live_validate_payment: function() {
				$( '.exms-setup-wizard-paypal input, .exms-setup-wizard-stripe input' ).on( 'keyup', function () {
					let self = $( this );
					if( self.val().trim().length >= 3 ) {
						self.removeClass( 'exms-setup-wizard-field-error' );
					} else {
						self.addClass( 'exms-setup-wizard-field-error' );
					}
				});
			},


			/**
			 * Hiding exit setup wizard button
			 * removing & adding active class
			 * closing success message
			 * @param {*} getAttr 
			 */
			buttonDisplay: function( getAttr ) {

				$( '.exms-success-close' ).on( 'click', function() {
					$( '.exms-success-messages' ).slideUp().change();
				});

				$( '.exms-course-structure' ).on( 'click', function() {
					$( '.exms-course-structure' ).removeClass( 'active' );
					$( this ).addClass( 'active' );
				});

				if( getAttr == 1 ) {
					$( '.exms-exit-setup' ).css( 'display', 'block' );
				}
			},

			/**
			 * Edit post type name
			 * Display input fields with change, cancel button
			 * Cancel button will revert the changes
			 * Change button will run the ajax
			 */
			editPostType: function() {

				$( 'body' ).on( 'click', '.exms-post-type-edit', function() {

					let self = $( this );
					let parent = self.parent( '.exms-post-type-list' );
					let postTypeName = parent.find( '.exms-post-type-name' ).text();

					let html = '<div class="exms-post-type-edit-wrap">';
					html += '<input type="text" class="exms-inline-post-type-input" value="'+postTypeName+'" />'
					html += '<input type="button" class="exms-inline-save-post-type" value="Save" />'
					html += '<input type="button" class="exms-inline-cancel-post-type" value="Cancel" />'
					html += '</div>';

					parent.find( '.exms-post-type-delete' ).hide();
					parent.find( '.exms-post-type-edit' ).hide();
					parent.find( '.exms-post-type-name' ).html( html );
				} );

				/* Remove post type inputs */
				$( 'body' ).on( 'click', '.exms-inline-cancel-post-type', function() {

					let self = $( this );
					let parent = self.parents( '.exms-post-type-list' );
					let postTypeName = parent.find( '.exms-inline-post-type-input' ).val();

					parent.find( '.exms-post-type-delete' ).show();
					parent.find( '.exms-post-type-edit' ).show();
					parent.find( '.exms-post-type-name' ).html( postTypeName );
				} );

				/* Rename post type name */
				$( 'body' ).on( 'click', '.exms-inline-save-post-type', function() {

					let self = $( this );
					let parent = self.parents( '.exms-post-type-list' );
					let postTypeName = parent.find( '.exms-inline-post-type-input' ).val();
					let postTypeSlug = parent.data( 'post-type' );

					parent.find( '.exms-post-type-delete' ).show();
					parent.find( '.exms-post-type-edit' ).show();
					parent.find( '.exms-post-type-name' ).html( postTypeName );

					let data = {
						'action' 			: 'exms_rename_post_type',
						'security'			: EXMS_SETUP_WIZARD.security,
						'post_type_slug'	: postTypeSlug,
						'post_type_name'	: postTypeName
					};

					jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {

                        let response = JSON.parse( resp );
                        if( response.status == 'false' ) {
                        	$.alert( response.message );
                        } else {
                        	
                        	$.alert( 'Post Type Renamed Successfully.' );
                        }
                    } );

				} );
			},

			/**
			 * Adding validations in labels fields
			 */
			validate_labels: function() {

				let valid = true;
				
				if( $( '#exms_wp_exams' ).val() == '' ) {
					$( '#exms_wp_exams' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_wp_exams' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_quizzes' ).val() == '' ) {
					$( '#exms_quizzes' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_quizzes' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_questions' ).val() == '' ) {
					$( '#exms_questions' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_questions' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_qroup' ).val() == '' ) {
					$( '#exms_qroup' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_qroup' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_point_types' ).val() == '' ) {
					$( '#exms_point_types' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_point_types' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_certificates' ).val() == '' ) {
					$( '#exms_certificates' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_certificates' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_settings' ).val() == '' ) {
					$( '#exms_settings' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_settings' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_submitted_essays' ).val() == '' ) {
					$( '#exms_submitted_essays' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_submitted_essays' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_user_report' ).val() == '' ) {
					$( '#exms_user_report' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_user_report' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_quiz_report' ).val() == '' ) {
					$( '#exms_quiz_report' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_quiz_report' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_transactions' ).val() == '' ) {
					$( '#exms_transactions' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_transactions' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_import_export' ).val() == '' ) {
					$( '#exms_import_export' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_import_export' ).removeClass('exms-setup-wizard-field-error');
				}

				if( $( '#exms_setup_wizard' ).val() == '' ) {
					$( '#exms_setup_wizard' ).focus().addClass('exms-setup-wizard-field-error');
					valid = false;
				} else {
					$( '#exms_setup_wizard' ).removeClass('exms-setup-wizard-field-error');
				}

				return valid;
			},
			
			/**
			 * Adding validation in payment fields
			 */
			validate_payments: function(){
				$('.exms-setup-wizard-field-error').removeClass('exms-setup-wizard-field-error');
				let valid = true;
					
					if( $( '.exms-paypal-currency' ).val() == '' ) {
						$( '.exms-paypal-currency' ).focus().addClass('exms-setup-wizard-field-error');
						valid = false;
					} else {
						$( '.exms-paypal-currency' ).removeClass('exms-setup-wizard-field-error');
					}

					if( $( '.exms-paypal-payee-email' ).val() == '' ) {
						$( '.exms-paypal-payee-email' ).focus().addClass('exms-setup-wizard-field-error');
						valid = false;
					} else {
						$( '.exms-paypal-payee-email' ).removeClass('exms-setup-wizard-field-error');
					}

					if( $( '.exms-paypal-redirects-complete' ).val() == '' ) {
						$( '.exms-paypal-redirects-complete' ).focus().addClass('exms-setup-wizard-field-error');
						valid = false;
					} else {
						$( '.exms-paypal-redirects-complete' ).removeClass('exms-setup-wizard-field-error');
					}

					if( $( '.exms-paypal-redirects-cancel' ).val() == '' ) {
						$( '.exms-paypal-redirects-cancel' ).focus().addClass('exms-setup-wizard-field-error');
						valid = false;
					} else {
						$( '.exms-paypal-redirects-cancel' ).removeClass('exms-setup-wizard-field-error');
					}

					var switch_stripe = false;
					if( valid ) {
						switch_stripe = true;
					}

					if( $( '.exms-stripe-currency' ).val() == '' ) {
						$( '.exms-stripe-currency' ).focus().addClass( 'exms-setup-wizard-field-error' );
						valid = false;
					} else {
						$( '.exms-stripe-currency' ).removeClass( 'exms-setup-wizard-field-error' );
					}

					if( $( '.exms-stripe-payee-email' ).val() == '' ) {
						$( '.exms-stripe-payee-email' ).focus().addClass( 'exms-setup-wizard-field-error' );
						valid = false;
					} else {
						$( '.exms-stripe-payee-email' ).removeClass( 'exms-setup-wizard-field-error' );
					}

					if( $( '.exms-stripe-redirects-complete' ).val() == '' ) {
						$( '.exms-stripe-redirects-complete' ).focus().addClass('exms-setup-wizard-field-error');
						valid = false;
					} else {
						$( '.exms-stripe-redirects-complete' ).removeClass('exms-setup-wizard-field-error');
					}

					if( $( '.exms-stripe-redirects-cancel' ).val() == '' ) {
						$( '.exms-stripe-redirects-cancel' ).focus().addClass('exms-setup-wizard-field-error');
						valid = false;
					} else {
						$( '.exms-stripe-redirects-cancel' ).removeClass('exms-setup-wizard-field-error');
					}

					if( switch_stripe && !valid ) {
						$( '.exms-setup-wizard-stripe' ).show().change();
						$( '.exms-setup-wizard-paypal' ).hide().change();

					}
				
				return valid;
			},

			/**
			 * saving payment details
			 */
			save_payment: function() {
				
				let paypalData = $( '.exms-setup-wizard-paypal' );
				let paymentData = $( '.exms-paypal-settings-parent' ).data( 'payment');
				
				if (!Array.isArray(paymentData)) {
					paymentData = [paymentData];
				}
				let paypalForm = {};
				let paypalRedirectURL = {};

				paypalRedirectURL['complete_url'] = paypalData.find( '.exms-paypal-redirects-complete' ).val();
				paypalRedirectURL['cancel_url'] = paypalData.find( '.exms-paypal-redirects-cancel' ).val();

				paypalForm['paypal_enable'] = paypalData.find('.exms-paypal-enable:checked').val();
				paypalForm['paypal_sandbox'] = paypalData.find('.exms-paypal-sandbox:checked').val();
				paypalForm['paypal_redirect_url'] = paypalRedirectURL;
				paypalForm['paypal_currency'] = paypalData.find( '.exms-paypal-currency' ).val();
				paypalForm['paypal_vender_email'] = paypalData.find( '.exms-paypal-payee-email' ).val();
				
				let stripeData = $( '.exms-setup-wizard-stripe' );

				let stripeForm = {};
				let stripeRedirectURL = {};

				stripeRedirectURL['complete_url'] = stripeData.find( '.exms-stripe-redirects-complete' ).val();
				stripeRedirectURL['cancel_url'] = stripeData.find( '.exms-stripe-redirects-cancel' ).val();

				stripeForm['stripe_enable'] = stripeData.find('.exms-stripe-enable:checked').val();
				stripeForm['stripe_sandbox'] = stripeData.find('.exms-stripe-sandbox:checked').val();
				stripeForm['stripe_redirect_url'] = stripeRedirectURL;
				stripeForm['stripe_currency'] = stripeData.find( '.exms-stripe-currency' ).val();
				stripeForm['stripe_vender_email'] = stripeData.find( '.exms-stripe-payee-email' ).val();
				
				let isStripePopupVisible = $( '.exms-setup-wizard-stripe' ).is( ':visible' );
				let isPaypalPopupVisible = $( '.exms-setup-wizard-paypal' ).is( ':visible' );
					
				let invalid = false;
				if( isPaypalPopupVisible ) {
					let paypalFieldsFilled =
						paypalForm['paypal_currency'] &&
						paypalForm['paypal_vender_email'] &&
						paypalForm['paypal_redirect_url']['complete_url'] &&
						paypalForm['paypal_redirect_url']['cancel_url'];

					if ( !paypalFieldsFilled ) {
						invalid = true;
					}
				}

				if( isStripePopupVisible ) {
					let stripeFieldsFilled =
						stripeForm['stripe_currency'] &&
						stripeForm['stripe_vender_email'] &&
						stripeForm['stripe_redirect_url']['complete_url'] &&
						stripeForm['stripe_redirect_url']['cancel_url'];

					if ( !stripeFieldsFilled ) {
						invalid = true;
					}
				}

				EXMSSetupWizard.validate_payments();
				if( invalid ) {
					$.alert( EXMS_SETUP_WIZARD.requiredFields );
					return false;
				}

				let currentData = {
					paypal_enable: paypalForm['paypal_enable'],
					paypal_redirect_url: paypalRedirectURL,
					paypal_sandbox: paypalForm['paypal_sandbox'],
					paypal_currency: paypalForm['paypal_currency'],
					paypal_vender_email: paypalForm['paypal_vender_email'],
					stripe_currency: stripeForm['stripe_currency'],
					stripe_enable: stripeForm['stripe_enable'],
					stripe_redirect_url: stripeRedirectURL,
					stripe_sandbox: stripeForm['stripe_sandbox'],
					stripe_vender_email: stripeForm['stripe_vender_email'],
				};

				let noChange = false;
				$.each( $( paymentData ), function( index, elem ) {
					let paypal_currency = elem.paypal_currency || "";
					let paypal_enable = elem.paypal_enable;
					let paypal_redirect_cancel_url = elem.paypal_redirect_url?.cancel_url || "";
					let paypal_redirect_complete_url = elem.paypal_redirect_url?.complete_url || "";
					let paypal_sandbox = elem.paypal_sandbox;
					let paypal_vender_email = elem.paypal_vender_email || "";

					let stripe_currency = elem.stripe_currency || "";
					let stripe_enable = elem.stripe_enable;
					let stripe_redirect_cancel_url = elem.stripe_redirect_url?.cancel_url || "";
					let stripe_redirect_complete_url = elem.stripe_redirect_url?.complete_url || "";
					let stripe_sandbox = elem.stripe_sandbox;
					let stripe_vender_email = elem.stripe_vender_email || "";

					if( paypal_currency === currentData.paypal_currency && paypal_enable === currentData.paypal_enable  &&
						paypal_sandbox === currentData.paypal_sandbox && paypal_vender_email === currentData.paypal_vender_email &&
						stripe_currency === currentData.stripe_currency && stripe_enable === currentData.stripe_enable &&
						stripe_sandbox === currentData.stripe_sandbox && stripe_vender_email === currentData.stripe_vender_email &&
						paypal_redirect_cancel_url === currentData.paypal_redirect_url.cancel_url && paypal_redirect_complete_url === currentData.paypal_redirect_url.complete_url && stripe_redirect_cancel_url === currentData.stripe_redirect_url.cancel_url && stripe_redirect_complete_url === currentData.stripe_redirect_url.complete_url
					) {
						noChange = true;
					}
				} )

				
				if( noChange == true ) {
					$.alert( EXMS_SETUP_WIZARD.unChange );
					return false;
				}

				let nextWrap = $( '.exms-update-button' );
				nextWrap.addClass( 'loading' ); 
				
				let data = {
					'action' 			: 'exms_save_setup_payments',
					'security'			: EXMS_SETUP_WIZARD.security,
					'paypal_data'		: paypalForm,
					'stripe_data'		: stripeForm
				};

				jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {

					let response = JSON.parse( resp );
					if( response.status == 'false' ) {
						$.alert( response.message );
					} else {
						nextWrap.removeClass( 'loading' ); 
						$.alert( EXMS_SETUP_WIZARD.paymentSaveMessage );
						if( stripeForm['stripe_enable'] == "on" ) {
							$( '.exms-stripe-button' ).text( EXMS_SETUP_WIZARD.enabled );
						} else {
							$( '.exms-stripe-button' ).text( EXMS_SETUP_WIZARD.configure );
						}
						if( paypalForm['paypal_enable'] == "on" ) {
							$( '.exms-paypal-button' ).text( EXMS_SETUP_WIZARD.enabled );
						} else {
							$( '.exms-paypal-button' ).text( EXMS_SETUP_WIZARD.configure );
						}

						$('.exms-paypal-settings-parent').data('payment', {
							paypal_enable: paypalForm['paypal_enable'],
							paypal_sandbox: paypalForm['paypal_sandbox'],
							paypal_redirect_url: paypalForm['paypal_redirect_url'],
							paypal_currency: paypalForm['paypal_currency'],
							paypal_vender_email: paypalForm['paypal_vender_email'],
							stripe_enable: stripeForm['stripe_enable'],
							stripe_sandbox: stripeForm['stripe_sandbox'],
							stripe_redirect_url: stripeForm['stripe_redirect_url'],
							stripe_currency: stripeForm['stripe_currency'],
							stripe_vender_email: stripeForm['stripe_vender_email'],
						});

						$( '#exms-payment-modal' ).hide().change();
					}
				} );
			},

			isEqual: function(a, b) {
				return JSON.stringify(a) === JSON.stringify(b);
			},
			
			/**
			 * Save payment settings on db
			 */
			savePaymentSettings: function() {

				$( 'body' ).on( 'click', '.exms-update-button', function(e) {

					e.preventDefault();
					let self = $( this );
					
					EXMSSetupWizard.save_payment();

				} );
			},

			/**
			 * Switch payment setting payment/stripe
			 */
			paymentButtons: function() {

				$( 'body' ).on( 'click', '.exms-payment-buttons', function() {

					let self = $( this );
					let check = $( '.exms-settings-container' ).addClass( 'exms-payment-parent' );
					let paymentType = self.data( 'payment-method' );
					$( '#exms-payment-modal' ).show().change();

					if( 'exms-paypal' == paymentType ) {

						$( '.exms-setup-wizard-paypal' ).show().change();
						$( '.exms-setup-wizard-stripe' ).hide().change();
					}

					if( 'exms-stripe' == paymentType ) {

						$( '.exms-setup-wizard-stripe' ).show().change();
						$( '.exms-setup-wizard-paypal' ).hide().change();
					}
				} );

				$( 'body' ).on( 'click', '#exms-payment-close-btn', function() {

					$( '#exms-payment-modal' ).hide().change();
				} );
			},

			/**
			 * Expand course post type
			 */
			expandCoursePostType: function() {

				$( 'body' ).on( 'click', '.exms-post-type-expand', function() {

					let self = $( this );
					let parent = self.parent( '.exms-post-type-list' );
					let postType = parent.data( 'post-type' );

					let expandParent = parent.parent( '.exms-post-type-list-wrap' );

					if( self.hasClass( 'dashicons-arrow-down-alt2' ) ) {

						$( '.exms-add-post-title-wrap' ).hide();

						let expandParentLength = expandParent.nextAll().length;
						if( ! expandParentLength ) {
							$( '.exms-create-post-wrap' ).insertAfter( expandParent ).slideDown();
						}

						expandParent.next().slideDown();

						self.removeClass( 'dashicons-arrow-down-alt2' );
						self.addClass( 'dashicons-arrow-up-alt2' );

					} else {

						let expandParentLength = expandParent.nextAll().length;
						if( ! expandParentLength ) {
							$( '.exms-create-post-wrap' ).insertAfter( expandParent ).slideUp();
						}

						expandParent.nextAll().slideUp();
						expandParent.nextAll().find( '.exms-post-type-expand' ).removeClass( 'dashicons-arrow-up-alt2' );
						expandParent.nextAll().find( '.exms-post-type-expand' ).addClass( 'dashicons-arrow-down-alt2' );

						self.removeClass( 'dashicons-arrow-up-alt2' );
						self.addClass( 'dashicons-arrow-down-alt2' );
					}

					$( '.exms-create-post-wrap' ).attr( 'data-parent-post-type', postType );

				} );
			},

			/**
			 * Delete Post type
			 */
			deletePostType: function() {

				$( 'body' ).on( 'click', '.exms-post-type-delete', function() {

					let self = $( this );
					let parent = self.parent( '.exms-post-type-list' );
					let postType = parent.data( 'post-type' );

					let childExists = parent.parent( '.exms-post-type-list-wrap' ).nextAll().length;
					if( childExists != 0 ) {

						$.alert( 'First Delete the Child Post Type.' );
						return false;
					}
					
					$.confirm({
                        title: false,
                        content: 'Are you sure you want to delete this post type.?',
                        buttons: {
                            Yes: function () {

                                let data = {
									'action' 			: 'exms_delete_post_type',
									'security'			: EXMS_SETUP_WIZARD.security,
									'post_type'			: postType,
								};

								jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {

			                        let response = JSON.parse( resp );
			                        if( response.status == 'false' ) {
			                        		
			                        	$.alert( response.message );

			                        } else {
			                        	
			                        	$( '.exms-create-post-wrap' ).appendTo( '.exms-finish-p2-setup' );
			                        	$( '.exms-create-post-wrap' ).hide();

			                        	parent.parent( '.exms-post-type-list-wrap' ).remove();

			                        	let postTypeCount = $( '.exms-post-type-list-wrap' ).length;
			                        	if( postTypeCount < 1 ) {

			                        		$( '.exms-create-post-wrap' ).show();
			                        		$( '.exms-post-types-html' ).removeClass( 'exms-post-types-exists' );
			                        	}

			                        	$.alert( 'Post Type Delete Successfully.' );
			                        }
			                    } );
                            },
                            No: function () {},
                        }
                    });
				} );
			},

			/**
			 * Adding new post type
			 * Structure || Manual 
			 */
			addNewPostType: function () {
				let previousStructure = $('.exms-course-structure.active').data('structure');
				$( 'body' ).on( 'click', '.exms-course-structure', function () {
					
					$( '.exms-course-structure' ).removeClass( 'active' );
					$( this ).addClass( 'active' );
				} );
				
				$( 'body' ).on( 'click', '.exms-next-setup-wrap', function () {

					let pageContainer = $( this ).attr( 'data-redirect' );
					let pageNo = parseInt( pageContainer.replace( 'exms-setup-p', '' ) );
					let nextPage = pageNo + 1;
					if ( $( this ).attr( 'id' ) === 'exms-next-setup-wrap' && nextPage == 3 ) {

						let customPostTypes = $( '.exms-post-types-html .exms-post-type-item' );
						if ( customPostTypes.length > 0 ) {
							return;
						}

						let structureCard = $( '.exms-course-structure.active' );
						let structureType = structureCard.data( 'structure' );
						let stepsContainer = structureCard.find( '.exms-course-structure-steps' );
						let structureSteps = stepsContainer.data( 'structure-steps' );
						if ( !Array.isArray( structureSteps ) ) {
							structureSteps = JSON.parse( stepsContainer.attr( 'data-structure-steps' ) || '[]' );
						}
						let labelContainer = $('.exms-label-setup-page');
						let newDynamicLabels = {};
						const fixedKeys = ['exms-courses', 'exms-lessons', 'exms-topics'];
						let fixedIndex = 0;
						structureSteps.forEach( ( step ) => {
							if (!step || typeof step !== 'string') return;
							const stepLower = step.toLowerCase().trim();
							if (['quiz', 'quizzes'].includes(stepLower)) return;

							let stepKey = '';
							if (fixedIndex < 3) {
								stepKey = fixedKeys[fixedIndex];
								fixedIndex++;
							} else {
								stepKey = 'exms-' + stepLower.replace(/\s+/g, '-');
							}

							if (['exms-quiz', 'exms-quizzes'].includes(stepKey)) return;

							newDynamicLabels[stepKey] = step;
						});
						
						let existingLabels = labelContainer.attr('data-dynamic-label');
						let isLabelEmpty = !existingLabels || existingLabels === '{}' || existingLabels === '[]';
						labelContainer.attr( 'data-dynamic-labels', JSON.stringify( newDynamicLabels ) );			
						labelContainer.attr( 'data-dynamic-label', JSON.stringify( newDynamicLabels ) );

						let oldLabels = {};
						try {
							oldLabels = existingLabels ? JSON.parse(existingLabels) : {};
						} catch (e) {
							oldLabels = {};
						}
						
						if( structureType === previousStructure && !isLabelEmpty && isEqual(oldLabels, newDynamicLabels) ) {
							return;
						}
						
						if( structureType === 'default' && isLabelEmpty ) {
							return;
						}

						let self = $( this );
						let nextChild = self.find( '.exms-flex-content-btn' );
						nextChild.addClass( 'loading' ); 

						let data = {
							action: 'exms_add_new_post_type',
							security: EXMS_SETUP_WIZARD.security,
							structure: structureType,
							steps: structureSteps,
							dynamic_labels: newDynamicLabels
						};
				
						jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {
							let response = ( typeof resp === 'string' ) ? JSON.parse( resp ) : resp;
							if ( response.status !== 'true' ) {
								$.alert( response.message );
							} else {
								nextChild.removeClass( 'loading' ); 
								EXMSSetupWizard.generateDynamicLabelInputs(structureSteps)	
								previousStructure = structureType;
								$( 'body' ).attr( 'data-previous-structure', structureType );
								$( '.exms-setup-report' ).attr( 'data-exms-post-types', JSON.stringify( response.post_types ) );
								$( '#exms_quizzes' ).val( response.quiz_name );
								let wrapper = $('.exms-dashboard-page-setting.exms-label-setup-page');
								if ( wrapper ) {
									let dataLabelRaw = wrapper.attr( 'data-label' ) || '{}';
									let dataLabel = JSON.parse( dataLabelRaw );
									dataLabel['exms_quizzes'] = response.quiz_name;
									wrapper.attr( 'data-label', JSON.stringify( dataLabel ) );
								}
							}
						} );
					}
				} );				
			},

			generateDynamicLabelInputs: function(steps) {
				const fixedKeys = ['exms-courses', 'exms-lessons', 'exms-topics'];
				let fixedIndex = 0;
				let container = $('.exms-label-setup-page');
				let labelsHtml = [];

				steps.forEach( ( step ) => {
					if ( !step || typeof step !== 'string' ) return;
					let stepLower = step.toLowerCase().trim();
					if( ['quiz', 'quizzes'].includes( stepLower ) ) return;

					let stepKey = '';
					if( fixedIndex < 3 ) {
						stepKey = fixedKeys[fixedIndex];
						fixedIndex++;
					} else {
						stepKey = 'exms-' + stepLower.replace(/\s+/g, '-');
					}

					if (['exms-quiz', 'exms-quizzes'].includes(stepKey)) return;

					let inputId = stepKey;
					let labelText = step.charAt(0).toUpperCase() + step.slice(1);
					let inputHtml = `
						<div class="exms-setup-settings-row exms-dynamic-label-row">
							<div class="exms-setting-lable">
								<label>${labelText} label:</label>
							</div>
							<div class="exms-setup-setting-data">
								<input type="text" id="${inputId}" name="${inputId}" placeholder="${labelText}" value="${labelText}">
							</div>
						</div>
					`;
					labelsHtml.push( inputHtml );
				});

				container.find( '.exms-dynamic-label-row' ).remove();
				container.prepend( labelsHtml.join( '' ) );
			},
			
			/**
			 * Adding custom post type structure
			 * exms_custom_post_types, exms_post_types in options table
			 */
			addNewCustomPostType: function() {
				
				$( 'body' ).on( 'click', '.exms-add-post-title-add', function () {
					let self = $( this );
					let parent = self.closest( '.exms-add-post-title-wrap' );
					let postTypeName = parent.find( '.exms-add-post-title-input' ).val().trim();
					let postTypeSlug = postTypeName.replace( /\s+/g, '-' ).toLowerCase();
					let parentPostType = parent.data( 'parent-post-type' ) || '';

					if ( !postTypeName ) return;
					let postTypeItem = `
						<div class="exms-post-type-item" data-name="${postTypeName}" data-slug="${postTypeSlug}" data-parent="${parentPostType}">
							<input type="text" class="exms-custom-post-type-name-input" value="${postTypeName}" style="" />
						</div>
					`;

					let newItem = $( postTypeItem );
					$( '.exms-post-types-html' ).append( newItem ).change();

					let index = $( '.exms-post-type-item' ).length - 1;
					let marginLeft = (index === 0) ? 0 : (index * 20);

					newItem.css('margin-left', marginLeft + 'px');
					parent.find( '.exms-add-post-title-input' ).val( '' );
				});

				$( 'body' ).on('click', '.exms-save-button a', function ( e ) {
					e.preventDefault();
				
					let postTypes = [];
					let button = $( this );

					$( '.exms-post-types-html .exms-post-type-item' ).each( function () {
						let item = $( this );
						let name = item.data( 'name' );
						let slug = item.data( 'slug' );
						let parent = item.data( 'parent' );
				
						if ( name && slug ) {
							postTypes.push( { name, slug, parent } );
						}
					});
				
					button.html( '<span class="exms-load"></span>' );

					if( postTypes.length === 0 ) {
						button.html( 'Save' );
						$.alert('No post types to save.');
						return;
					}
					let labelContainer = $( '.exms-label-setup-page' );
					let newDynamicLabels = {};
					const fixedKeys = ['exms-courses', 'exms-lessons', 'exms-topics'];
					let fixedIndex = 0;

					postTypes.forEach( ( step ) => {
						const stepName = step.name;
						if( !stepName || typeof stepName !== 'string' ) return;

						const stepLower = stepName.toLowerCase().trim();
						if( ['quiz', 'quizzes'].includes( stepLower ) ) return;

						let stepKey = '';
						if( fixedIndex < 3 ) {
							stepKey = fixedKeys[fixedIndex];
							fixedIndex++;
						} else {
							stepKey = 'exms-' + stepLower.replace(/\s+/g, '-');
						}

						if( ['exms-quiz', 'exms-quizzes'].includes( stepKey ) ) return;

						newDynamicLabels[stepKey] = stepName;
					});
					labelContainer.attr( 'data-dynamic-labels', JSON.stringify( newDynamicLabels ) );			
					labelContainer.attr( 'data-dynamic-label', JSON.stringify( newDynamicLabels ) );			
						
					$.post( EXMS_SETUP_WIZARD.ajaxURL, {
						action: 'exms_save_course_structure_callback',
						security: EXMS_SETUP_WIZARD.security,
						structure_data: postTypes,
						dynamic_labels: newDynamicLabels
					}, function( resp ) {
						
						if ( resp.status === 'true' ) {
							button.html( 'Save' );
							$( '.exms-course-structure' ).removeClass( 'active' );
							let html = `
								<div class="exms-course-structure active" data-structure="custom">
									<div class="exms-course-structure-heading">
										<h4> Custom Course Structure </h4>
										<span class="delete-custom-structure dashicons dashicons-trash" title="Delete structure"></span>
										<span class="edit-structure-step-names dashicons dashicons-edit"></span>
									</div>
									<div class="exms-course-structure-steps" data-structure-steps='${JSON.stringify(resp.steps)}'>
										${resp.steps.length > 0 ? resp.steps.map(step => `<p>${step}</p>`).join('') : '<p>No dynamic steps available</p>'}
									</div>
								</div>
							`;
							$( '.exms-setup-course-child' ).find( '.exms-course-structure[data-structure="modular"]' ).after( html );
							$( '#exms-course-structure-modal' ).hide().change();
							EXMSSetupWizard.generateDynamicLabelInputs(postTypes.map(p => p.name));
							$( '.exms-setup-report' ).attr( 'data-exms-post-types', JSON.stringify( response.post_types ) );
							$( '#exms_quizzes' ).val( response.quiz_name );
							let wrapper = $('.exms-dashboard-page-setting.exms-label-setup-page');
							if ( wrapper ) {
								let dataLabelRaw = wrapper.attr( 'data-label' ) || '{}';
								let dataLabel = JSON.parse( dataLabelRaw );
								dataLabel['exms_quizzes'] = response.quiz_name;
								wrapper.attr( 'data-label', JSON.stringify( dataLabel ) );
							}
							
						} else {
							$.alert(resp.message || 'Error saving structure.');
						}
					});
				});
			},

			/**
			 * Deleting custom post type structure when click on delete icon
			 * exms_custom_post_types from options table
			 */
			deleteNewCustomPostType: function() {

				$( document ).on( 'click', '.delete-custom-structure', function ( e ) {

					e.preventDefault();
					if ( confirm( EXMS_SETUP_WIZARD.customConfirmMessage ) ) {

						jQuery.ajax( {
							url: EXMS_SETUP_WIZARD.ajaxURL,
							type: 'POST',
							data: {
								action: 'exms_delete_custom_structure',
								security: EXMS_SETUP_WIZARD.security,
							},
							success: function ( response ) {
								if ( response.status === 'success' ) {
									$.alert( EXMS_SETUP_WIZARD.customMessage );
									$( '.exms-course-structure[data-structure="custom"]' ).remove();
									let selectedStructure = response.structure
									if ( selectedStructure == 'custom' ) {
										$('.exms-course-structure[data-structure="default"]').addClass( 'active' );
									} else {
										$( `.exms-course-structure[data-structure="${selectedStructure}"]` ).addClass( 'active' );
									}
								} else {
									$.alert( response.message || EXMS_SETUP_WIZARD.structureDeleteMessage );
								}
							},
							error: function () {
								$.alert( EXMS_SETUP_WIZARD.ajaxErrorMessage );
							}
						});
					}
				});
			},

			/**
			 * Remove Post type title direct on add new
			 */
			removePostTypeTitle: function() {

				$( 'body' ).on( 'click', '.exms-add-post-title-remove', function() {

					let self = $( this );
					let parent = self.parent( '.exms-add-post-title-wrap' );
					let grandParent = parent.parent( '.exms-post-type-list-wrap' );

					grandParent.find( '.exms-post-type-expand' ).removeClass( 'dashicons-arrow-up-alt2' );
					grandParent.find( '.exms-post-type-expand' ).addClass( 'dashicons-arrow-down-alt2' );

					parent.remove();

					let postTypeCount = $( '.exms-post-type-list-wrap' ).length;
	            	if( postTypeCount < 1 ) {
	            		$( '.exms-create-post-wrap' ).show();
	            	}
				} );
			},

			/**
			 * Add new post repeater fields
			 */
			addNewPostTypeTitle: function() {

				let modalInitialized = false;
				$( 'body' ).on( 'click', '.exms-post-add-new', function() {

					let self = $( this );
					$( '#exms-course-structure-modal' ).show().change();

					if( !modalInitialized ) {

						modalInitialized = true;
						let postTypesExist = $('.exms-post-types-html .exms-setup-created-post-types .exms-post-type-list-wrap').length > 0;
						if ( !postTypesExist ) {
							
							$( '.exms-create-post-wrap' ).hide().change();
							if ( $( '.exms-add-post-title-wrap' ).length === 0 ) {
								let parentPostType = self.attr( 'data-parent-post-type' );
								let html = '<div class="exms-add-post-title-wrap" data-parent-post-type="'+parentPostType+'">';
								html += '<input type="text" class="exms-add-post-title-input" name="exms_post_title" placeholder="Add New Structure" >';
								html += '<input type="button" class="exms-add-post-title-add exms-post-margin" name="exms_post_add" value="Add">';
								html += '<input type="button" class="exms-add-post-title-remove exms-post-margin" name="exms_post_remove" value="Remove">';
								html += '</div>';
		
								$( '#exms-course-structure-modal-form .exms-save-button' ).before( html ).change();
							}
						}
						return;
					} 

					$( '.exms-create-post-wrap, .exms-finish-p2-setup' ).hide().change();
					if ( $( '.exms-add-post-title-wrap' ).length === 0 ) {
						let parentPostType = self.attr( 'data-parent-post-type' );
						let html = '<div class="exms-add-post-title-wrap" data-parent-post-type="'+parentPostType+'">';
						html += '<input type="text" class="exms-add-post-title-input" name="exms_post_title" placeholder="Add New Structure" >';
						html += '<input type="button" class="exms-add-post-title-add exms-post-margin" name="exms_post_add" value="Add">';
						html += '<input type="button" class="exms-add-post-title-remove exms-post-margin" name="exms_post_remove" value="Cancel">';
						html += '</div>';
						$( '#exms-course-structure-modal-form .exms-save-button' ).before( html ).change();
					}
					self.appendTo( '.exms-finish-p2-setup' );
				} );

				$( 'body' ).on( 'click', '#exms-course-structure-close-btn', function() {
					modalInitialized = false;
					$( '#exms-course-structure-modal' ).hide().change();
				});
			},

			/**
			 * Display next page
			 */
			nextPage: function() {

				$( 'body' ).on( 'click', '.exms-next-setup-wrap, .exms-skip-setup-wrap, .exms-finish-setup-wrap', function() {

					let isValidate = $( '.exms-validate-license' ).hasClass( 'is-validate' );
					// if( ! isValidate ) {
					// 	return false;
					// }

					let self = $( this );
					let pageContainer = self.attr( 'data-redirect' );

					let pageNo = parseInt( pageContainer.replace( 'exms-setup-p', '' ) );
					let nextPage = pageNo + 1;
					
					if( self.attr('id') == 'exms-next-setup-wrap' ) {
						
						if (nextPage === 3) {
							let stripeEnabled = $( '.exms-stripe-enable:checked' ).val() === "on";
							let paypalEnabled = $( '.exms-paypal-enable:checked' ).val() === "on";

							if ( !stripeEnabled && !paypalEnabled ) {
								$.alert( EXMS_SETUP_WIZARD.configurePayment );
								return false;
							}
						} 
						if( nextPage == 4 ) {
							EXMSSetupWizard.save_general();
						} 
						if( nextPage == 5 ) {
							if( EXMSSetupWizard.validate_labels() ) {
								EXMSSetupWizard.save_labels();
							} else {
								return false;
							}
						} 
					}
					
					if( self.attr( 'id' ) == 'exms-finish-setup-wrap' ) {
						EXMSSetupWizard.saveBug();
						let postTypes = $( '.exms-setup-report' ).data( 'exms-post-types' );
						if( postTypes && typeof postTypes === 'object' ) {
							let keys = Object.keys( postTypes );
							if( keys.length > 0 && postTypes[keys[0]]?.post_type_name ) {
								let firstPostType = postTypes[keys[0]].post_type_name;
								document.location = EXMS_SETUP_WIZARD.admin_url + 'edit.php?post_type=' + firstPostType;
								return;
							}
						}
						document.location = EXMS_SETUP_WIZARD.admin_url + 'edit.php?post_type=exms-quizzes';
					}
					
					$( '.exms-page'+pageNo ).find( '.exms-progress-bar' ).css( {
						'border-color'  : '#552CA8'
					} );

					if( ! $( '.exms-setup-start' ).hasClass( 'exms-setup-p'+nextPage ) ) {
						return false;
					}

					$( '.exms-back-setup-wrap' ).attr( 'data-redirect', pageContainer );
					$( '.exms-next-setup-wrap, .exms-finish-setup-wrap, .exms-skip-setup-wrap' ).attr( 'data-redirect', 'exms-setup-p'+nextPage );

					let nextClass = 'exms-setup-p'+nextPage;

					$( '.'+nextClass ).show();

					$.each( $( '.exms-page' ), function( index, elem ) {
						
						let allPageID = parseInt( $( elem ).attr( 'class' ).replaceAll( 'exms-page', '' ) );	
						let currentPageID = parseInt( nextClass.replace( 'exms-setup-p', '' ) );
						
						if( allPageID <= currentPageID ) {
							$( '.exms-page'+allPageID ).find( '.exms-no' ).css( {
								'background': '#552CA8',
								'color': '#fff'
							} );
						}

						if( allPageID != currentPageID ) {
							$( '.exms-setup-p'+allPageID ).hide();
						}
					} );
					EXMSSetupWizard.switch_buttons(nextPage);
					
				} );
			},

			switch_buttons: function( nextp ) {
				if( nextp == 5 ) {
							
					$( '#exms-next-setup-wrap' ).css( 'display', 'none' );
					$( '#exms-skip-setup-wrap' ).css( 'display', 'none' );
					$( '#exms-finish-setup-wrap' ).css( 'display', 'block' );
				}  else {
					$( '#exms-next-setup-wrap' ).css( 'display', 'block' );
					$( '#exms-skip-setup-wrap' ).css( 'display', 'block' );
					$( '#exms-finish-setup-wrap' ).css( 'display', 'none' );
				}
			},
			
			save_general: function() {

				let container = $( '.exms-setup-wizard-general-settings' );
				let original = JSON.parse( container.attr( 'data-general' ) )

				let dashboard = $( '#exms-select-dashboard-wizard' ).val();
				let uninstall = $( 'input[name="exms_uninstall_wizard"]:checked' ).val();
				if ( dashboard == original['dashboard_page'] && uninstall === original['exms_uninstall'] ) {
					return;
				}

				let nextWrap = $( '.exms-next-setup-wrap' );
				let nextChild = nextWrap.find( '.exms-flex-content-btn' );
				nextChild.addClass( 'loading' ); 

				let data = { 
					'action' 			: 'exms_wizard_save_general',
					'security'			: EXMS_SETUP_WIZARD.security,
					'dashboard'			: dashboard,
					'uninstall'			: uninstall
				};
				
				jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {

					nextChild.removeClass( 'loading' );
					container.attr( 'data-general', JSON.stringify( {
						dashboard_page: dashboard,
						exms_uninstall: uninstall,
					} ) );
				} );
			},

			saveBug: function() {

				let container = $( '.exms-setup-report' );
				let original = JSON.parse( container.attr( 'data-bug' ) );

				let currentValue = $('input[name="dfce_lesson_enable"]:checked').val() || '';
				if( original['dfce_lesson_enable'] == currentValue) return false;

				let data = {
					'action' 				: 'exms_wizard_save_labels',
					'security'				: EXMS_SETUP_WIZARD.security,
					'dfce_lesson_enable'	: currentValue
				};

				let nextWrap = $( '.exms-finish-setup-wrap' );
				let nextChild = nextWrap.find( '.exms-flex-content-btn' );
				nextChild.addClass( 'loading' ); 
				
				jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {
					nextChild.removeClass( 'loading' );
					container.attr( 'data-bug', JSON.stringify( {
						dfce_lesson_enable: currentValue,
					} ) );
				} );
			},

			save_labels: function() {

				let container = $( '.exms-label-setup-page' );
				let original = JSON.parse( container.attr( 'data-label' ) )
				let dynamicLabels = JSON.parse( container.attr('data-dynamic-labels' ) || '{}');
				let checkLabels = JSON.parse( container.attr('data-dynamic-label' ) || '{}');
				let quiz_report = $( '#exms_quiz_report' ).val();
				let user_report = $( '#exms_user_report' ).val();
				let submitted_essays = $( '#exms_submitted_essays' ).val();
				let certificates = $( '#exms_certificates' ).val();
				let group = $( '#exms_qroup' ).val();
				let questions = $( '#exms_questions' ).val();
				let quizzes = $( '#exms_quizzes' ).val();

				let updatedDynamicLabels = {};
				let shouldSendDynamic = false;
				for( let key in dynamicLabels ) {
					let field = $( '#' + key );
					if ( field.length > 0 ) {
						updatedDynamicLabels[key] = field.val();
					}
				}
				for( let key in checkLabels ) {
					if( !( key in dynamicLabels ) ) {
						shouldSendDynamic = true;
						continue;
					}
					if( updatedDynamicLabels[key] !== checkLabels[key] ) {
						shouldSendDynamic = true;
					}
				}
				for( let key in dynamicLabels ) {
					if( !( key in checkLabels ) ) {
						shouldSendDynamic = true;
					}
				}

				if( quiz_report === original['exms_quiz_report'] && user_report === original['exms_user_report'] &&
					submitted_essays === original['exms_submitted_essays'] && certificates === original['exms_certificates'] &&
					group === original['exms_qroup'] && questions === original['exms_questions'] && quizzes === original['exms_quizzes'] && !shouldSendDynamic ) {
					return;
				}

				let nextWrap = $( '.exms-next-setup-wrap' );
				let nextChild = nextWrap.find( '.exms-flex-content-btn' );
				nextChild.addClass( 'loading' ); 

				let data = {
					'action': 'exms_wizard_save_labels',
					'security': EXMS_SETUP_WIZARD.security,
					'exms_quiz_report': quiz_report,
					'exms_user_report': user_report,
					'exms_submitted_essays': submitted_essays,
					'exms_certificates': certificates,
					'exms_qroup': group,
					'exms_questions': questions,
					'exms_quizzes': quizzes,
				};
				data['dynamic_labels'] = updatedDynamicLabels;
				jQuery.post( EXMS_SETUP_WIZARD.ajaxURL, data, function( resp ) {
					nextChild.removeClass( 'loading' ); 
					container.attr( 'data-dynamic-labels', JSON.stringify( updatedDynamicLabels ) );			
					container.attr( 'data-dynamic-label', JSON.stringify( updatedDynamicLabels ) );
					container.attr( 'data-label', JSON.stringify( {
						exms_quiz_report: quiz_report,
						exms_user_report: user_report,
						exms_submitted_essays: submitted_essays,
						exms_certificates: certificates,
						exms_qroup: group,
						exms_questions: questions,
						exms_quizzes: quizzes
					} ) );
				} );
			},

			/**
			 * Back to prev page.
			 */
			backToPrev: function() {

				$( 'body' ).on( 'click', '.exms-back-setup-wrap', function() {

					let self = $( this );
					let pageContainer = self.attr( 'data-redirect' );

					let pageNo = parseInt( pageContainer.replace( 'exms-setup-p', '' ) );
					let prevPage = pageNo - 1;
					let nextPage = prevPage + 1;

					let footerPage = 'exms-page'+nextPage;
					$( '.'+footerPage ).find( '.exms-no' ).css( {
						'background': '#552CA8'
					} );

					$( '.exms-page'+nextPage ).find( '.exms-progress-bar' ).css( {
						'border-color'  : '#EFF0F6'
					} );
					
					$( '.exms-back-setup-wrap' ).attr( 'data-redirect', 'exms-setup-p'+prevPage );
					$( '.exms-next-setup-wrap, .exms-finish-setup-wrap, .exms-skip-setup-wrap' ).attr( 'data-redirect', 'exms-setup-p'+nextPage );

					$( '.'+pageContainer ).show();

					$.each( $( '.exms-page' ), function( index, elem ) {
						
						let allPageID = parseInt( $( elem ).attr( 'class' ).replaceAll( 'exms-page', '' ) );	
						
						if( nextPage < allPageID  ) {
							$( '.exms-page'+allPageID ).find( '.exms-no' ).css( {
								'background': '#EFF0F6',
								'color': '#000'
							} );
						}

						if( allPageID != nextPage ) {
							$( '.exms-setup-p'+allPageID ).hide().change();
						}
					} );

					if( pageNo == 0 ) {
						$( '.exms-form-footer' ).hide().change();
						$( '.exms-setup-start' ).attr( 'data-start-page', 1 ).data( "start-page", 1 );
						let getAttr = $( '.exms-setup-start' ).data( "start-page" );
						EXMSSetupWizard.buttonDisplay( getAttr );
					}

					EXMSSetupWizard.switch_buttons( prevPage );
				} );
			},

			/**
			 * Removed Validation msg if field is not empty
			 */
			removeValidationMessage: function() {

				$( '.exms-email-input, .exms-license-input' ).on( 'change', function() {

                    let self = $( this );
                    let dateValue = self.val();
                        
                    if( dateValue == '' || ! dateValue ) {

                        self.parent( '.exms-input-wrap' ).find( '.exms-validate-msg' ).attr( 'style', '' );
                    } else {

                        self.parent( '.exms-input-wrap' ).find( '.exms-validate-msg' ).hide();
                    }
                } );
			},

			/**
			 * EXMS Setup Wizard
			 */
			setupWizardStartUP: function() {

				$( 'body' ).on( 'click', '.exms-start-setup', function() {

					let self = $( this );
					let parent = self.parents( '.exms-content-wrapper' );
					parent.find( '.exms-setup-p0' ).hide();
					parent.find( '.exms-setup-p1' ).show();
					$( '.exms-back-setup-wrap' ).attr( 'data-redirect', 'exms-setup-p0' );
					$( '.exms-next-setup-wrap, .exms-finish-setup-wrap, .exms-skip-setup-wrap' ).attr( 'data-redirect', 'exms-setup-p1' );
					$( '.exms-form-footer' ).css( 'display', 'inline' );
					$( '.exms-exit-setup' ).css( 'display', 'none' );
					$( '.exms-page1' ).find( '.exms-no' ).css( {
						'background': '#552CA8',
						'color': '#fff'
					} );
				} );
			},

			/**
			 * Setup form validate
			 */
			setupFormValidate: function() {

				$( 'body' ).on( 'click', '.exms-validate-license', function() {

					let error = 0;
					let self = $( this );
					let parent = self.parents( '.exms-setup-license' );

					let licenseKey = parent.find( '.exms-license-input' ).val();
					if( EXMSSetupWizard.formValidation( licenseKey, '.exms-license-input' ) ) {

						error++;
					}

					if( error != 0 ) {
                        return false;
                    }

					//working here after validate.

					self.addClass( 'is-validate' );
                    $( '.exms-next-setup, .exms-skip-setup, .exms-finish-setup' ).css( {
						'background': 'linear-gradient(90deg, rgba(222,18,45,1) 0%, rgba(253,28,56,1) 34%, rgba(240,34,58,1) 100%)'
					} );

				} );
			},

			/**
             * Create Validation message
             *
             * @param existsVal
             * @param parent
             * @param beforeClass
             */
            formValidation: function( existsVal, beforeClass ) {

                if( '' == existsVal || ! existsVal ) {

                    $( beforeClass ).parent( '.exms-input-wrap' ).find( '.exms-validate-msg' ).html( 'Please fill out this field.' );

                    return true;
                }
            },

			/**
			 * Rename lms structure steps name
			 */
			renameStructurePostType: function () {

				$( 'body' ).on( 'click', '.edit-structure-step-names', function ( e ) {
    				
					e.preventDefault();

    				let button = $( this );
					let structureCard = button.closest( '.exms-course-structure' );
					let stepsContainer = structureCard.find( '.exms-course-structure-steps' );

    				let isEditing = structureCard.data( 'editing' );

					if ( !isEditing ) {
						let steps = stepsContainer.data( 'structure-steps' );
						if ( !Array.isArray( steps ) ) steps = [];

						structureCard.data( 'original-steps', [...steps] );

						stepsContainer.empty();
						steps.forEach( ( step, index ) => {
							let indent = 20 * index;
							stepsContainer.append( `
								<input 
									type="text" 
									class="exms-inline-edit-step" 
									data-step-index="${index}" 
									value="${step}" 
									style="margin-left: ${indent}px;" />
							`);
						});

						stepsContainer.append( `
							<div class="step-edit-buttons">
								<button class="button save-step-changes">Change</button>
								<button class="button cancel-step-changes">Cancel</button>
							</div>
						` );

						structureCard.data( 'editing', true );
						button.addClass( 'editing' );
						button.hide().change();
					}
				});

				$( 'body' ).on( 'click', '.save-step-changes', function ( e ) {
					
					e.preventDefault();

					let stepsContainer = $( this ).closest( '.exms-course-structure-steps' );
					let structureCard = $( this ).closest( '.exms-course-structure' );

					let updatedSteps = [];
					stepsContainer.find( '.exms-inline-edit-step' ).each( function () {
						updatedSteps.push( $( this ).val().trim() );
					});

					stepsContainer.data( 'structure-steps', updatedSteps ).attr( 'data-structure-steps', JSON.stringify( updatedSteps ) );
					structureCard.data( 'editing', false );

					stepsContainer.empty();
					updatedSteps.forEach( step => {
						stepsContainer.append( `<p>${step}</p>` );
					});

					structureCard.find( '.edit-structure-step-names' ).removeClass( 'editing' ).show().change();
				});

				$( 'body' ).on( 'click', '.cancel-step-changes', function ( e ) {
					e.preventDefault();

					if ( !confirm( 'Are you sure you want to discard your changes?' ) ) return;

					let structureCard = $( this ).closest( '.exms-course-structure' );
					let stepsContainer = structureCard.find( '.exms-course-structure-steps' );

					let originalSteps = structureCard.data( 'original-steps' ) || [];

					stepsContainer.empty();
					originalSteps.forEach( step => {
						stepsContainer.append( `<p>${step}</p>` );
					});

					structureCard.data( 'editing', false );
					structureCard.find( '.edit-structure-step-names' ).removeClass( 'editing' ).show().change();
				});


				$( document ).on( 'click', '.exms-success-message .close-btn', function () {

					$( this ).closest('.exms-success-message').empty().hide().change();
				});
				
				$( 'body' ).on( 'click', '#exms-edit-course-structure-close-btn', function () {
					
					$( '#exms-edit-course-structure-modal' ).hide().change();
				} );
			},			
		}

		EXMSSetupWizard.init();
	});
})( jQuery );