( function( $ ) {
    'use strict';
    $( document ).ready(function() { 

        let EXMSCoursePage = { 

            /**
             *  Initialize the functionality
             */
            init: function() {
                this.DisplayNextStep();
                this.DisplayTabs();
                this.ShowLesson();
                this.handleEnrollmentsection();
                this.CourseDetailsSideBar();
                this.HandleStartCourse();
                this.handlePopupforLogin();
            },


            DisplayNextStep: function () {
                $(document).on('click', '.js-exms-toggle-step', function () {
                    let header = $(this);
                    let container = header.closest('.exms-course-module');
                    let childContainer = container.find('.js-exms-child-container').first();
                    let arrowIcon = header.find('.dashicons');

                    if (!childContainer.length) {
                        return;
                    }

                    if (header.hasClass('open')) {
                        header.removeClass('open');
                        arrowIcon.removeClass('rotated');

                        childContainer
                            .stop(true, true)
                            .animate(
                                { height: 0, opacity: 0 },
                                {
                                    duration: 350,
                                    easing: 'swing',
                                    complete: function () {
                                        $(this).hide().css({ height: '', opacity: '' });
                                    }
                                }
                            );
                    } else {
                        header.addClass('open');
                        arrowIcon.addClass('rotated');

                        childContainer.stop(true, true).show().css({ height: 0, opacity: 0 });

                        let fullHeight = childContainer.get(0).scrollHeight;

                        childContainer
                            .animate(
                                { height: fullHeight, opacity: 1 },
                                {
                                    duration: 350,
                                    easing: 'swing',
                                    complete: function () {
                                        $(this).css({ height: '', opacity: '' });
                                    }
                                }
                            );
                    }
                });
            },

           /**
            * Course details side bar handler
            */ 
            CourseDetailsSideBar: function () {
                const toggleBtn = $('#toggleSidebarBtn');
                const sidebar = $('.exms-course-page-right');
                const body = $('body');
                const toggleIcon = toggleBtn.find('.toggle-side-bar-icon');
                const iconRight = toggleIcon.data('icon-right');
                const iconLeft = toggleIcon.data('icon-left');

                toggleIcon.attr('src', toggleBtn.hasClass('active') ? iconRight : iconLeft);

                toggleBtn.on('click', function (e) {
                    e.preventDefault();

                    toggleBtn.toggleClass('active');
                    sidebar.toggleClass('active');
                    body.toggleClass('sidebar-open');

                    const isActive = toggleBtn.hasClass('active');
                    toggleIcon.attr('src', isActive ? iconRight : iconLeft);
                });

                $(document).on('click', function (e) {
                    const isClickInside = $(e.target).closest('.exms-course-page-right, #toggleSidebarBtn').length > 0;

                    if (sidebar.hasClass('active') && !isClickInside) {
                        sidebar.removeClass('active');
                        body.removeClass('sidebar-open');
                        toggleBtn.removeClass('active');
                        toggleIcon.attr('src', iconLeft);
                    }
                });
            },

            /**
             * Handle Tab Switching
             */
            DisplayTabs: function() {

                $( document ).on( 'click', '.exms-course-info-tabs button', function() {

                    let self = $( this );

                    $( '.exms-course-info-tabs button' ).removeClass( 'active-tab' );

                    self.addClass( 'active-tab' );

                    $( '.exms-course-steps, .course-description-tab, .course-notice-tab, .course-review-tab' ).hide();

                    if ( self.hasClass( 'course-content' ) ) {
                        $( '.exms-course-steps' ).slideDown( 300 );
                    } else if ( self.hasClass( 'course-description' ) ) {
                        $( '.course-description-tab' ).slideDown( 300 );
                    } else if ( self.hasClass( 'course-notice' ) ) {
                        $( '.course-notice-tab' ).slideDown( 300 );
                    } else if ( self.hasClass( 'course-review' ) ) {
                        $( '.course-review-tab' ).slideDown( 300 );
                    }
                });
            },

            /**
             * Show the Lesson Content
             */
            ShowLesson: function(){

                $( document ).on( 'click', '.start-course', function() {

                    $( '.exms-course-page-container' ).hide()
                    $( '.lesson-page-container' ).show()
                    
                });

            },

            handleEnrollmentsection: function () {

                const stickyBar = $( '.mobile-sticky-container' );
                const footer = $( 'footer' );

                function checkFooterVisibility () {
                    const windowWidth = $( window ).width();

                    if ( windowWidth <= 1024 ) {
                        const footerTop = footer.offset().top;
                        const scrollTop = $( window ).scrollTop();
                        const windowHeight = $( window ).height();

                        if ( scrollTop + windowHeight >= footerTop ) {
                            if ( stickyBar.is( ':visible' ) ) {
                                stickyBar.stop( true, true ).slideUp( 300 );
                            }
                        } else {
                            if ( !stickyBar.is( ':visible' ) ) {
                                stickyBar.stop( true, true ).slideDown( 300 );
                            }
                        }
                    } else {
                        if ( stickyBar.is( ':visible' ) ) {
                            stickyBar.stop( true, true ).slideUp( 300 );
                        }
                    }
                }

                checkFooterVisibility();
                $( window ).on( 'scroll resize', checkFooterVisibility );

            },
            
            /**
            * Handle course start control
            */
            HandleStartCourse: function() {

                $(document).on('click', '.exms-start-course', function () {

                    let currentRequest = null;
                    let self = $(this);
                    let CourseType = self.data('course_type');
                    let CourseID = self.attr('data_course_id');
                    let CourseStatus = self.data( 'course_status' );

                    if ( CourseStatus == 'Not Started' ) {
                        return;
                    }
                    $('body').append(
                        '<div class="exms-loader-overlay">' +
                            '<div class="exms-loader-spinner"></div>' +
                        '</div>'
                    );

                    currentRequest = $.ajax({
                        url: EXMS.ajaxURL,
                        method: 'POST',
                        data: {
                            action: 'exms_enrolled_user_to_the_course',
                            course_type: CourseType,
                            course_id : CourseID,
                            security: EXMS.security
                        },
                        success: function (response) {
                            if ( response.status === 'show_login_popup' ) {
                                $('.exms-loader-overlay').remove();

                                if ( !$( '#exms-login-popup' ).length ) {
                                    $('body').append( response.popup_html );
                                }

                                $( '#exms-login-popup' ).fadeIn();
                            }
                            else if ( response.status === 'success' || response.status === 'true' ) {
                                setTimeout(function () {
                                    $('.exms-loader-overlay').remove();

                                    self.append(
                                        '<div class="exms-success-message">🎉 ' + response.message + '</div>'
                                    );

                                    setTimeout(function () {
                                        $('.exms-success-message').fadeOut(300, function () {
                                            $(this).remove();
                                            location.reload();
                                        });
                                    }, 2000);
                                }, 1000);
                            }
                            else if( response.status === 'show_payment_popup' ) {
                                $('.exms-loader-overlay').remove();

                                if( !$( '#exms-payment-popup-overlay' ).length ) {
                                    $( 'body' ).append( response.popup_html );
                                }

                                $( '#exms-payment-popup-overlay' ).fadeIn();
                                if( response.user_name ) {
                                    $( '#paypal-name, #stripe-name' ).val( response.user_name );
                                }

                                if( response.user_email ) {
                                    $( '#paypal-email, #stripe-email' ).val( response.user_email );
                                }

                                // Populate course data for PayPal
                                if( response.course_id ) {
                                    $( '#exms-course-id' ).val( response.course_id );
                                }
                                if( response.course_price ) {
                                    $( '#exms-course-price' ).val( response.course_price );
                                }
                                if( response.course_title ) {
                                    $( '#exms-course-title' ).val( response.course_title );
                                }
                                if( response.paypal_payee ) {
                                    $( '#exms-paypal-payee' ).val( response.paypal_payee );
                                }

                                // Initialize PayPal button directly
                                initCoursePayPalButton();
                            }

                            else {
                                $( '.exms-loader-overlay' ).remove();
                            }
                        }
                    });
                });

                // Tab switch
                $(document).on('click', '.exms-tab', function () {
                    var target = $(this).data('target');
                    $('.exms-tab').removeClass('active');
                    $(this).addClass('active');
                    $('.exms-tab-content').hide();
                    $('#' + target).show();
                });

                // Close popup
                $(document).on('click', '.exms-close-popup', function () {
                    $('#exms-payment-popup, #exms-login-popup').fadeOut();
                    $('#exms-payment-popup-overlay').remove();
                });
            },

            handlePopupforLogin: function() {

                $(document).on('click', '.exms-login-tab-button', function() {
                    var tabId = $(this).data('tab');

                    $('.exms-login-tab-button').removeClass('active');
                    $(this).addClass('active');

                    $('.exms-login-tab-content').removeClass('active');
                    $('#' + tabId).addClass('active');
                });

                $(document).on('click', '.open-exms-login-popup', function(e) {
                    e.preventDefault();
                    $('#exms-login-popup, #exms-login-popup-overlay').fadeIn(200);
                });

                $(document).on('click', '.exms-login-close, #exms-login-popup-overlay', function() {
                    $('#exms-login-popup, #exms-login-popup-overlay').fadeOut(200);
                });
            },

        };

        // PayPal course payment function
        function initCoursePayPalButton() {
            // Check if PayPal SDK is loaded
            if (typeof paypal === 'undefined') {
                console.error('PayPal SDK not loaded yet');
                return false;
            }

            // Check if PayPal button container exists
            if (!$('#exms-paypal-button-container').length) {
                console.error('PayPal button container not found');
                return false;
            }

            let courseID = $('#exms-course-id').val();
            let price = $('#exms-course-price').val();
            let courseTitle = $('#exms-course-title').val();
            let payeeEmail = $('#exms-paypal-payee').val();
            let userID = EXMS.user_id || 0;

            if (!courseID || !price || !payeeEmail) {
                console.error('Missing required data for PayPal payment');
                return false;
            }

            paypal.Buttons({
                createOrder: (data, actions) => {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: price
                            },
                            payee: {
                                email_address: payeeEmail
                            },
                            description: 'Purchase of ' + courseTitle
                        }]
                    });
                },

                onApprove: (data, actions) => {
                    return actions.order.capture().then(function(orderData) {

                        let ajaxData = {
                            'action': 'exms_save_course_paypal_transactions',
                            'security': EXMS.security,
                            'user_id': userID,
                            'course_id': courseID,
                            'price': price,
                            'order_data': orderData
                        };

                        jQuery.post(EXMS.ajaxURL, ajaxData, function(resp) {

                            let response = JSON.parse(resp);
                            if (response.status == 'false' || response.status == 'error') {
                                alert(response.message || 'Payment failed. Please try again.');
                            } else {
                                alert('Payment completed successfully! You are now enrolled in the course.');
                                location.reload(true);
                            }
                        }).fail(function() {
                            alert('Payment processing failed. Please contact support.');
                        });

                    });
                },

                onError: function(err) {
                    console.error('PayPal error:', err);
                    alert('Payment failed. Please try again or contact support.');
                }
            }).render('#exms-paypal-button-container');
        }

        EXMSCoursePage.init();
    });
})( jQuery );
