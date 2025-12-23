( function( $ ) {
    'use strict';
    $( document ).ready(function() { 

        let EXMS_REPORT_FRONTEND = { 

            /**
             *  Initialize the functionality
             */
            init: function() {
                this.changeReportDropdown();
                this.getReportDataAccordingToInstruction();
                this.workingOnAcademicComment();
                this.workingOnBehaviourComment();
                this.addComments();
                this.changeCommentPosition();
                this.makeReportCommentEditAble();
                this.getUserSpecificData();
                this.backReportData();
            },

            /**
             * back report data
             */
            backReportData: function() {

                $( document ).on( 'click', '.exms-report-back-btn', function() {

                    $( '.exms-std-report-content' ).slideDown();
                    $( '.exms-std-report-detail-content' ).slideUp();
                    $("html, body").animate({ scrollTop: 300 }, 500);
                } );
            },

            /**
             * get user specific data
             */
            getUserSpecificData: function() {

                $( document ).on( 'click', '.exms-student-col', function() {

                    let self = $(this);
                    let courseID = self.attr( 'data-course_id' );
                    let userID = self.attr( 'data-user_id' );
                    let groupID = $( '#exms-frontend-report-group-dropdown' ).val(); 
                    
                    let data = {
                        action     : 'exms_user_specific_report_data',
                        nonce      : EXMS_REPORT.exms_ajax_nonce,
                        group_id   : groupID,
                        course_id  : courseID,
                        user_id    : userID
                    };

                    $.ajax( {
                        url   : EXMS_REPORT.ajaxURL,
                        type  : 'POST',
                        data  : data,
                        dataType: 'json',
                        success: ( response ) => {
                            $( '.exms-report-left-body .exms-report-class' ).text( response.exms_group_name );
                            $( '.exms-report-subject' ).text( response.exms_course_name );
                            $( '.exms-report-course-count' ).text( response.exms_course_count );
                            $( '.exms-std-report-content' ).slideUp();
                            $( '.exms-std-report-detail-content' ).slideDown();
                            $("html, body").animate({ scrollTop: 300 }, 500);
                        },
                    } );
                } );
            },

            /**
             * making report comment editable 
             */
            makeReportCommentEditAble: function() {

                $( document ).on( 'click', '.exms-report-comment-edit', function() {

                    let self = $(this);
                    self.parents( '.exms-comment-row' ).find( '.exms-comment-text' ).attr( 'contenteditable', 'true' );
                    self.parents( '.exms-comment-row' ).find( '.exms-comment-text' ).css({
                        'background'    : '#e4e4e445',
                        'padding'       : '10px'   
                    } );
                } );
            },

            /**
             * changed comment position
             */
            changeCommentPosition: function() {

                /**
                 * convert academic comment into behaviour comment
                 */
                $( document ).on( 'click', '.exms-report-comment', function() {

                    let self   = $(this);
                    let parent = self.closest('.exms-comment-parent');

                    if ( parent.hasClass('exms-behaviour-comment') ) {
                        let childParent = self.parents( '.exms-comment-row' ).html();
                        $( '.exms-behaviour-comment .exms-even-if-better-wrapper' ).append( `
                            <div class="exms-comment-row">
                            ${childParent}
                            </div>
                            ` );
                    } else {
                        
                        let childParent = self.parents( '.exms-comment-row' ).html();
                        $( '.exms-academic-comment .exms-even-if-better-wrapper' ).append( `
                            <div class="exms-comment-row">
                            ${childParent}
                            </div>
                            ` );
                    }
                    self.parents( '.exms-comment-row' ).remove();
                    $( '.exms-even-if-better-wrapper .exms-report-comment' ).removeClass( 'dashicons-no' );
                    $( '.exms-even-if-better-wrapper .exms-report-comment' ).addClass( 'dashicons-yes' );
                    $( '.exms-even-if-better-wrapper .exms-report-comment' ).addClass( 'exms-behaviour-comment-row' );
                    $( '.exms-even-if-better-wrapper .exms-behaviour-comment-row' ).removeClass( 'exms-report-comment' );                    
                } );

                /**
                 * convert behaviour comment into academic comment
                 */
                $( document ).on( 'click', '.exms-behaviour-comment-row', function() {

                    let self   = $(this);
                    let parent = self.closest('.exms-comment-parent');

                    if ( parent.hasClass('exms-behaviour-comment') ) {
                        let childParent = self.parents( '.exms-comment-row' ).html();
                        $( '.exms-behaviour-comment .exms-what-went-well-wrapper' ).append( `
                            <div class="exms-comment-row">
                            ${childParent}
                            </div>
                            ` );
                    } else {

                        let childParent = self.parents( '.exms-comment-row' ).html();
                        $( '.exms-academic-comment .exms-what-went-well-wrapper' ).append( `
                            <div class="exms-comment-row">
                            ${childParent}
                            </div>
                            ` );
                    }
                    self.parents( '.exms-comment-row' ).remove();
                    $( '.exms-what-went-well-wrapper .exms-behaviour-comment-row' ).removeClass( 'dashicons-yes' );
                    $( '.exms-what-went-well-wrapper .exms-behaviour-comment-row' ).addClass( 'dashicons-no' );
                    $( '.exms-what-went-well-wrapper .exms-behaviour-comment-row' ).addClass( 'exms-report-comment' );
                    $( '.exms-what-went-well-wrapper .exms-report-comment' ).removeClass( 'exms-behaviour-comment-row' );                       
                } );
            },

            /**
             * add behaviour comment
             */
            addComments: function() {

                $(document).on('click', '.exms-add-behaviour-comment,.exms-add-academic-comment', function() {

                    let self   = $(this);
                    let parent = self.closest('.exms-comment-parent');
                    let classPrefix = '';
                    let wrapperSelector = '';

                    if ( parent.hasClass('exms-behaviour-comment') ) {
                        classPrefix = 'behaviour';
                        wrapperSelector = '.exms-what-went-well-wrapper';
                    } else {
                        classPrefix = 'academic';
                        wrapperSelector = '.exms-what-went-well-wrapper';
                    }

                    let textarea = parent.find('.exms-add-new-' + classPrefix + '-comment textarea');

                    if ( textarea.length ) {
                        textarea.each(function( index, elem ) {

                            let commentText = $(elem).val().trim();
                            parent.find(wrapperSelector).append(`
                                <div class="exms-comment-row">
                                <div class="exms-comment-text">${commentText}</div>
                                <div class="exms-comment-actions">
                                <button type="button" class="exms-mini-btn" title="Edit">
                                <span class="dashicons dashicons-edit exms-report-comment-edit"></span>
                                </button>
                                <button type="button" class="exms-mini-btn" title="Approve">
                                <span class="dashicons dashicons-no exms-report-comment"></span>
                                </button>
                                </div>
                                </div>
                                `);
                        } );

                        $( '.exms-add-new-'+classPrefix+'-comment' ).remove();
                    }
                } );
            },

            /**
             * working on behavious comment
             */
            workingOnBehaviourComment: function() {

                $( document ).on( 'click', '.exms-behaviour-comment .exms-acc-icon', function() {

                    let container = $( this ).closest( '.exms-behaviour-comment' );
                    let body      = container.find( '.exms-accordion-body' );

                    if ( ! container.find( '.exms-add-new-behaviour-comment' ).length ) {

                        body.before(`
                            <div class="exms-add-new-behaviour-comment">
                            <textarea rows="4"></textarea>
                            </div>
                            `);
                    } else {
                        $( '.exms-add-new-behaviour-comment' ).prepend( `<textarea rows="4"></textarea>` );
                    }

                    if ( ! container.find( '.exms-add-behaviour-comment' ).length ) {

                        container
                        .find( '.exms-add-new-behaviour-comment textarea' )
                        .after(
                            '<input type="button" class="exms-submit-btn exms-add-behaviour-comment" value="Add">'
                            );
                    }
                } );
            },

            /**
             * working on academic comment
             */
            workingOnAcademicComment: function() {

                $( document ).on( 'click', '.exms-academic-comment .exms-acc-icon', function() {

                    let container = $( this ).closest( '.exms-academic-comment' );
                    let body      = container.find( '.exms-accordion-body' );

                    if ( ! container.find( '.exms-add-new-academic-comment' ).length ) {

                        body.before(`
                            <div class="exms-add-new-academic-comment">
                            <textarea rows="4"></textarea>
                            </div>
                            `);
                    } else {
                        $( '.exms-add-new-academic-comment' ).prepend( `<textarea rows="4"></textarea>` );
                    }

                    if ( ! container.find( '.exms-add-academic-comment' ).length ) {

                        container
                        .find( '.exms-add-new-academic-comment textarea' )
                        .after(
                            '<input type="button" class="exms-submit-btn exms-add-academic-comment" value="Add">'
                            );
                    }
                } );
            },

            /**
             * get student report data according to instruction
             */
            getReportDataAccordingToInstruction: function() {

                $( document ).on( 'click', '.exms-std-report-apply-btn', function() {

                    $( '.exms-dropdown-error' ).remove();
                    let groupID = $( '#exms-frontend-report-group-dropdown' ).val();
                    let courseID = $( '#exms-frontend-report-course-dropdown' ).val();
                    let startDate = $( '#exms_report_start_date' ).val();
                    let endDate = $( '#exms_report_end_date' ).val();
                    
                    if ( ! groupID ) {

                        if ( ! $('.exms-dropdown-error').length ) {
                            $( '.exms-std-report-apply-btn' ).before(
                                `<p class="exms-dropdown-error">${EXMS_REPORT.dropdown_error_msg}</p>`
                                );
                        }
                        return false;
                    }

                    $( '.exms-loader' ).css( 'display', 'inline-block' );

                    let reportData = {
                        action     : 'exms_user_report_data',
                        nonce      : EXMS_REPORT.exms_ajax_nonce,
                        group_id   : groupID,
                        course_id  : courseID
                    };

                    $.ajax( {
                        url   : EXMS_REPORT.ajaxURL,
                        type  : 'POST',
                        data  : reportData,
                        dataType: 'json',
                        success: ( response ) => {

                            setTimeout(function () {
                                $( '.exms-loader' ).css( 'display', 'none' );
                                $( '.exms-std-report-data' ).show();
                                $( '.exms-std-report-table tbody' ).html( response.report_content );
                            }, 2000);
                        },
                    } );
                } );
            },

            /**
             * change report dropdown on dropdown
             */
            changeReportDropdown: function() {

                $( document ).on( 'change', '#exms-frontend-report-group-dropdown', function() {

                    let self = $(this);
                    let groupID = self.val();

                    let submitData = {
                        action     : 'exms_report_group_dropdown',
                        nonce      : EXMS_REPORT.exms_ajax_nonce,
                        group_id   : groupID
                    };

                    $.ajax( {
                        url   : EXMS_REPORT.ajaxURL,
                        type  : 'POST',
                        data  : submitData,
                        dataType: 'json',
                        success: ( response ) => {
                            $( '#exms-frontend-report-course-dropdown' ).html( response.content );
                        },
                    } ); 
                } );
            },
        };
        EXMS_REPORT_FRONTEND.init();
    });
})( jQuery );
