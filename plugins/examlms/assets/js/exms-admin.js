( function( $ ) { 'use strict';

	$( document ).ready( function() {

		let EXMSadmin = {
			
			/**
			 * Initialize functions on load
			 */
			init: function() {

				this.initializeQuestionTimerField();
				this.pointBalanceToggle();
				this.savePointBalanceValue();
				this.toggleReportsFilterField();
				this.addResultReapeter();
				this.uploadBadgesImage();
				this.diaplayQuizTabContent();
				this.updateTaxonomies();
				this.deleteTaxonomies();
				this.quizEditTaxonomies();
				this.changeExportPostType();
				this.changeTexttoProperSlug();
				this.deleteCertificate();
				this.makeAsSelectedPDF();
				this.updatePDFContent();
				this.enqueueColorPicker();
				this.removeCertificate();
				this.changeCertificateTemplateType();
				this.uploadLogoFromURL();
				this.removeEmailLogo();
				this.exms_settings_tabs_form();
			},
			/**
			 * remove email logo 
			 */
			exms_settings_tabs_form: function() {

				$( document ).on( 'submit', '#exms_settings_tabs_form', function() {  

					var tab = $('#exms_tab_type').val();
					var form = $( this );
					var no_error = true;
					switch( tab ) {
						case "exms_stripe_payment-integration":
							if ( $( '.exms-stripe-enable:checked' ).val() === 'on' ) {
								
								if( $.trim( $( '.exms-stripe-redirects-complete' ).val() ) == "" ) {
									no_error = false;
									$( '.exms-stripe-redirects-complete' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
								} else {
									$('.exms-stripe-redirects-complete' ).css( 'background-color', '#FFFFFF' );
									no_error = true;
								}
								
								if( $.trim( $( '.exms-stripe-redirects-cancel' ).val() ) == "" ) {
									no_error = false;
									$( '.exms-stripe-redirects-cancel' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
								} else {
									$('.exms-stripe-redirects-cancel' ).css( 'background-color', '#FFFFFF' );
									no_error = true;
								}

								if ( $.trim( $( '.exms-stripe-currency' ).val() ) === "" ) {
									$( '.exms-stripe-currency' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
									no_error = false;
								} else {
									$( '.exms-stripe-currency' ).css( {
										'border': '',
										'outline': ''
									} );
								}

								if ( $.trim( $( '.exms-stripe-payee-email' ).val() ) === "" ) {
									$( '.exms-stripe-payee-email' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
									no_error = false;
								} else {
									$( '.exms-stripe-payee-email' ).css( {
										'border': '',
										'outline': ''
									} );
								}

							} else {
								$( '.exms-stripe-currency' ).css( 'background-color', '#FFFFFF' );
								$( '.exms-stripe-payee-email' ).css( 'background-color', '#FFFFFF' );
								no_error = true;
							}
						break;

						case "exms_paypal_payment-integration":
							
							if( $('.exms-paypal-enable:checked').val() == "on" ) {
								
								if( $.trim( $( '.exms-paypal-redirects-complete' ).val() ) == "" ) {
									no_error = false;
									$( '.exms-paypal-redirects-complete' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
								} else {
									$('.exms-paypal-redirects-complete' ).css( 'background-color', '#FFFFFF' );
									no_error = true;
								}
								
								if( $.trim( $( '.exms-paypal-redirects-cancel' ).val() ) == "" ) {
									no_error = false;
									$( '.exms-paypal-redirects-cancel' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
								} else {
									$('.exms-paypal-redirects-cancel' ).css( 'background-color', '#FFFFFF' );
									no_error = true;
								}
								
								if( $.trim( $( '.exms-paypal-currency' ).val() ) == "" ) {
									no_error = false;
									$( '.exms-paypal-currency' ).css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
								} else {
									$( '.exms-paypal-currency' ).css( 'background-color', '#FFFFFF' );
									no_error = true;
								}
							
								if( $.trim( $( '.exms-paypal-payee-email').val() ) == "" ) {
									no_error = false;
									$('.exms-paypal-payee-email').css( {
										'border': '1px solid red',
										'outline': '1px solid red'
									} ).focus();
								} else {
									$( '.exms-paypal-payee-email' ).css( 'background-color', '#FFFFFF' );
									no_error = true;
								}

							} else {
								no_error = true;
								$( '.exms-paypal-redirects-complete' ).css( 'background-color', '#FFFFFF' );
								$( '.exms-paypal-redirects-cancel' ).css( 'background-color', '#FFFFFF' );
								$( '.exms-paypal-currency' ).css( 'background-color', '#FFFFFF' );
								$( '.exms-paypal-payee-email' ).css( 'background-color', '#FFFFFF' );
							}
						break;
					}

					return no_error;
				});
			},
			/**
			 * remove email logo 
			 */
			removeEmailLogo: function() {

				$( document ).on( 'click', '.exms-remove-image', function() {
					$( '.exms-display-image' ).attr( 'src', ' ' );
					$('input[name="exms_email_logo_url"]').val( '' );
					$( '.exms-set-setting-image' ).show();
					$( '.exms-remove-image' ).hide();
				} );
			},

			/**
			 * Upload the from url and set
			 */
			uploadLogoFromURL: function() { 
				
				let mediaUploaders;

				$( 'body' ).on( 'click', '.exms-set-setting-image', function(e) {

					e.preventDefault();

					if( mediaUploaders ) {
						mediaUploaders.open();
						return;
					}

					mediaUploaders = wp.media.frames.file_frame = wp.media({
						title: 'Choose a images',
						button: {
							text: 'Choose Picture'
						},
						multiple: false
					});

					let attachment = '';

					mediaUploaders.on('select', function() {

						attachment = mediaUploaders.state().get('selection').first().toJSON();
						let CertificateURL =  attachment.url;
						$('input[name="exms_email_logo_url"]').val(CertificateURL);
					} );
					mediaUploaders.open();
				});
			},
			/**
			 * change certificate template type
			 */
			changeCertificateTemplateType: function() {

				$( 'body' ).on( 'change', '.exms-pdf-orientation', function() {
					let TempType = $( '.exms-pdf-orientation' ).val();
					
					if( 'l' == TempType ) {
						$( '#exms-pdf-template-wrap' ).css( 'height', '85vh' );
						$( '#exms-pdf-template-wrap' ).css( 'width', '100%' );
						$( '.exms-pdf-header' ).css( 'padding', '15% 10% 0' );
						$( '.exms-pdf-footer' ).css( 'padding', '6% 0' );
					}

					if( 'p' == TempType ) {
						$( '#exms-pdf-template-wrap' ).css( 'height', '100%' );
						$( '#exms-pdf-template-wrap' ).css( 'width', '85%' );
						$( '.exms-pdf-header' ).css( 'padding', '20% 20% 14% 20%' );
						$( '.exms-pdf-footer' ).css( 'padding', '13% 0' );
					}
				} );
			},

			/**
			 *	remove certificate
			 */
			removeCertificate: function() {

				$( 'body' ).on( 'click', '.exms-certificate-remove', function() {
					$(this).parent( '.exms-cert-img' ).remove();
					$(this).parent( '.exms-certificate-sign-wrap' ).remove();
				} );
			},
			/**
			 * Enqueue color picker
			 */
			enqueueColorPicker: function() {

			 	if( ! $( 'input' ).hasClass( 'exms-color-picker' ) ) {
			 		return false;
			 	}

				$( '.exms-color-picker' ).wpColorPicker();

				jQuery( document ).ready( function( $ ){

					$('.exms-color-picker').iris( {
						hide: true,
						palettes: true,
						change: function( event, ui ) {
							let self = $( this );
							let parent = self.parents( '.wp-picker-container' );
							parent.find( '.wp-color-result' ).css( 'background-color', ui.color.toString() );
							let getAttr = self.attr( 'data-target' );

							if( 'title' == getAttr ) {
								$( '.exms-pdf-title' ).css( {
									'color'	: ui.color.toString()
								} );
							}

							if( 'username' == getAttr ) {
								$( '.exms-pdf-iner-title' ).css( {
									'color'	: ui.color.toString()
								} );
							}
						}
					});
				});

			},

			updatePDFContent: function() {

				/**
				 * update pdf title
				 */
				$( '.exms-certificate-title .exms-title-input' ).keydown( function() {
					
					let title = $( this ).val();
					$( '.exms-pdf-title' ).html( title );
				} );

				$( '.exms-certificate-title .exms-title-input' ).keyup( function() {
					
					let title = $( this ).val();
					$( '.exms-pdf-title' ).html( title );
				} );

				/**
				 * update user name
				 */
				$( '.exms-certificate-user-content .exms-name-input' ).keydown( function() {
					
					let innertitle = $( this ).val();
					$( '.exms-pdf-iner-title' ).html( innertitle );
				} );

				$( '.exms-certificate-user-content .exms-name-input' ).keyup( function() {
					
					let innertitle = $( this ).val();
					$( '.exms-pdf-iner-title' ).html( innertitle );
				} );

				/**
				 * change fontsize of a title
				 */
				$( '.exms-t-f-size' ).on( 'input', function() {
					
					let self = $( this );
					let val = self.val();
					$( '.exms-pdf-title' ).css( 'fontSize', val+'px' );
				} );

				/**
				 * change fontsize of a title
				 */
				$( '.exms-u-f-size' ).on( 'input', function() {
					
					let self = $( this );
					let val = self.val();
					$( '.exms-pdf-iner-title' ).css( 'fontSize', val+'px' );
				} );
			},

			/**
			 * Make selected PDF when admin click on PDF 
			 */
			makeAsSelectedPDF: function() {

				$( 'body' ).on( 'click', '.exms-cert-img img', function() {

					$( '#exms-pdf-template-wrap' ).slideDown( 'slow' );
					let self = $( this );
					$( '.exms-certificate-hidden' ).attr( 'name', '' );
					$( '.exms-cert-img' ).css( 'background', '' );
					$( '.exms-updated-dertificate' ).css( 'background-color', 'white' );
					
					let src = self.attr( 'bgimage' );
					$( '#exms-pdf-template-wrap' ).css({'background-image':'url('+src+')' } );
					let parent = self.parents( '.exms-cert-img' );
					let val = parent.find( '.exms-certificate-hidden' ).val();
					parent.find( '.exms-certificate-hidden' ).attr( 'name', 'exms_selected-certificate' );
					parent.css( 'background', 'lightgray' );
				} );
			},

			/**
			 * Remove Certificate
			 */
			deleteCertificate: function() {

				$( 'body' ).on( 'click', '.exms-delete-certificate', function() {

					let self = $( this );
					self.parents( '.exms-certificate-repeater' ).remove();
				} );
			},

			/**
			 * Change text to proper slug on point type slug
			 */
			changeTexttoProperSlug: function() {

				$( 'body' ).on( 'input', '.exms-point-type-input', function() {

					let self = $( this );
					var value = self.val();
  					self.val( value.replace( / /g, '-' ) );
				} );
			},	

			/**
			 * Change on Export post type
			 */
			changeExportPostType: function() {

				$( 'body' ).on( 'change', '.exms-select-post-type', function() {

					let self = $(this);
					let postType = self.val();

					if( 'exms_post_type' == postType ) {
						return false;
					}

					$( '.exms-post-to-export' ).html( '' );
					$( '.exms-export-loader' ).show();

					let data = {
						'action'  	: 'exms_generate_exp_post_type',
						'post_type' : postType
					};

					jQuery.post( ajaxurl, data, function( response ) {
						
						$( '.exms-export-loader' ).hide();
						$( '.exms-post-to-export' ).html( response );
					});
				} );
			},

			/**	
			 * Quick edit taxonomies 
			 */
			quizEditTaxonomies: function() {

				$( 'body' ).on( 'click', '.exms-quick-edit-taxonomy', function() {

					let self = $(this);
					let parent = self.parents( 'tr' );
					let twoo = parent.siblings( '.exms-quick-edit-wrap' ).find( '.exms-quick-edit-row' ).parent( 'td' ).hide();
					$( self ).parents( '#the-list' ).find( 'tr' ).attr( 'style', '' );
					let name = parent.find( '.exms-taxonomy-id' ).attr( 'data-taxo-name' );
					let slug = parent.find( '.slug' ).text();
					let id = parent.find( '.exms-taxonomy-id' ).attr( 'data-texo-id' );
					let taxonomy = parent.find( '.exms-taxonomy-id' ).attr( 'data-taxonomy' );
					let assetsURL = self.attr( 'data-assets-url' );

					parent.hide();

					let html = '';
					html = '<tr class="hidden"></tr>';
					html += '<tr class="exms-quick-edit-wrap">';
					html += '<td colspan="5">';
					html += '<div class="exms-quick-edit-row">';
					html += '<img class="exms-quick-loader" src="'+assetsURL+'imgs/spinner.gif ">';
					html += '<span class="exms-quick-heading">Quick Edit</span>';
					html += '<div class="exms-quick-form">';
					html += '<label class="exms-quick-title">Name :</label>';
					html += '<input class="exms-quick-name" type="text" value="'+name+'">';
					html += '</div>';
					html += '<div class="exms-quick-form">';
					html += '<label class="exms-quick-title">Slug :</label>';
					html += '<input class="exms-quick-slug" type="text" value="'+slug+'">';
					html += '</div>';
					html += '<div class="exms-quick-form">'
					html += '<input class="button-secondary exms-quick-edit-cancel" type="button" value="Cancel">';
					html += '<input data-taxonomy="'+taxonomy+'" data-taxo-id="'+id+'" class="button-primary exms-quick-edit-update" type="button" value="Update">';
					html += '</div>';
					html += '</div>';
					html += '</td>';
					html += '</tr>';

					parent.after( html );
				} );

				$( 'body' ).on( 'click', '.exms-quick-edit-cancel', function() {

					let self = $( this );

					self.parents( '#the-list' ).find( 'tr' ).show( 400 );
					self.parents( '.exms-quick-edit-row' ).hide(); 
				} )

				$( 'body' ).on( 'click', '.exms-quick-edit-update', function() {

					let self = $(this);
					let taxoID = $( self ).attr( 'data-taxo-id' );
					let taxonomy = $( self ).attr( 'data-taxonomy' );
					let name = $( '.exms-quick-name' ).val();
					let slug = $( '.exms-quick-slug' ).val();

					$( '.exms-quick-loader' ).show();

					let data = {
						'action'  	: 'exms_quick_edit_taxonomies',
						'taxo_id'	:  taxoID,
						'name'		:  name,
						'slug'		:  slug,
						'taxonomy'	: taxonomy
					};

					jQuery.post( ajaxurl, data, function( response ) {
						$( '.exms-quick-loader' ).hide();
						var url = document.location.href+"&message=updated";
      					document.location = url;
					});
				} )
			},

			/**
			 * Delete Taxonomy
			 */
			deleteTaxonomies: function() {

				/**	
				 * Delete single taxonomy
				 */

				$( 'body' ).on( 'click', '.exms-delete-taxonomy', function() {
					
					if ( confirm('Are you sure, you want to delete ?') ) {

						let self = $(this);
						let id = self.parents( 'tr' ).find( '.exms-taxonomy-id' ).data( 'texo-id' );
						let taxo = self.parents( 'tr' ).find( '.exms-taxonomy-id' ).attr( 'data-taxonomy' );

						let data = {
							'action'  	: 'exms_delete_taxonomy',
							'id'		: id,
							'taxo'		:  taxo
						};

						jQuery.post( ajaxurl, data, function( response ) {
							var url = document.location.href+"&message=updated";
  							document.location = url;
						});
					}
				});

				/**
				 * Delete multiple taxonomy
				 */
				if( $( '.exms-bulk-btn' ).length > 0 ) {

				 	$( '.exms-bulk-btn' ).click( function() {
				 		
				 		let taxonomySelectBox = $( '.exms-select-taxonomy-id' ).val();
						
						if( taxonomySelectBox ) {

				 			if ( confirm('Are you sure you want to delete ?') ) {

								let CheckedId = [];
								$.each( $( '.exms-select-taxonomy' ), function(index, elem) {

									if( $( elem ).prop( 'checked' ) ) {

										let bulkID = $( elem ).parents( 'tr' ).find( '.exms-taxonomy-id' ).data( 'texo-id' );
										CheckedId.push( bulkID );
									}
								});

								let taxonomy = $( this ).parents( '.exms-taxonomy-data-table' ).find( '.exms-taxonomy-id' ).attr( 'data-taxonomy' );
								
								let data = {
									'action'  	: 'exms_delete_taxonomy',
									'checkedID'	:  CheckedId,
									'taxo'  	:  taxonomy
								};

								jQuery.post( ajaxurl, data, function( response ) {
									var url = document.location.href+"&message=updated";
      								document.location = url;
								});
							}
						} else {
							alert( 'Please select any option to perform bulk action' );
						}
				 	});
				}				
			},

			/**
			 * Update Taxonomies using pop up 
			 */
			updateTaxonomies: function() {

				$( 'body' ).on( 'click', '.exms-edit-taxonomy', function() {

					let self = $(this);
					let parent = self.parents( 'tr' );
					
					/**
					 *  Get column values 
					 */
					let id = parent.find( '.exms-taxonomy-id' ).attr( 'data-texo-id' );
					let taxonomy = parent.find( '.exms-taxonomy-id' ).attr( 'data-taxonomy' );
					let name = parent.find( '.exms-taxonomy-id' ).attr( 'data-taxo-name' );
					let desc = parent.find( '.description' ).text();
					let slug = parent.find( '.slug' ).text();
					let parentID = parent.find( '.exms-taxonomy-id' ).attr( 'data-parent-id' );
					if( 0 == parentID ) {
						parentID = 'None';
					}
					$( '.exms-update-parent-cat' ).val( parentID );

					/**
					 * Put the column values in popup form
					 */
					$( '.exms-taxonomy-id' ).val( id );
					$( '.exms-parent-id' ).val( parentID );
					$( '.exms-taxonomy' ).val( taxonomy );
					$( '.exms-update-name' ).val( name );
					$( '.exms-update-slug' ).val( slug );
					$( '.exms-update-desc' ).val( desc );
				} );

				if( $( '.exms-update-taxonomy' ).length > 0 ) {

					$( '.exms-update-taxonomy' ).on( 'click', function() {

						let self = $( this );
						self.val( 'Processing...' );
						self.attr( 'disabled', 'disabled' );

						let texoID = $( '.exms-taxonomy-id' ).val();
						let taxonomy = $( '.exms-taxonomy' ).val();
						let updateName = $( '.exms-update-name' ).val();
						let updateSlug = $( '.exms-update-slug' ).val();
						let updateDesc = $( '.exms-update-desc' ).val();
						let updateParent = $( '.exms-update-parent-cat' ).val();
						
						let data = {
							'action'  		: 'exms_update_taxonomies',
							'exms_id'  		: texoID,
							'exms_name'		: updateName,
							'exms_slug'		: updateSlug,
							'exms_desc'		: updateDesc,
							'exms_taxonomy'  : taxonomy,
							'exms_parent'	: updateParent
						};

						jQuery.post( ajaxurl, data, function( response ) {
							var url = document.location.href+"&message=updated";
      						document.location = url;
						} );	
					} );					
				}
			},

			/**
			 * Display Quiz Tab Content
			 */
			diaplayQuizTabContent: function() {

				/**	
				 * change wordpress class ( inside ) style
				 */
				if( $( '.exms-setting-tab-wrapper' ).length > 0 ) {

					$( '.exms-setting-tab-wrapper' ).parent( '.inside' ).css( {'padding':'0', 'margin-top':'0'} );
				}

				if ($('.exms-tab-title.exms-active-tab').val() === 'course-video') {
					$('.exms-course-video-content').show().change();
				}
				
				if ($('.exms-tab-title.exms-active-tab').val() === 'quiz-video-url') {
					$('.exms-course-video-content').show().change();
					$('.exms-group-type-content').hide().change();
				}
				if ($('.exms-tab-title.exms-active-tab').val() === 'group-type') {
					$('.exms-group-type-content').show().change();
					$('.exms-course-video-content').hide().change();
				}

				if( $( '.exms-tab-title' ).length > 0 ) {

					$( '.exms-tab-title' ).click( function() {
						
						let self = $( this );
						$( '.exms-tab-title' ).css( {'background': '#ffffff', 'border-left': 'none' } );	
						self.css( { 'background': '#D9DDFD40','border-left': '4px solid #552CA8' } );

						$( '.exms-quiz-type-content' ).hide();

						switch ( self.val() ) {

							case 'group-type':
								$('.exms-group-type-content').show().change();
								$('.exms-course-video-content').hide().change();
							break;
							case 'quiz-type':
								$( '.exms-quiz-type-content' ).show().change();
								$( '.exms-quiz-setting-content, .exms-quiz-achievement-content, .exms-quiz-message-content, .exms-quiz-result-content, .exms-course-video-content, .exms-progress-type-content' ).hide().change();
							break;

							case  'quiz-setting':
								$( '.exms-quiz-setting-content' ).show().change();
								$( '.exms-quiz-type-content, .exms-quiz-achievement-content, .exms-quiz-message-content, .exms-quiz-result-content, .exms-course-video-content, .exms-progress-type-content' ).hide().change();
								$( '.exms-achivement-attched' ).hide().change();
							break;

							case 'quiz-achivement':
								$( '.exms-quiz-achievement-content' ).show().change();
								$( '.exms-quiz-type-content, .exms-quiz-setting-content, .exms-quiz-message-content, .exms-quiz-result-content, .exms-course-video-content, .exms-progress-type-content' ).hide().change();
								$( '.exms-achivement-attched' ).show().change();
							break;
							case 'quiz-result':
								$( '.exms-quiz-result-content' ).show().change();
								$( '.exms-quiz-type-content, .exms-quiz-setting-content, .exms-quiz-message-content, .exms-quiz-achievement-content, .exms-course-video-content, .exms-progress-type-content' ).hide().change();
								$( '.exms-achivement-attched' ).show().change();
							break;
							
							case 'quiz-message':
								$( '.exms-quiz-message-content' ).css( 'display', 'inline-block' );
								$( '.exms-course-video-content, .exms-progress-type-content, .exms-quiz-type-content, .exms-quiz-setting-content, .exms-quiz-achievement-content, .exms-quiz-result-content' ).hide().change();
							break;

							case 'course-video':
								$( '.exms-course-video-content' ).show().change();;
								$( '.exms-quiz-type-content, .exms-quiz-setting-content, .exms-quiz-achievement-content, .exms-quiz-result-content, .exms-progress-type-content' ).hide().change();
							break;
							case 'quiz-video-url':
								$( '.exms-course-video-content' ).show().change();;
								$( '.exms-quiz-type-content, .exms-group-type-content, .exms-quiz-setting-content, .exms-quiz-achievement-content, .exms-quiz-result-content, .exms-progress-type-content' ).hide().change();
							break;
							case 'progress-settings':
								$( '.exms-progress-type-content' ).show().change();;
								$( '.exms-quiz-type-content, .exms-quiz-setting-content, .exms-quiz-achievement-content, .exms-quiz-result-content,.exms-course-video-content' ).hide().change();
							break;
						}
					});
				}
			},	

			/**
			 * Upload/remove badges image
			 */
			uploadBadgesImage: function() {

				/**
                 * Set certificate image
                 */
                let mediaUploaders;

                $( 'body' ).on( 'click', '.exms-set-certificate', function( e ) {

                    e.preventDefault();

                    let self = $(this);
                    let postId = self.attr( 'data-post_id' );

                    if( mediaUploaders ) {
                        mediaUploaders.open();
                        return;
                    }

                    mediaUploaders = wp.media.frames.file_frame = wp.media({
                    title: 'Choose a images',
                        button: {
                        text: 'Choose Picture'
                    },
                        multiple: false
                    });

                    let attachment = '';

                    mediaUploaders.on('select', function() {
                    	
                    	$( '.exms-set-certificate' ).attr( 'data-image-uploaded', 'true' );
                    	attachment = mediaUploaders.state().get('selection').first().toJSON();
                    	let CertificateURL =  attachment.url;

                    	$( '.exms-save-image-value-'+postId+'' ).after('<div class="exms-cert-img"><img src="' + attachment.url + '" class="exms-display-image"></div>').next().val( attachment.id ).next().show();
                    	$( '.exms-metabox-container-backimg' ).before( '<input type="hidden" class="exms-hidden-certificate-image-'+postId+'" name="exms_certificate_image_url[]" value="'+ attachment.url +'">' );
                    } );
                    mediaUploaders.open();
                } );

                /**
                 * set certificate sign
                 */
                $( 'body' ).on( 'click', '.exms-certificate-sign', function( e ) {

                	e.preventDefault();

                	let self = $(this);
                	let postId = self.attr( 'data-post_id' );

                	if( mediaUploaders ) {
                		mediaUploaders.open();
                		return;
                	}

                	mediaUploaders = wp.media.frames.file_frame = wp.media({
                		title: 'Choose a Signature',
                		button: {
                			text: 'Choose Signature'
                		},
                		multiple: false
                	});

                	let attachment = '';

                	mediaUploaders.on('select', function() {

                		attachment = mediaUploaders.state().get('selection').first().toJSON();
                		let CertificateURL =  attachment.url;
                		let img = '<img src="'+CertificateURL+'">';
                		$( '.exms-certificate-sign-wrap img' ).remove();
                		$( '.exms-metabox-container-signed' ).html( img );
                		$( '.exms-metabox-container-signed' ).after( '<input type="hidden" name="exms_certificate-signed" value="'+CertificateURL+'">' );
                		$( '.exms-pdf-date img' ).remove();
                		$( '.exms-pdf-date' ).html( '<img src="'+CertificateURL+'">' );
                	} );
                	mediaUploaders.open();

                } );
			},

			/**
			 * Fetch data to add multiple tags
			 */
			addResultReapeter: function( e ) {

				$( 'body' ).on( 'click', '.exms-add-certificate', function() {

					let options = [];
					$(".exms-certificate-repeater option").each(function() {
					    
					    let optionHtml = $(this).html();
					    let certificateID = $(this).val();
					    options[certificateID] = optionHtml;
					});

					let certHtml = '<select class="exms-certificates">';
					certHtml += '<option value="">Select a certificate id</option>';
					$.each( $( options ), function( i,elem ) {
						
						if( elem ) {
							certHtml += '<option value="'+i+'">'+elem+'</option>';
						}
					});
					certHtml += '</select>';
					certHtml += '<span class="dashicons dashicons-no exms-delete-certificate"></span>';
					$( '.exms-certificate-repeater:last' ).after( '<div class="exms-certificate-repeater">'+certHtml+'</div>' );
				} );

				$( 'body' ).on( 'change', '.exms-certificates', function() {

					let certificate = $( '.exms-certificates' ).val();
				
					if( certificate ) {

	                    let self = $( this );
	                    let val = self.val();
						self.attr( 'name', 'exms_quiz-certificates-'+val );
					}
				} );
			},

			/**
			 * Remove array in hidden field 
			 */
			removeFieldValues: function( array, to_remove ) {

				let elements = array.split( ',' );
				let remove_index = elements.indexOf( to_remove );
				elements.splice( remove_index, 1 );
				let result = elements.join( ',' );
				return result;
			},

			/**
			 * Toggle reports input fields
			 */
			toggleReportsFilterField: function() {

				if( $( '.exms-filter-reports' ).length > 0 ) {

					$( '.exms-filter-reports' ).on( 'change', function() {

						switch( $( this ).val() ) {

							case 'date':
								$( '.exms-filter-dates' ).show();
								$( '.exms-filter-field' ).addClass( 'exms-hide' );
								break;

							case 'quiz_name':
								$( '.exms-filter-dates' ).hide();
								$( '.exms-filter-field' ).removeClass( 'exms-hide' );
								break;
							
							default:
								$( '.exms-filter-field' ).removeClass( 'exms-hide' );
								break;
						}
					} );
				}
			},

			/**
			 * Initialize question timer field
			 */
			initializeQuestionTimerField: function() {

				if( $( '.wpeq-sel-time-btn' ).length > 0 ) {

					$( '.wpeq-sel-time-btn' ).on( 'click', function( e ) {

						e.preventDefault();
						
						$( '.wpeq-ques-timer-table' ).toggle();	
					});
				}

				if( $( '.wpeq-timer-range' ).length > 0 ) {

					$( '.wpeq-timer-range' ).on( 'input', function() {

						$( this ).parent().siblings( '.wpeq-ques-sel-timer-value' ).html( $( this ).val() );
					});

					$( '.wpeq-set-ques-time-btn' ).on( 'click', function( e ) {

						e.preventDefault();
						let hours = $( '.wpeq-ques-hours' ).val(),
							mins = $( '.wpeq-ques-mins' ).val(),
							secs = $( '.wpeq-ques-secs' ).val();

						$( '.wpeq-ques-timer' ).val( hours + ':' + mins + ':' + secs );
						$( this ).parents( '.wpeq-ques-timer-table' ).hide();
					});
				}

				if( $('.wpeq-timer-input-td').length > 0 ) {

					$.each( $('.wpeq-timer-input-td'), function( index, td ) {

						let timer_val = $( td ).find( '.wpeq-timer-range' ).val();
						$( td ).siblings( '.wpeq-ques-sel-timer-value' ).html(timer_val);
					});
				}

				$( '.wpeq-ques-hours, .wpeq-ques-mins, .wpeq-ques-secs' ).on( 'input', function () {
					const h = $( '.wpeq-ques-hours' ).val() || '00';
					const m = $( '.wpeq-ques-mins' ).val() || '00';
					const s = $( '.wpeq-ques-secs' ).val() || '00';
					$( '.wpeq-ques-timer' ).val( `${h}:${m}:${s}` );
				});

				const hInit = $( '.wpeq-ques-hours' ).val() || '00';
				const mInit = $( '.wpeq-ques-mins' ).val() || '00';
				const sInit = $( '.wpeq-ques-secs' ).val() || '00';
				$( '.wpeq-ques-timer' ).val( `${hInit}:${mInit}:${sInit}` );

				$('.wpeq-reset-btn').on('click', function(e) {
					e.preventDefault();
					$( '.wpeq-ques-hours, .wpeq-ques-mins, .wpeq-ques-secs' ).val( '00' );
					const h = '00', m = '00', s = '00';
					$( '.wpeq-ques-timer' ).val( `${h}:${m}:${s}` );
				});
			},

			/**
			 * Toggle point balance input field
			 */
			pointBalanceToggle: function() {

				if( $( '.exms-profile-point-toggle' ).length > 0 ) {

					$( '.exms-profile-point-toggle' ).on( 'click', function() {
						
						let parent = $( this ).parents( '.exms-point-type-box' );
						$( parent ).find( '.exms-profile-point-toggle' ).css( 'display', 'none' );
						$( parent ).find( '.exms-new-balance' ).slideToggle();
					} );				
				}
				
				if( $( '.exms-cancel' ).length > 0 ) {

					$( '.exms-cancel' ).on( 'click', function() {
						
						let parent = $( this ).parents( '.exms-point-type-box' );
						$( parent ).find( '.exms-profile-point-toggle' ).css( 'display','block' );
						$( parent ).find( '.exms-new-balance' ).slideToggle();
					} );
				}
			},

			/**
			 * Save point balance value
			 */
			savePointBalanceValue: function() {

				$( 'body' ).on( 'click', '.exms-save', function() {

					let self = $( this );
					let parent = self.parents( '.exms-point-type-box' );
					let prevBalance = parseInt( parent.attr( 'data-point-balance' ) );
					let totalPoints = parseInt( $( parent ).find( '.new-balance' ).val() );
					let p_type = $( parent ).find( '.exms-point-type' ).val();
					let userID  = $( '.exms-current-id' ).val();
					$( parent ).find( '.exms-profile-point-toggle' ).css( 'display','block' );
					$( parent ).find( '.exms-new-balance' ).slideToggle();
					$( parent ).find( '.exms-balance .exms-display-point-balance' ).html( totalPoints );
				
					let manualPoints = totalPoints - prevBalance;

					let data = {
						'action'     	: 'exms_point_balance',
						'security'		: EXMS.security,
						'total_points'  : totalPoints,
						'manual_points'	: manualPoints,
						'user_id'    	: userID,
						'point_type'    : p_type	
					};

					jQuery.post( EXMS.ajaxURL, data, function( resp ) {

                        let response = JSON.parse( resp );
                        if( response.status == 'false' ) {
                        	$.alert( response.message );
                        } else {}
                    } );
				} );
			}
		}

		EXMSadmin.init();
	});

})( jQuery );