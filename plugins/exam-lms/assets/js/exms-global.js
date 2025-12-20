( function( $ ) { 'use strict';

	$( document ).ready( function() {
		
		let EXMScommon = {
			
			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.initializeSelect2();
				this.createUserQuizChart();
			},

			/**
			 * Initialize select2 field
			 */
			initializeSelect2: function() {
				
				$( '.exms-select2' ).select2( {
					tags: true,
					tokenSeparators: [ ',' ],
					placeholder: 'Add search tags here...'
				});

				$( '.exms-inst-select2' ).select2( {
					allowClear: true,
					placeholder: 'Search with user...',
					ajax: {
                            url: EXMS.ajaxURL,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'exms_search_users'
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
				} );
			}, 

			/**
			 * Create user quiz progress chart
			 */
			createUserQuizChart: function() {

				if( $( '.exms-quiz-chart' ).length > 0 ) {

					let ctx = $( '.exms-quiz-chart' );
					let labels = ctx.data( 'labels' ).split( ',' );
					let data = ctx.data( 'values' ).split( ',' );
					this.initializeChart( ctx, 'pie', labels, data );
				}
			},

			/**
			 * Helper function to create a chart
			 */
			initializeChart: function( ctx, chartType, chartlabels, chartData ) {

				new Chart( ctx, {
				    type: chartType,
				    data: {
				        labels: chartlabels,
				        datasets: [ {
				            data: chartData,
				            backgroundColor: [
				                'rgba(255, 99, 132, 1)',
				                'rgba(54, 162, 235, 1)',
				                'rgba(255, 206, 86, 1)',
				                'rgba(75, 192, 192, 0.2)',
				                'rgba(153, 102, 255, 0.2)',
				                'rgba(255, 159, 64, 0.2)'
				            ],
				            borderColor: [
				                'rgba(255, 99, 132, 1)',
				                'rgba(54, 162, 235, 1)',
				                'rgba(255, 206, 86, 1)',
				                'rgba(75, 192, 192, 1)',
				                'rgba(153, 102, 255, 1)',
				                'rgba(255, 159, 64, 1)'
				            ],
				            borderWidth: 1
				        } ]
					}
				} );
			}
		}

		EXMScommon.init();
	});
})( jQuery );


