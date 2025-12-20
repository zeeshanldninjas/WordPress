( function( $ ) { 'use strict';

	$( document ).ready( function() {
		
		let EXMS_Post_Completions = {
			
			init: function() {

				this.markCompletePost();
			},

			/**
			 * Post mark complete
			 */
			markCompletePost: function() {

				$( 'body' ).on( 'click', '.exms-mark-complete-button', function() {

					let self = $( this );
					self.attr( 'disabled', 'disabled' );
					let parent = self.parents( '.exms-action-button' );
					let postID = parent.data( 'post-id' );
					let parentPostID = '';
					let results = new RegExp( '[\?&]' + 'parent_posts' + '=([^&#]*)' ).exec( window.location.href );
			        if( results ) {
			        	parentPostID = decodeURI( results[1] ) || 0;
			        }

			        if( ! parentPostID ) {
			        	$.alert( 'URL is Changed Request is not Proceed.' );
			        	return false;
			        }

					let data = {
						'action' 			: 'exms_post_mark_complete',
						'security'			: EXMS.security,
						'post_id'			: postID,
						'parent_posts'	 	: parentPostID
					};

					jQuery.post( EXMS.ajaxURL, data, function( resp ) {

                        let response = JSON.parse( resp );
                        if( response.status == 'false' ) {
                        	$.alert( response.message );
                        } else {
                        	$( '.exms-mark-completed-button' ).html( '<div class="exms-post-com-msg">Post has been Completed</div>' );
                        	location.reload( true );
                        }
                    } );
				} );
			},
		}

		EXMS_Post_Completions.init();
	});
})( jQuery );