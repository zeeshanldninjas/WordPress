( function( $ ) { 'use strict';

	$( document ).ready( function() {
		
		let EXMSdragDrop = {
			
			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.createTable();
				this.dragDropPostItems();
				this.searchPostRelationItems();
				this.searchNextBackPageItems();
				this.toggleProgressDetailItems();
				this.changeOnProgressCheckBox();
				this.toggleParentPriceFields();
				this.toggleDotsDropdown();
				this.assignUserRole();
				this.assignParentUserRole();
				this.assignPostRelation();
				this.iconPostRelation();
				this.iconUser();
				this.iconInstructorUser();
			},

			/**
             * Create table if not exist
             */
            createTable: function() {
                
                $( document ).on( 'click', '.create-tables-link', function( e ) {
                    e.preventDefault();

                    if ( !confirm( EXMS_POST_STRUCTURE.confirmation_text ) ) {
                        return; 
                    }
                    
                    let $this = $( this );
                    $this.prop( 'disabled', true ).text( EXMS_POST_STRUCTURE.processing );
        
                    let action = $( this ).data( 'action' );
                    let tables = $( this ).data( 'tables' );
            
                    $.ajax({
                        url: EXMS_POST_STRUCTURE.ajaxURL,
                        type: 'POST',
                        data: {
                            action: action,
                            tables: JSON.stringify( tables ),
                            nonce: EXMS_POST_STRUCTURE.create_table_nonce
                        },
                        success: function( response ) {
                            if ( response.success ) {
								
								let messageParent = $( '.exms-table-creation-message' ).removeClass( 'notice-error' ).addClass( 'notice-success' );
                                $( '.exms-para-content' ).html( response.data ).show().change();
                                $( '.exms-para' ).removeClass( 'exms-notice-error' ).hide().change();
                            } else {
                                let message = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
    							alert( message );
                                $this.prop( 'disabled', false ).text( EXMS_POST_STRUCTURE.create_table ).change();
                            }
                        }
                    });
                });
            },

			/**
			 * Change on progress checkbox
			 */
			changeOnProgressCheckBox: function() {

				$( '.exms-progress-check-box' ).change(function() {

					let self = $( this );
					let parent = self.parent( '.exms-progress-content' );
					let grandParent = parent.parent( '.exms-progress-post-wrap' );

			        if( self.is( ':checked' ) ) {

			        	$.each( $( grandParent.find( '.exms-progress-check-box' ) ), function( index, elem ) {

			        		$( elem ).prop( 'checked', true );
			        	} );
			        } else {
			        	
			        	$.each( $( grandParent.find( '.exms-progress-check-box' ) ), function( index, elem ) {

			        		$( elem ).prop( 'checked', false );
			        	} );

			        	$.each( self.parents( '.exms-progress-post-wrap.exms-progress-wrap-padding' ), function( index, elem ) {
			        		
			        		let checkboxs = $( elem ).find( '.exms-progress-check-box' );
			        		let parentIDs = self.attr( 'data-parent' );
		        			let parentArray = parentIDs.split( '-' );
		        			let postID = $( checkboxs ).attr( 'data-post-id' );

		        			$( elem ).find( 'input[data-post-id="'+postID+'"]' ).prop( 'checked', false );
		        			$( '.exms-all-completed' ).prop( 'checked', false );
			        	} );
			        }
			    } );
			},

			/**
			 * Toggle progress detail items
			 */
			toggleProgressDetailItems: function () {

				$( 'body' ).on( 'click', '.exms-progress-icon', function() {

					let self = $( this );
					let assetsURL = self.attr( 'data-assets-url' );
					let parent = self.parent( '.exms-progress-content' );
					let grandParent = parent.parent( '.exms-progress-post-wrap' );

					if( 'collapse' == self.attr( 'data-src' ) ) {

						self.attr( 'data-src', 'expand' );
						self.attr( 'src', assetsURL+'imgs/gray_arrow_expand.png' );
					} else {
						self.attr( 'data-src', 'collapse' );
						self.attr( 'src', assetsURL+'imgs/gray_arrow_collapse.png' );
					}

					grandParent.find( '.exms-progress-post-check' ).slideToggle();
					parent.siblings( '.exms-progress-post-wrap' ).slideToggle();
				} );
			},

			/**	
			 * Search next page sortable items 
			 */
			searchNextBackPageItems: function() {

				$( 'body' ).on( 'click', '.exms-sortable-pagination-wrap .exms-sortable-paginate', function() {
					let self = $( this );
					let value = self.data( 'value' );
					let paginationWrap = self.closest( '.exms-sortable-pagination-wrap' );
					let user = paginationWrap.attr( 'data-user' );
					let isRightSide;
					let isLeftSide;
					if ( user === 'user' ) {
						isRightSide = paginationWrap.hasClass( 'exms-user-sortable-pagination-wrap-right' );
						isLeftSide = paginationWrap.hasClass( 'exms-user-sortable-pagination-wrap-left' );
					} else if (user === 'post-relation') {
						isRightSide = paginationWrap.hasClass( 'exms-post-sortable-pagination-wrap-right' );
						isLeftSide = paginationWrap.hasClass( 'exms-post-sortable-pagination-wrap-left' );
					} else {
						isRightSide = paginationWrap.hasClass( 'exms-sortable-pagination-wrap-right' );
						isLeftSide = paginationWrap.hasClass( 'exms-sortable-pagination-wrap-left' );
					}

					let currentPageSpan = paginationWrap.find( '.exms-start-page' );
					let totalPageSpan = paginationWrap.find( '.exms-total-page' );
					
					let nextPage;
					if( value == "next" ) {
						nextPage = parseInt( currentPageSpan.text() ) + 1;
					} else {
						nextPage = parseInt( currentPageSpan.text() ) - 1;
					}
					
					let totalPage = parseInt( totalPageSpan.text() );
					
					if( nextPage > totalPage || nextPage < 1 ) {

						return false;
					}

					let parent = self.parents( '.exms-sortable-lists' );
					let postId = paginationWrap.attr( 'data-post-id' );
					let name = parent.find('.exms-sortable-item').data( 'name' );
					let nameKey = parent.find( '.exms-sortable-item' ).data( 'name-key' );
					let postType = parent.find( '.exms-post-search-input' ).data( 'search-post-type' );
					
					currentPageSpan.text( nextPage );

					let paginationType = isRightSide ? 'assigned' : 'unassigned';
					
					let data = {
						'action': 'exms_next_back_sortable_page',
						'security': EXMS.security,
						'page': nextPage,
						'post_id': postId,
						'relation_type': name,
						'pagination_type': paginationType,
						'post_type': postType,
						'user' : user
					};

					jQuery.post( EXMS.ajaxURL, data, function( resp ) {
						let response = JSON.parse( resp );
						if( response.status == 'false' ) {
							$.alert( response.message );
						} else {
							let paginationRecords = '';
							$.each( response.content, function( index, records ) {
								let avatarHtml = '';
								if ( user == "user" ) {
									avatarHtml = `<img src="${records.avatar}" class="exms-avatar" />`;
								}
								paginationRecords += `
									<div class="exms-sortable-item" 
										data-name="${name}" 
										data-name-key="${nameKey}">
										
										<a href="${records.href}" target="_blank" class="exms-sortable-ui-link">
											<span class="exms-post-title">${records.title}</span>
										</a>
										${avatarHtml}
										<input type="hidden" 
											name="${nameKey}[${nameKey}][]" 
											class="exms-assign-unassign-id" 
											value="${records.id}">
											
										<input type="hidden" 
											name="exms_${nameKey}_relation" 
											value="${postType}">
									</div>`;
							});
							
							let isClass = '';
							let targetContainer;

							if (user === 'user') {
								isClass = isRightSide ? '.exms-user-sortable-pagination-wrap-right' : '.exms-user-sortable-pagination-wrap-left';
							} else if (user === 'post-relation') {
								isClass = isRightSide ? '.exms-post-sortable-pagination-wrap-right' : '.exms-post-sortable-pagination-wrap-left';
							} else {
								isClass = isRightSide ? '.exms-sortable-pagination-wrap-right' : '.exms-sortable-pagination-wrap-left';
							}

							targetContainer = $( ( isRightSide ? '.exms-assign-box-right ' : '.exms-assign-box-left ' ) + isClass );
							if( user == "user" ) {
								targetContainer = targetContainer.closest( '.exms-sortable-box-wrap' ).find( '.exms-sortable-items-wrap' + isClass );
							} else {
								targetContainer = targetContainer
								.closest( '.exms-sortable-box-wrap' )
								.find( '.exms-sortable-items-wrap' + isClass )
								.filter( function () {
									return $( this ).data( 'post-type' ) == postType;
								});
							}
							console.log(postType);
							console.log(targetContainer);
							
							if( targetContainer.length > 0 ) {
								targetContainer.html( paginationRecords );
							} else {
								console.error('Target container not found for pagination update');
							}
						}
					});
				});
			},

			/**
			 * Filter post relation search items.
			 */
			searchPostRelationItems: function() {

				$( 'body' ).on( 'keyup', '.exms-post-search-input', function() {

					let self = $( this );
					let searchKey = self.val();
					let searchPostType = self.data( 'search-post-type' );
					let currentPostType = self.data( 'current-post-type' );
					let dataType = self.data( 'type' );
					let postID = self.data( 'post-id' );
					let parentRelation = self.data( 'parent-relation' ) && self.data( 'parent-relation' ) != 'undefined' ? self.data( 'parent-relation' ) : '';
					let parent = self.parents( '.exms-sortable-lists' );
					let nameKey = parent.data( 'relation' );
					let name = parent.find( '.exms-sortable-items-wrap' ).data( 'name' );
					let userID = self.parents( '.exms-sortable-lists' ).data( 'user-id' );

					let data = {
						'action' 			: 'exms_search_post_items',
						'security'			: EXMS.security,
						'search_key'		: searchKey,
						'search_post_type'	: searchPostType,
						'current_post_type' : currentPostType,
						'data_type'			: dataType,
						'post_id'			: postID,
						'parent_relation'	: parentRelation,
						'name'				: name,
						'name_key'			: nameKey,
						'user_id'			: userID
					};

					jQuery.post( EXMS.ajaxURL, data, function( resp ) {

                        let response = JSON.parse( resp );
                        if( response.status == 'false' ) {
                        	$.alert( response.message );
                        } else {
                        	
                        	parent.find( '.exms-sortable-items-wrap' ).html( response.content );
                        }
                    } );
				} );
			},

			/**
			 * Drag and drop post items
			 */
			dragDropPostItems: function() {
				  		
			  	$( '.exms-sortable-items-wrap' ).multipleSortable({
					items: '.exms-sortable-item',
					connectWith: '.exms-sortable-items-wrap',
					placeholder: 'placeholder',
					container: '.exms-sortable-box-wrap',
					over: function( event, ui ) {
				        ui.placeholder.css({
				      		'visibility'	: 'visible',
				      		'padding' 		: '6px 6px 6px 24px',
				      	});
				    },
				    stop: function( event, ui, items ) {
				    	$.each( items, function( index, elem ) {
							if( $( elem ).hasClass( 'multiple-sortable-selected' ) ) {
								$( elem ).removeClass( 'multiple-sortable-selected' );
								let name = $( elem ).find( '.exms-assign-unassign-id' ).attr( 'name' );
								let postID = $( elem ).find( '.exms-assign-unassign-id' ).val();
								$( elem ).parents( '.exms-sortable-items-wrap' ).find( '.exms-post-not-found' ).remove();

								let position = $( elem ).index();
								let parent = $( elem ).parents( '.exms-sortable-lists' );
								let relation = parent.data( 'relation' );

								let dragIcon = $( elem ).find( '.exms-drag-icon' );
								let postTitle = $( elem ).find('.exms-post-title' );
								let postTitleAnchor = postTitle.find( 'a' );
								let imgParent = $( elem ).find( '.exms-img-parent' );
								let avatarImg = imgParent.find( 'img' );

								dragIcon.detach();
								if( parent.hasClass( 'exms-assign-box-right' ) ) {
									name = 'exms_assign_items['+relation+'][]';
									$( elem ).find( '.exms-assign-unassign-id' ).addClass( 'exms-assign-'+relation );
									$( elem ).find( '.exms-assign-unassign-id' ).removeClass( 'exms-unassign-'+relation );
									dragIcon.empty().html('&#8592;');
									postTitleAnchor.before( dragIcon );

								} else if( parent.hasClass( 'exms-assign-box-left' ) ) {
									name = 'exms_unassign_items['+relation+'][]';
									$( elem ).find( '.exms-assign-unassign-id' ).addClass( 'exms-unassign-'+relation );
									$( elem ).find( '.exms-assign-unassign-id' ).removeClass( 'exms-assign-'+relation );
									dragIcon.empty().html('&#8594;');
									if( imgParent.length ) {
										avatarImg.after( dragIcon );
									} else {
										postTitle.after( dragIcon );
									}
								}
								$( elem ).find( '.exms-assign-unassign-id' ).attr( 'name', name );
							}
						});
				    },
				} );
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
							$( '.wpeq_quiz_sign' ).prop( 'disabled', false );
							break;

						case 'subscribe':
							$( '.exms-price-row, .exms-subs-row' ).addClass( 'exms-show' );
							$( '.exms-close-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', false );
							break;

						case 'close':
							$( '.exms-price-row, .exms-close-row' ).addClass( 'exms-show' );
							$( '.exms-price-row' ).removeClass( 'exms-show' );
							$( '.exms-subs-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', true );
							break;
						
						case 'free':
							$( '.exms-price-row' ).removeClass( 'exms-show' );
							$( '.exms-subs-row' ).removeClass( 'exms-show' );
							$( '.exms-close-row' ).removeClass( 'exms-show' );
							$( '.wpeq_quiz_sign' ).prop( 'disabled', true );
							break;
						
						default:
							$( '.exms-price-row, .exms-subs-row, .exms-close-row' ).removeClass( 'exms-show' );
							break;
					}

				} );
			},

			/**
			 * Toggle assign/unassign dots dropdowns
			 */
			toggleDotsDropdown: function () {

				$( 'body' ).on( 'click', '.exms-dots-toggle', function ( e ) {
					var type = $( this ).data( 'type' );
					var dropdown = $( this ).siblings( type === 'assign' ? '.exms-dropdown-assign' : '.exms-dropdown-unassign' );

					if ( dropdown.is( ':visible' ) ) {
						dropdown.hide().change();
					} else {
						$( '.exms-dropdown-menu' ).hide().change();
						dropdown.show().change();
					}
					e.stopPropagation();
				});

				$( 'body' ).on( 'click', function () {
					$( '.exms-dropdown-menu' ).hide().change();
				} );

				$( 'body' ).on( 'click', '.exms-dropdown-menu', function ( e ) {
					e.stopPropagation();
				});
			},

			assignUserRole: function () {
				$( '.exms-dropdown-btn[data-target="user"]' ).on( "click", function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( "type" );
					let role = self.data( "role" );

					let isAssigning = type === "assigned";

					let sourceWrap = isAssigning
						? $( '.exms-user-sortable-items-wrap-left' )
						: $( '.exms-user-sortable-items-wrap-right' );

					let targetWrap = isAssigning
						? $( '.exms-user-sortable-items-wrap-right' )
						: $( '.exms-user-sortable-items-wrap-left' );

					let items = sourceWrap.find( '.exms-sortable-item' );
					if ( items.length === 0 ) return;

					let existingTargetItems = targetWrap.find( '.exms-sortable-item' );

					if ( isAssigning && existingTargetItems.length === 0 ) {
						targetWrap.empty();
					} else if ( !isAssigning && existingTargetItems.length === 0 ) {
						targetWrap.empty();
					}

					items.each( function () {
						let item = $( this );

						item.attr( 'data-name', isAssigning ? 'exms_assign_items' : 'exms_unassign_items' );

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

					let updatedUserIds = [];
					targetWrap.find( '.exms-assign-unassign-id' ).each( function () {
						updatedUserIds.push( $( this ).val() );
					} );

					if ( isAssigning ) {
						$( '.exms-dropdown-assign .exms-dropdown-btn' )
							.data( 'users', updatedUserIds )
							.attr( 'data-users', JSON.stringify( updatedUserIds ) );

						$( '.exms-dropdown-unassign .exms-dropdown-btn' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					} else {
						$( '.exms-dropdown-unassign .exms-dropdown-btn' )
							.data( 'users', updatedUserIds )
							.attr( 'data-users', JSON.stringify( updatedUserIds ) );

						$( '.exms-dropdown-assign .exms-dropdown-btn' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					}

					if ( isAssigning ) {
						$( '.exms-user-sortable-pagination-wrap-left' ).hide().change();
						$( '.exms-user-sortable-pagination-wrap-right' ).show().change();
					} else {
						$( '.exms-user-sortable-pagination-wrap-right' ).hide().change();
						$( '.exms-user-sortable-pagination-wrap-left' ).show().change();
					}
				} );
			},

			assignParentUserRole: function () {
				$( '.exms-dropdown-btn[data-target="parent-user"]' ).on( "click", function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( "type" );
					let role = self.data( "role" );

					let isAssigning = type === "assigned";

					let sourceWrap = isAssigning
						? $( '.exms-parent-user-sortable-items-wrap-left' )
						: $( '.exms-parent-user-sortable-items-wrap-right' );

					let targetWrap = isAssigning
						? $( '.exms-parent-user-sortable-items-wrap-right' )
						: $( '.exms-parent-user-sortable-items-wrap-left' );

					let items = sourceWrap.find( '.exms-sortable-item' );
					if ( items.length === 0 ) return;

					let existingTargetItems = targetWrap.find( '.exms-sortable-item' );

					if ( isAssigning && existingTargetItems.length === 0 ) {
						targetWrap.empty();
					} else if ( !isAssigning && existingTargetItems.length === 0 ) {
						targetWrap.empty();
					}

					items.each( function () {
						let item = $( this );

						item.attr( 'data-name', isAssigning ? 'exms_assign_items' : 'exms_unassign_items' );

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

					let updatedUserIds = [];
					targetWrap.find( '.exms-assign-unassign-id' ).each( function () {
						updatedUserIds.push( $( this ).val() );
					} );

					if ( isAssigning ) {
						$( '.exms-dropdown-assign .exms-dropdown-btn' )
							.data( 'users', updatedUserIds )
							.attr( 'data-users', JSON.stringify( updatedUserIds ) );

						$( '.exms-dropdown-unassign .exms-dropdown-btn' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					} else {
						$( '.exms-dropdown-unassign .exms-dropdown-btn' )
							.data( 'users', updatedUserIds )
							.attr( 'data-users', JSON.stringify( updatedUserIds ) );

						$( '.exms-dropdown-assign .exms-dropdown-btn' )
							.data( 'users', [] )
							.attr( 'data-users', [] );
					}

					if ( isAssigning ) {
						$( '.exms-parent-user-sortable-pagination-wrap-left' ).hide().change();
						$( '.exms-parent-user-sortable-pagination-wrap-right' ).show().change();
					} else {
						$( '.exms-parent-user-sortable-pagination-wrap-right' ).hide().change();
						$( '.exms-parent-user-sortable-pagination-wrap-left' ).show().change();
					}
				} );
			},

			assignPostRelation: function () {
				$('.exms-dropdown-btn[data-target="post"]').on( 'click', function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( 'type' );
					let isAssigning = type === 'assigned';

					let parentBox = self.closest( '.exms-sortable-lists' );
					let postType = parentBox.find( '.exms-sortable-items-wrap' ).data( 'post-type' );

					let sourceWrap = parentBox.find( '.exms-sortable-items-wrap[data-post-type="' + postType + '"]' );

					let targetWrapSelector = isAssigning
						? '.exms-post-sortable-items-wrap-right[data-post-type="' + postType + '"]'
						: '.exms-post-sortable-items-wrap-left[data-post-type="' + postType + '"]';

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
						$( '.exms-post-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).hide().change();
						$( '.exms-post-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).show().change();
					} else {
						$( '.exms-post-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).hide().change();
						$( '.exms-post-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).show().change();
					}
				} );
			},
			
			/**
			 * Click on drag icon to move to the otherside
			 */
			iconPostRelation: function () {
				$( document ).on( 'click', '.exms-drag-icon[data-target="post"]', function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( 'name' );
					let isAssigning = type === 'exms_unassign_items';

					let itemField = self.closest( '.exms-sortable-item' );
					let parentBox = self.closest( '.exms-sortable-lists' );
					let postType = parentBox.find( '.exms-sortable-items-wrap' ).data( 'post-type' );

					let sourceSelector = isAssigning
						? '.exms-post-sortable-items-wrap-left[data-post-type="' + postType + '"]'
						: '.exms-post-sortable-items-wrap-right[data-post-type="' + postType + '"]';

					let targetSelector = isAssigning
						? '.exms-post-sortable-items-wrap-right[data-post-type="' + postType + '"]'
						: '.exms-post-sortable-items-wrap-left[data-post-type="' + postType + '"]';

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
						$( '.exms-post-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).find( itemField ).hide().change();
						$( '.exms-post-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).show().change();
					} else {
						$( '.exms-post-sortable-pagination-wrap-right[data-post-type="' + postType + '"]' ).find( itemField ).hide().change();
						$( '.exms-post-sortable-pagination-wrap-left[data-post-type="' + postType + '"]' ).show().change();
					}
				});
			},
			
			/**
			 * Click on drag icon to move to the other side for User
			 */
			iconUser: function () {
				$( document ).on( 'click', '.exms-drag-icon[data-target="user"]', function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( 'name' );
					let isAssigning = type === 'exms_unassign_items';

					let itemField = self.closest( '.exms-sortable-item' );
					let parentBox = self.closest( '.exms-sortable-lists' );
					let postType = parentBox.find( '.exms-sortable-items-wrap' ).data( 'post-type' );

					let sourceSelector = isAssigning
						? '.exms-user-sortable-items-wrap-left'
						: '.exms-user-sortable-items-wrap-right';

					let targetSelector = isAssigning
						? '.exms-user-sortable-items-wrap-right'
						: '.exms-user-sortable-items-wrap-left';

					let sourceWrap = $( sourceSelector );
					let targetWrap = $( targetSelector );
					let existingTargetItems = targetWrap.find( '.exms-sortable-item' );

					itemField.attr( 'data-name', isAssigning ? 'exms_assign_items' : 'exms_unassign_items' );
					itemField.attr( 'data-name-key', 'current' );

					let iconButton = itemField.find( '.exms-drag-icon' );
					let postTitle = itemField.find( '.exms-post-title' );
					let imgAvatar = itemField.find( '.exms-avatar' );
					iconButton.detach();

					if ( isAssigning ) {
						iconButton.empty().html('&#8592;');
						postTitle.prepend( iconButton );
						iconButton.data( 'name', 'exms_assign_items' );
					} else {
						iconButton.empty().html('&#8594;');
						imgAvatar.after( iconButton );
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
						$( '.exms-user-sortable-pagination-wrap-left' ).find( itemField ).hide().change();
						$( '.exms-user-sortable-pagination-wrap-right' ).show().change();
					} else {
						$( '.exms-user-sortable-pagination-wrap-right' ).find( itemField ).hide().change();
						$( '.exms-user-sortable-pagination-wrap-left' ).show().change();
					}
				});
			},

			/**
			 * Click on drag icon to move to the other side for User
			 */
			iconInstructorUser: function () {
				$( document ).on( 'click', '.exms-drag-icon[data-target="parent-user"]', function ( e ) {
					e.preventDefault();

					let self = $( this );
					let type = self.data( 'name' );
					let isAssigning = type === 'exms_unassign_items';

					let itemField = self.closest( '.exms-sortable-item' );

					let sourceSelector = isAssigning
						? '.exms-parent-user-sortable-items-wrap-left'
						: '.exms-parent-user-sortable-items-wrap-right';

					let targetSelector = isAssigning
						? '.exms-parent-user-sortable-items-wrap-right'
						: '.exms-parent-user-sortable-items-wrap-left';

					let sourceWrap = $( sourceSelector );
					let targetWrap = $( targetSelector );
					let existingTargetItems = targetWrap.find( '.exms-sortable-item' );

					itemField.attr( 'data-name', isAssigning ? 'exms_assign_items' : 'exms_unassign_items' );
					itemField.attr( 'data-name-key', 'current' );

					let iconButton = itemField.find( '.exms-drag-icon' );
					let postTitle = itemField.find( '.exms-post-title' );
					let postImgParent = itemField.find( '.exms-img-parent' );
					let imgAvatar = postImgParent.find( '.exms-avatar' );
					iconButton.detach();

					if ( isAssigning ) {
						iconButton.empty().html('&#8592;');
						postTitle.prepend( iconButton );
						iconButton.data( 'name', 'exms_assign_items' );
					} else {
						iconButton.empty().html('&#8594;');
						imgAvatar.after( iconButton );
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
						$( '.exms-parent-user-sortable-pagination-wrap-left' ).find( itemField ).hide().change();
						$( '.exms-parent-user-sortable-pagination-wrap-right' ).show().change();
					} else {
						$( '.exms-parent-user-sortable-pagination-wrap-right' ).find( itemField ).hide().change();
						$( '.exms-parent-user-sortable-pagination-wrap-left' ).show().change();
					}
				});
			},
		}

		EXMSdragDrop.init();
	});
})( jQuery );


