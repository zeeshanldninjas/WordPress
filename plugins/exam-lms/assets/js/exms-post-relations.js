( function( $ ) { 'use strict';

	$( document ).ready( function() {
		
		let EXMSdragDrop = {
			
			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.dragDropPostItems();
				this.searchPostRelationItems();
				this.searchNextBackPageItems();
				this.toggleProgressDetailItems();
				this.changeOnProgressCheckBox();
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

				$( 'body' ).on( 'click', '.exms-sortable-paginate', function() {

					let self = $( this );
					let value = self.data( 'value' );
					let parent = self.parents( '.exms-sortable-lists' );
					let page = parseInt( parent.find( '.exms-sortable-pagination-wrap' ).attr( 'data-pages' ) );
					let postType = parent.find( '.exms-post-search-input' ).data( 'search-post-type' );
					let currentPostType = parent.find( '.exms-post-search-input' ).data( 'current-post-type' );
					let type = parent.find( '.exms-post-search-input' ).data( 'type' );
					let currentPostID = parent.find( '.exms-post-search-input' ).data( 'post-id' );
					let relation = parent.data( 'relation' );
					let name = parent.find( '.exms-sortable-item' ).data( 'name' );
					let nameKey = parent.find( '.exms-sortable-item' ).data( 'name-key' );
					let userID = parent.data( 'user-id' );

					let unassignExists = parent.find( '.exms-sortable-item' ).find( '.exms-unassign-'+relation ).val();
					let assignExists = parent.find( '.exms-sortable-item' ).find( '.exms-assign-'+relation ).val();
					if( unassignExists || assignExists ) {
						$.alert( 'Please save your changes first.' );
						return false;
					}

					let pageCount = '';
                	if( 'back' == value ) {
                		pageCount = page - 1;
                	} else if( 'next' == value ) {
                		pageCount = page + 1;
                	}

                	if( ! pageCount || pageCount == 0 ) {
 						return false;
                	}

					let data = {
						'action' 			: 'exms_next_back_sortable_page',
						'security'			: EXMS.security,
						'page'				: pageCount,
						'post_type' 		: postType,
						'relation'			: relation,
						'name'				: name,
						'name_key'			: nameKey,
						'type'				: type,
						'current_post_type'	: currentPostType,
						'post_id'			: currentPostID,
						'user_id'			: userID
					};

					jQuery.post( EXMS.ajaxURL, data, function( resp ) {

                        let response = JSON.parse( resp );
                        if( response.status == 'false' ) {

                        	$.alert( response.message );

                        } else {

                        	if( response.content ) {
                        		parent.find( '.exms-sortable-items-wrap' ).find( '.exms-sortable-item' ).hide();
                        		parent.find( '.exms-sortable-items-wrap' ).append( response.content );
                        		parent.find( '.exms-sortable-pagination-wrap' ).attr( 'data-pages', pageCount );
                        		parent.find( '.exms-sortable-pagination-wrap' ).find( '.exms-sortable-pages' ).text( 'Page '+pageCount );
                        	} else {
                        		parent.find( '.exms-sortable-pagination-wrap' ).attr( 'data-pages', page );
                        		parent.find( '.exms-sortable-pagination-wrap' ).find( '.exms-sortable-pages' ).text( 'Page 1' );
                        	}

                        	if( 'next' == value && response.next_post_count == 0 ) {
                        		self.css( 'visibility', 'hidden' );
                        	}

                        	if( 'back' == value ) {
                        		parent.find( '.exms-sortable-next' ).css( 'visibility', 'visible' );
                        	}

                        	if( 'back' == value && pageCount == 1 ) {
		                		self.css( 'visibility', 'hidden' );
		                	} else if( 'next' == value && pageCount > 1 ) {
		                		parent.find( '.exms-sortable-back' ).css( 'visibility', 'visible' );
		                	}
                        }
                    } );
				} );
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
				      		'border' 		: 'dashed 1px #000000',
				      		'padding' 		: '6px 6px 6px 24px',
				      		'background' 	: '#b2b1b1'
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

				    			if( parent.hasClass( 'exms-assign-box-right' ) ) {
				    				name = 'exms_assign_items['+relation+'][]';
				    				$( elem ).find( '.exms-assign-unassign-id' ).addClass( 'exms-assign-'+relation );
				    				$( elem ).find( '.exms-assign-unassign-id' ).removeClass( 'exms-unassign-'+relation );

				    			} else if( parent.hasClass( 'exms-assign-box-left' ) ) {
				    				name = 'exms_unassign_items['+relation+'][]';
				    				$( elem ).find( '.exms-assign-unassign-id' ).addClass( 'exms-unassign-'+relation );
				    				$( elem ).find( '.exms-assign-unassign-id' ).removeClass( 'exms-assign-'+relation );
				    			}

				    			$( elem ).find( '.exms-assign-unassign-id' ).attr( 'name', name );
				    		}
				    	} );
				    },
				} );
			},

		}

		EXMSdragDrop.init();
	});
})( jQuery );


