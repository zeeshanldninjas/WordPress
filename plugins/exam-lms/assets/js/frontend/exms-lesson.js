( function( $ ) {
    'use strict';
    $( document ).ready(function() { 

        let EXMSLegacyPage = { 

            /**
             *  Initialize the functionality
             */
            init: function() {
                this.HandleLegacyTabs();
                this.HandleNestedSteps();
                this.handleConfirmationPopup();
            },

            handleConfirmationPopup: function() {
                let openBtn = $(".open-confirm-popup");
                let popup = $("#exms-confirm-popup");
                let form = $(".mark-complete-wrapper");

                if (popup.length === 0 || openBtn.length === 0) return;

                let confirmBtn = popup.find(".confirm");
                let cancelBtn = popup.find(".cancel");

                // Open popup
                openBtn.on("click", function(e) {
                    e.preventDefault();
                    popup.fadeIn(200).css("display", "flex");
                });

                // Cancel popup
                cancelBtn.on("click", function(e) {
                    e.preventDefault();
                    popup.fadeOut(200);
                });

                // Confirm action
                confirmBtn.on("click", function(e) {
                    e.preventDefault();
                    popup.fadeOut(200, function() {
                        form.submit();
                    });
                });
            },

            /**
             * Handle Tab Switching
             */
            HandleLegacyTabs: function() {

                $( document ).on( 'click', '.exms-legacy-info-tabs button', function() {

                    let self = $( this );

                    $( '.exms-legacy-info-tabs button' ).removeClass( 'active-tab' );

                    self.addClass( 'active-tab' );

                    $( '.exms-legacy-steps, .legacy-description-tab, .legacy-notice-tab, .legacy-review-tab' ).hide();

                    if ( self.hasClass( 'legacy-content' ) ) {
                        $( '.exms-legacy-steps' ).slideDown( 300 );
                    } else if ( self.hasClass( 'legacy-description' ) ) {
                        $( '.legacy-description-tab' ).slideDown( 300 );
                    } else if ( self.hasClass( 'legacy-notice' ) ) {
                        $( '.legacy-notice-tab' ).slideDown( 300 );
                    } else if ( self.hasClass( 'legacy-review' ) ) {
                        $( '.legacy-review-tab' ).slideDown( 300 );
                    }
                });
            },

            HandleNestedSteps: function() {

                $(document).on('click', '.exms-legacy-course__toggle', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var btn    = $(this);
                    var lesson = btn.closest('.exms-legacy-course__lesson');
                    var sub    = lesson.next('.exms-legacy-course__substeps');

                    if (!sub.length) {
                        return;
                    }

                    var isOpen = lesson.hasClass('exms-legacy-course__lesson--open');

                    if (isOpen) {

                        lesson.removeClass('exms-legacy-course__lesson--open');
                        btn.attr('aria-expanded', 'false');

                        sub.css('max-height', 0);
                        sub.removeClass('exms-legacy-course__substeps--open');
                    } else {

                        lesson.addClass('exms-legacy-course__lesson--open');
                        btn.attr('aria-expanded', 'true');
                        sub.addClass('exms-legacy-course__substeps--open');
                        var fullHeight = 0;
                        sub.children().each(function() {
                            fullHeight += $(this).outerHeight(true);
                        });
                        sub.css('max-height', (fullHeight + 40) + 'px');
                    }
                });

                $(document).on('click', '.exms-legacy-course__lesson-title', function(e) {
                    var btn = $(this).closest('.exms-legacy-course__lesson').find('.exms-legacy-course__toggle').first();
                    if (btn.length) { btn.trigger('click'); }
                });
            },
        };
        EXMSLegacyPage.init();
    });
})( jQuery );
