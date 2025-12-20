( function( $ ) { 'use strict';

	$( document ).ready( function() {
        let EXMS_CACHED_DATA = {};   
        let EXMS_Report_Settings = {

			/**
			 * Initialize functions on load
			 */
			init: function() {
				this.resetSelectFields();
				this.downloadRecord();
				this.applyingSelect2();
				this.reportModalBox();
				this.fileDisplayModal();
				this.reportDataAjax();
				this.submitComment();
				this.storingPostRelations();
				this.reportFilters();
				this.filterButton();
				this.filterPagination();
				this.filterPostTypes();
				this.courseFilterPostType();
				this.userHistory();
				this.loadMorePagination();
				this.liveSearch();
			},

            /**
             * Live search for quizzes/student reports
             * @returns 
             */
            liveSearch: function() {
                let searchInput = $('.exms-student-search-input, .exms-search-input');
                let table = $('.wp-list-table, .exms-report-data-table table');

                if (!searchInput.length || !table.length) return;

                searchInput.on('input', function() {
                    let value = $(this).val().toLowerCase().trim();

                    table.find('tbody tr').each(function() {
                        let row = $(this);
                        let text = row.text().toLowerCase();

                        if (text.indexOf(value) > -1) {
                            row.show().change();
                        } else {
                            row.hide().change();
                        }
                    });
                });
            },

            /**
             * user history in reports details modal box
             */
            userHistory: function() {
                $(document).on('click', '.user-history-link', function(e) {
                    e.preventDefault();

                    let userId = $( this ).data( 'user-id' );
                    $( '.exms-user-history-id' ).val( userId );

                    $('.exms-reports-content, .exms-reports-header').hide().change();
                    $('.exms-user-history-header h2 .exms-quiz-heading').text(''); 
                    $('.exms-user-history-content tbody').html('<tr><td colspan="8">Loading...</td></tr>');

                    $.ajax({
                        url: EXMS_REPORTS.ajaxURL,
                        type: 'POST',
                        data: {
                            action: 'exms_get_user_history',
                            user_id: userId,
                            security: EXMS_REPORTS.security
                        },
                        success: function(response) {
                            if (response.success) {
                                let userName = response.data.user_name ?? 'Unknown User';
                                let records  = response.data.records ?? [];
                                let hasMore  = response.data.has_more;
                                let currentPage = response.data.current_page;

                                $( '.exms-user-history-header' ).css('display', 'flex');
                                $( '.exms-user-history-header .exms-quiz-heading' ).text( userName );
                                $( '.exms-user-history-header' ).show().change();
                                $( '.exms-user-history-content' ).show().change();

                                if( currentPage === 1 ) {
                                    $( '.exms-user-history-content .exms-attempts-table tbody' ).html( '' );
                                }

                                if( records.length > 0 ) {
                                    let rowsHtml = '';
                                    records.forEach(row => {
                                        rowsHtml += `
                                            <tr>
                                                <td>${row.course_name || '-'}</td>
                                                <td>${row.quiz_name || '-'}</td>
                                                <td>${row.percentage ? row.percentage + "%" : "-"}</td>
                                                <td>${row.attempt_date || '-'}</td>
                                                <td>${row.passed || '-'}</td>
                                                <td>${row.attempt_number || '-'}</td>
                                            </tr>
                                        `;
                                    });
                                    $( '.exms-user-history-content .exms-attempts-table tbody' ).append( rowsHtml );
                                } else if( currentPage === 1 ) {
                                    $( '.exms-user-history-content .exms-attempts-table tbody' ).html( '<tr><td colspan="8">No records found.</td></tr>' );
                                }

                                if( hasMore ) {
                                    if( $( '#load_more_history' ).length === 0 ) {
                                        $( '.exms-user-history-content' ).append( '<div class="exms-user-history-load-more"><button id="load_more_history" data-page="2">Load More</button></div>' );
                                    } else {
                                        $( '#load_more_history' ).attr( 'data-page', currentPage + 1 ).show().change();
                                    }
                                } else {
                                    $( '#load_more_history' ).hide().change();
                                }
                            } else {
                                $( '.exms-user-history-content .exms-attempts-table tbody' ).html( '<tr><td colspan="8">Error: ' + response.data + '</td></tr>' );
                            }
                        },

                        error: function( xhr, status, error ) {
                            $( '.exms-user-history-content .exms-attempts-table tbody' ).html( '<tr><td colspan="8">AJAX Error: ' + error + '</td></tr>' );
                        }
                    });
                });
            },

            /**
             * Load More pagination in user history
             */
            loadMorePagination: function() {
                $(document).on('click', '#load_more_history', function(e) {
                    e.preventDefault();
                    let nextPage = parseInt($(this).attr('data-page'));
                    let userId = $('.exms-user-history-id').val();

                    $('#load_more_history').text('Loading...').prop('disabled', true);

                    $.ajax({
                        url: EXMS_REPORTS.ajaxURL,
                        type: 'POST',
                        data: {
                            action: 'exms_get_user_history',
                            user_id: userId,
                            security: EXMS_REPORTS.security,
                            current_page: nextPage
                        },
                        success: function(response) {
                            $('#load_more_history').prop('disabled', false).text('Load More');
                            if (response.success) {
                                let records = response.data.records ?? [];
                                let hasMore = response.data.has_more;

                                if (records.length > 0) {
                                    let rowsHtml = '';
                                    records.forEach(row => {
                                        rowsHtml += `
                                            <tr>
                                                <td>${row.course_name || '-'}</td>
                                                <td>${row.quiz_name || '-'}</td>
                                                <td>${row.percentage ? row.percentage + "%" : "-"}</td>
                                                <td>${row.attempt_date || '-'}</td>
                                                <td>${row.passed || '-'}</td>
                                                <td>${row.attempt_number || '-'}</td>
                                            </tr>
                                        `;
                                    });
                                    $('.exms-user-history-content .exms-attempts-table tbody').append(rowsHtml);
                                }

                                if (hasMore) {
                                    $('#load_more_history').attr('data-page', nextPage + 1);
                                } else {
                                    $('#load_more_history').hide();
                                }
                            }
                        },
                        error: function() {
                            $('#load_more_history').text('Error!').prop('disabled', true);
                        }
                    });
                });
            },

            resetSelectFields: function() {
                $( document ).on( 'click', '.exms-filter-reset-btn', function( e ) {
                    
                    e.preventDefault();
                    $( '#exms_search_query' ).val( 'choose_filters' ).trigger( 'change' );
                });

                $( ".exms-all-filter-reset-btn" ).on( "click", function( e ) {
                    e.preventDefault();

                    $( "#exms_search_query" ).val( "choose_filters" ).trigger( "change" );
                    $( ".exms-filter-field select" ).val( "" ).trigger( "change" );
                    $( "input[type='date']" ).val("");
                    $( ".exms-filter-field" ).hide().change();
                    $( ".exms-dynamic-field" ).show().change();
                    $( ".exms-hierarchy-select, .exms-course-hierarchy-field select" ).prop( "disabled", true );
                    $( ".exms-custom-date-field input" ).prop( "disabled", true );
                    $( ".exms-custom-date-field").hide().change();
                });

            },

            /**
             * Student/Quiz Report Download
             */
            downloadRecord: function() {
                $(document).on('click', '#exms-download-btn', function(e){
                    e.preventDefault();
                    var ids = [];
                    jQuery('.exms-select:checked').each(function(){
                        ids.push(jQuery(this).val());
                    });

                    if(ids.length === 0){
                        alert('Please select at least one record.');
                        return;
                    }

                    var url = window.location.href.split('?')[0];
                    window.location = url + '?exms_download=1&ids=' + ids.join(',');
                });

                $(document).on('click', '#exms-student-download-btn', function(e) {
                    e.preventDefault();

                    var ids = [];
                    $('.exms-select:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length === 0) {
                        alert('Please select at least one record.');
                        return;
                    }

                    var url = window.location.href.split('?')[0];
                    window.location = url + '?exms_student_download=1&ids=' + ids.join(',');
                });

            },

            /**
             * Applying select2 on filters
             */
            applyingSelect2: function() {
                $( '.exms-search-select' ).select2({
                    placeholder: function(){
                        return $( this ).find( 'option:first' ).text();
                    },
                    allowClear: false
                });
            },

            /**
             * Report modal box open/close 
             */
            reportModalBox: function() {
                $( document ).on( 'click', '.exms-report-action', function( e ) {
                    
                    e.preventDefault();
                    let attempts = $(this).data('attempts');
                    $( '.exms-overlay, .exms-reports-modal, .exms-reports-header, .exms-reports-content' ).show().change();
                    EXMS_Report_Settings.reportModalData( attempts );
                });
                
                $( document ).on( 'click', '.exms-student-report-action', function( e ) {
                    
                    e.preventDefault();
                    let studentDetails = $( this ).data( 'student-details' );
                    $( '.exms-student-overlay, .exms-student-reports-modal, .exms-student-reports-header, .exms-student-reports-content' ).show().change();
                    EXMS_Report_Settings.studentReportModalData( studentDetails );
                });
                
                $( document ).on( 'click', '.exms-add-comment', function( e ) {
                    
                    e.preventDefault();
                    $('.exms-comments-modal').addClass('active');
                    let self = $( this );
                    EXMS_Report_Settings.questionComment( self );
                });
                
                $( document ).on( 'click', '.exms-filter-popup', function( e ) {
                    
                    e.preventDefault();
                    $('.exms-search-form').addClass('active');
                    let self = $( this );
                });
                
                $( document ).on( 'click', '.exms-filter-modal-close', function( e ) {
                    
                    e.preventDefault();
                    $('.exms-search-form').removeClass('active');
                });
                
                $( document ).on( 'click', '.exms-comment-modal-close', function( e ) {
                    
                    e.preventDefault();
                     $('.exms-comments-modal').removeClass('active');
                     $( '.exms-unsave-message, .exms-save-message' ).hide().change();
                });

                $( document ).on( 'click', '.exms-overlay, .exms-modal-close', function() {
                    $( '.exms-overlay, .exms-reports-modal' ).hide().change();
                });

                $( document ).on( 'click', '.exms-student-overlay, .exms-student-modal-close', function() {
                    $( '.exms-student-overlay, .exms-student-reports-modal' ).hide().change();
                });
                
                $( document ).on( 'click', '.exms-user-history-modal-close', function() {
                    $( '.exms-overlay, .exms-reports-modal' ).hide().change();
                    $( '.exms-user-history-header, .exms-user-history-content' ).hide().change();
                });

                $( document ).on( 'click', '.exms-file-sidebar-close', function() {
                    $( '.exms-file-sidebar' ).removeClass( 'active' );
                    $( '.exms-file-overlay' ).removeClass( 'active' );
                });       
                
                $( document ).on( 'click', '.exms-back-report', function( e ) {
                    e.preventDefault();
                    $( '.exms-user-history-header, .exms-user-history-content' ).hide().change();
                    $( '.exms-reports-header, .exms-reports-content' ).show().change();
                });       
            },

            /**
             * Display Question Comment in comment popup
             * @param {*} self 
             */
            questionComment: function(self) {

                let qid     = self.data( 'qid' );
                let uid     = self.data( 'uid' );
                let quiz    = self.data( 'quiz' );
                let attempt = self.data( 'attempt' );
                let comment = decodeURIComponent( self.data( 'comment' ) || '' );

                let modal = $( '.exms-comments-modal' );  

                if( comment !== '' ) {
                    modal.find( '.exms-comment-list .exms-comment-item p' ).html( comment );

                    if( tinyMCE.get( 'exms_comment_editor' ) ) {
                        tinyMCE.get( 'exms_comment_editor' ).setContent( comment );
                    } else {
                        $( '#exms_comment_editor' ).val( comment );
                    }

                    modal.find( '.exms-comment-file-preview' ).remove();
                } else {
                    modal.find( '.exms-comment-list .exms-comment-item p' ).html( EXMS_REPORTS.no_comment_text );

                    if( tinyMCE.get( 'exms_comment_editor' ) ) {
                        tinyMCE.get( 'exms_comment_editor' ).setContent( '' );
                    } else {
                        $( '#exms_comment_editor' ).val( '' );
                    }

                    modal.find( '.exms-comment-file-url' ).val( '' );
                    modal.find( '.exms-comment-file-preview' ).remove() ;
                }

                modal.find( '.exms-qid' ).val( qid );
                modal.find( '.exms-uid ').val( uid );
                modal.find( '.exms-quiz' ).val( quiz );
                modal.find( '.exms-attempt-number' ).val( attempt );
            },

            /**
             * Display file that are submitted by when attempting the quiz
             * Display download button
             * Display field to add comment
             */
            fileDisplayModal: function() {
                $( document ).on( 'click', '.exms-file-preview', function( e ){
                    e.preventDefault();

                    let qid = $( this ).data( 'qid' );
                    let uid = $( this ).data( 'uid' );
                    let quiz = $( this ).data( 'quiz' );
                    let attempt = $( this ).data( 'attempt' );
                    let comment = $( this ).data( 'comment' );

                    let fileUrl = $( this ).closest( 'td' ).find( '.exms-file-preview' ).data( 'url' );
                    let extension = fileUrl.split('.').pop().toLowerCase();
                    let allowed = ['jpg','jpeg','png','webp'];

                    if(allowed.includes(extension)) {
                        $('.exms-file-frame').attr('src', fileUrl).show().change();
                    } else {
                        $('.exms-file-frame').attr('src', '').hide().change();
                        window.open(fileUrl, '_blank');
                    }

                    $('.exms-download-file').attr('href', fileUrl);
                    
                    $( '.exms-file-sidebar' ).addClass( 'active' );
                    $( '.exms-file-overlay' ).addClass( 'active' );
                    $( '.exms-qid' ).val( qid );
                    $( '.exms-uid' ).val( uid );
                    $( '.exms-quiz' ).val( quiz );
                    $( '.exms-attempt-number' ).val( attempt );
                });
            },

            /**
             * Report modalbox details
             * @param {*} attempts 
             */
            reportModalData: function( attempts ) {
                let rowsHtml = '';
                if( attempts && attempts.additional ) {
                    let timestamp = attempts.additional.result_date ?? null;
                    if( timestamp ) {
                        let date = new Date(timestamp * 1000);
                        let options = { 
                            year: "numeric", 
                            month: "long", 
                            day: "numeric", 
                            hour: "numeric", 
                            minute: "numeric", 
                            hour12: true 
                        };
                        $('.exms-date').html( " " + date.toLocaleString('en-US', options) );
                    } else {
                        $('.exms-date').text('-');
                    }
                    $( '.exms-quiz-heading' ).text( attempts.quiz_name ?? '-' );
                    $( '.exms-username' ).html(
                        attempts.user_name
                            ? `${attempts.user_name} <a href="#" class="user-history-link" data-user-id="${attempts.additional.user_id}"> User History </a>`
                            : '-'
                        );
                    $( '.exms-attempt' ).text( attempts.additional.attempt_number ?? '-' );
                    $( '.exms-percentage' ).text( ( attempts.additional.percentage ?? '-' ) );
                    $( '.exms-obtained' ).text( attempts.additional.obtained_points ?? '-' );
                    $( '.exms-wrong-answers' ).text( attempts.additional.wrong_questions ?? '-' );
                    $( '.exms-correct-answers' ).text( attempts.additional.correct_questions ?? '-' );
                    $( '.exms-review' ).text( attempts.additional.review_questions ?? '-' );
                    $( '.exms-course-instructor' ).text( attempts.additional.instructor_name ?? '-' );
                    let isPassed = attempts.additional.passed == 1;
                    $( '.exms-quiz-pass' )
                        .text(isPassed ? 'Passed' : 'Failed')
                        .removeClass('pass fail')
                        .addClass(isPassed ? 'pass' : 'fail');
                    let passMsg = attempts.additional.pass_quiz_message || '';
                    let failMsg = attempts.additional.fail_quiz_message || '';
                    let percentage = attempts.additional.percentage || 0;
                    let requiredPercentage = attempts.additional.passing_percentage || 0;
                    let userName = attempts.user_name || '';
                    let quizName = attempts.quiz_name || '';
                    let courseName = attempts.course_name || '';
                    let score = attempts.additional.obtained_points || 0;
                    let correct = attempts.additional.correct_questions || 0;
                    let wrong = attempts.additional.wrong_questions || 0;
                    let pending = attempts.additional.not_attempt || 0;
                    let instructorName = attempts.additional.instructor_name || '-';

                    let messageTemplate = isPassed ? passMsg : failMsg;

                    let message = messageTemplate
                        .replace(/\{quiz_name\}/g, quizName)
                        .replace(/\{course_name\}/g, courseName)
                        .replace(/\{result\}/g, isPassed ? 'Pass' : 'Fail')
                        .replace(/\{score\}/g, score)
                        .replace(/\{percentage\}/g, percentage)
                        .replace(/\{required_percentage\}/g, requiredPercentage)
                        .replace(/\{correct_answers\}/g, correct)
                        .replace(/\{wrong_answers\}/g, wrong)
                        .replace(/\{pending_review\}/g, pending)
                        .replace(/\{user_name\}/g, userName)
                        .replace(/\{instructor_name\}/g, instructorName);

                    if( message != "") {
                        $('.exms-quiz-message').html( message );
                    } else {
                        $('.exms-quiz-message').hide().change();
                    }
                    $( '.exms-time' ).text( EXMS_Report_Settings.formatTimeDisplay(attempts.additional.time_taken) ?? '-' );
                    $( '.exms-course-name' ).text( attempts.course_name ?? '-' );
                    $( '.exms-quiz-total-time' ).text( EXMS_Report_Settings.formatTimeDisplay(attempts.additional.quiz_timer) ?? '-' );
                    $( '.exms-quiz-passing-percentage' ).text( attempts.additional.passing_percentage ?? '-' );
                }

                if (attempts && attempts.attempts && attempts.attempts.length) {
    attempts.attempts.forEach(function (a) {
        rowsHtml += '<tr>';
        rowsHtml += '<td>' + a.question_desc + '</td>';
        rowsHtml += '<td>' + a.question_type + '</td>';

        if (a.question_type === 'file_upload' || a.question_type === 'essay') {
            if (a.file_url) {
                let fileName = a.file_url.split('/').pop();
                rowsHtml += '<td>-</td>';
                rowsHtml += '<td>' + a.correct_answer + '</td>';
            } else {
                rowsHtml += '<td>-</td>';
                rowsHtml += '<td>' + a.correct_answer + '</td>';
            }
        } else {
            let answerText = '-';
            if (a.answer) {
                try {
                    let parsed = JSON.parse(a.answer);
                    if (typeof parsed === 'object') {
                        answerText = Array.isArray(parsed.answer)
                            ? parsed.answer.join(', ')
                            : (parsed.answer || JSON.stringify(parsed));
                    } else {
                        answerText = parsed;
                    }
                } catch (e) {
                    answerText = a.answer;
                }
            }
            rowsHtml += '<td>' + answerText + '</td>';
            rowsHtml += '<td>' + a.correct_answer + '</td>';
        }

        /* ---------------------------
           ⭐ ADD CATEGORY COLUMN
        ---------------------------- */
        let categoryHtml = '-';
        if (a.categories && a.categories.length) {
            categoryHtml = a.categories.map(c => c.name).join(', ');
        }
        rowsHtml += '<td>' + categoryHtml + '</td>';

        /* ---------------------------
           ⭐ ADD TAG COLUMN
        ---------------------------- */
        let tagHtml = '-';
        if (a.tags && a.tags.length) {
            tagHtml = a.tags.map(t => t.name).join(', ');
        }
        rowsHtml += '<td>' + tagHtml + '</td>';

        /* ---------------------------
           FILE COLUMN (existing)
        ---------------------------- */
        if (a.question_type === 'file_upload' || a.question_type === 'essay') {
            if (a.file_url) {
                let fileName = a.file_url.split('/').pop();
                rowsHtml += '<td>' +
                    '<a href="#" class="exms-file-preview" data-comment="' + a.comment + '" data-url="' + a.file_url + '">' +
                    fileName +
                    '</a>' +
                    '</td>';
            } else {
                rowsHtml += '<td>-</td>';
            }
        } else {
            rowsHtml += '<td>-</td>';
        }

        /* ---------------------------
           STATUS COLUMN (existing)
        ---------------------------- */
        if (a.is_correct === 'pending') {
            rowsHtml += '<td style="min-width: 140px;">' +
                '<button class="exms-btn exms-accept" ' +
                'data-qid="' + a.question_id + '" ' +
                'data-uid="' + attempts.additional.user_id + '" ' +
                'data-quiz="' + attempts.additional.quiz_id + '" ' +
                'data-attempt="' + a.attempt_number + '">Accept</button> ' +

                '<button class="exms-btn exms-decline" ' +
                'data-qid="' + a.question_id + '" ' +
                'data-uid="' + attempts.additional.user_id + '" ' +
                'data-quiz="' + attempts.additional.quiz_id + '" ' +
                'data-attempt="' + a.attempt_number + '">Decline</button>' +
                '</td>';
        } else if (a.is_correct === '0' || a.is_correct === 0) {
            rowsHtml += '<td><span class="exms-status exms-wrong">Wrong</span></td>';
        } else if (a.is_correct === '1' || a.is_correct === 1) {
            rowsHtml += '<td><span class="exms-status exms-correct">Correct</span></td>';
        } else if (a.is_correct === 'not-attempted') {
            rowsHtml += '<td>-</td>';
        } else {
            rowsHtml += '<td>-</td>';
        }

        /* ---------------------------
           COMMENT COLUMN (existing)
        ---------------------------- */
        rowsHtml += '<td><a href="#" class="exms-add-comment" ' +
            'data-comment="' + encodeURIComponent(a.comment || '') + '" ' +
            'data-qid="' + a.question_id + '" ' +
            'data-uid="' + attempts.additional.user_id + '" ' +
            'data-quiz="' + attempts.additional.quiz_id + '" ' +
            'data-attempt="' + a.attempt_number + '">' +
            EXMS_REPORTS.add_comment_text + '</a></td>';

        rowsHtml += '</tr>';
    });
}  else {
                    rowsHtml = '<tr><td colspan="7">' + EXMS_REPORTS.no_attempt_text +'</td></tr>';
                }
                $( '.exms-attempts-table tbody' ).html( rowsHtml );
            },
            
            /**
             * Student Report modalbox details
             * @param {*} attempts 
             */
            studentReportModalData: function (studentDetails) {
                
                if(!studentDetails || typeof studentDetails !== 'object') return;
                
                let name       = studentDetails.student_name ?? '-';
                let email      = studentDetails.student_details?.email ?? '-';
                let registered = studentDetails.student_details?.registered ?? '-';
                let total      = studentDetails.stats?.total_courses ?? '-';
                let completed  = studentDetails.stats?.completed_courses ?? '-';
                let incomplete = studentDetails.stats?.incomplete_courses ?? '-';

                $( '.exms-student-name' ).text( name );
                $( '.exms-student-email' ).text (email );
                $( '.exms-student-join' ).text( registered );
                $( '.exms-student-enrolled-courses' ).text( total );
                $( '.exms-student-complete-courses' ).text( completed );
                $( '.exms-student-incomplete-courses' ).text( incomplete );

                let courseRowsHtml = '';
                let detailsList = Array.isArray( studentDetails.course_details ) ? studentDetails.course_details : [];

                if( detailsList.length ) {
                    detailsList.forEach( function( c ) {
                    let courseName   = c.course_name || '-';
                    let enrolledById = (c.enrolled_by ?? '');
                    let enrolledByNm = c.enrolled_by_name || '-';
                    if(enrolledByNm !== '-') {
                        enrolledByNm = enrolledByNm.charAt(0).toUpperCase() + enrolledByNm.slice(1);
                    }

                    let progRaw      = (c.progress_percent ?? '').toString().trim().toLowerCase();
                    let progress;
                    if( progRaw === '100' || progRaw === '100%' ) {
                        progress = '<span class="exms-status exms-correct">Completed</span>';
                    } else if( progRaw === 'in progress' ) {
                        progress = '<span class="exms-status exms-inprogress">In Progress</span>';
                    } else if( progRaw === '0' || progRaw === '0%' ) {
                        progress = '<span class="exms-status exms-not-started">Not Started</span>';
                    } else if( progRaw === '' || progRaw === null || progRaw === undefined ) {
                        progress = '-';
                    } else {
                        progress = progRaw;
                    }
                    let startDate    = c.start_date || '-';
                    let endDate      = c.end_date || '-';

                    let studentId = studentDetails.student_details?.id ?? 0;
                    if( parseInt( enrolledById ) === parseInt( studentId ) ) {
                        enrolledByNm = 'Auto Enrolled';
                    }
                    courseRowsHtml += '<tr>' +
                        '<td>' + courseName + '</td>' +
                        '<td>' + progress + '</td>' +
                        '<td data-enrolled-by-id="' + enrolledById + '">' + enrolledByNm + '</td>' +
                        '<td>' + startDate + '</td>' +
                        '<td>' + endDate + '</td>' +
                    '</tr>';
                    });
                } else {
                    const noCoursesText = (window.EXMS_REPORTS && EXMS_REPORTS.no_courses_text) ? EXMS_REPORTS.no_courses_text : '-';
                    courseRowsHtml = '<tr><td colspan="5">' + noCoursesText + '</td></tr>';
                }
                $('.exms-student-course-details-table tbody').html(courseRowsHtml);
            },

            /**
             * Reports ajax for accept,decline file upload, essay answer
             */
            reportDataAjax: function() {
                $( document ).on( 'click', '.exms-accept, .exms-decline', function( e ) {
                    e.preventDefault();

                    let btn   = $( this );
                    let qid     = btn.data( 'qid' );
                    let uid     = btn.data( 'uid' );
                    let quiz_id = btn.data( 'quiz' );
                    let attempt = btn.data( 'attempt' );
                    let action = btn.hasClass( 'exms-accept' ) ? 'accept' : 'decline';

                    $.ajax({
                        url: EXMS_REPORTS.ajaxURL,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'exms_update_attempt_status',
                            question_id: qid,
                            user_id: uid,
                            quiz_id: quiz_id,
                            attempt_number: attempt,
                            decision: action,
                            security: EXMS_REPORTS.security
                        },
                        beforeSend: function() {
                            btn.prop( 'disabled', true ).text( EXMS_REPORTS.processing );
                        },
                        success: function( res ) {
                            if( res.success ) {
                                if( action === 'accept' ) {
                                    btn.closest('td').html('<span class="exms-status exms-correct">Correct</span>');
                                } else {
                                    btn.closest('td').html('<span class="exms-status exms-wrong">Wrong</span>');
                                }

                                if( res.data && res.data.percentage !== undefined ) {
                                    let selector = '.exms-percentage-col[data-user="'+res.data.user_id+'"][data-quiz="'+res.data.quiz_id+'"][data-attempt="'+res.data.attempt+'"]';
                                    $( selector ).text( res.data.percentage );
                                }

                                let linkSelector = '.exms-report-action[data-attempts]';
                                $(linkSelector).each(function(){
                                    let link = $(this);
                                    let raw = link.attr('data-attempts');
                                    if(!raw) return;
                                        let parsed = JSON.parse(raw);

                                        if( parsed.additional.user_id == res.data.user_id &&
                                            parsed.additional.quiz_id == res.data.quiz_id &&
                                            parsed.additional.attempt_number == res.data.attempt ) {
                                            parsed.additional = res.data.additional;
                                            parsed.percentage = res.data.percentage;

                                            if(parsed.attempts && parsed.attempts.length){
                                                parsed.attempts = parsed.attempts.map(q => {
                                                    if(q.question_id == res.data.question_id && q.attempt_number == res.data.attempt){
                                                        q.is_correct = (action === 'accept') ? '1' : '0';
                                                    }
                                                    return q;
                                                });
                                            }

                                            let newJson = JSON.stringify(parsed);
                                            link.attr('data-attempts', newJson);
                                            link.data('attempts', parsed);
                                        }
                                });

                                if( res.data && res.data.additional ) {
                                    
                                    let add = res.data.additional;
                                    if( add.percentage !== undefined ) $('.exms-percentage').text(add.percentage);
                                    if( add.obtained_points !== undefined ) $('.exms-obtained').text(add.obtained_points);
                                    if( add.correct_questions !== undefined ) $('.exms-correct-answer').text(add.correct_questions);
                                    if( add.wrong_questions !== undefined ) $('.exms-wrong-answer').text(add.wrong_questions);
                                    if( add.review_questions !== undefined ) $('.exms-review').text(add.review_questions);
                                    if( add.time_taken !== undefined ) $('.exms-time').text(add.time_taken);
                                }
                            } else {
                                $.alert( res.data || 'Error occurred.' );
                                btn.prop('disabled', false).text( action === 'accept' ? EXMS_REPORTS.approve_text : EXMS_REPORTS.wrong_text );
                            }
                        }

                    });
                });
            },

            /**
             * Ajax for comment submit
             */
            submitComment: function() {
                $( document ).on( 'click', '.exms-submit-comment', function( e ){
                    e.preventDefault();

                    let btn = $( this );

                    let editorContent = '';
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('exms_comment_editor')) {
                        editorContent = tinyMCE.get('exms_comment_editor').getContent();
                    } else {
                        editorContent = $('#exms_comment_editor').val();
                    }

                    $.ajax({
                        url: EXMS_REPORTS.ajaxURL,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'exms_save_comment',
                            comment: editorContent,
                            question_id: $( '.exms-qid' ).val(),
                            user_id: $( '.exms-uid' ).val(),
                            quiz_id: $( '.exms-quiz' ).val(),
                            attempt_number: $( '.exms-attempt-number' ).val(),
                            security: EXMS_REPORTS.security
                        },
                        beforeSend: function() {
                            btn.prop( 'disabled', true ).text (EXMS_REPORTS.processing );
                        },
                        success: function(res) {
                            if(res.success){
                                $( '.exms-save-message' ).html( EXMS_REPORTS.comment_saved_text ).show().change();
                                $( '.exms-unsave-message' ).html( "" ).hide().change();
                                if (tinyMCE.get('exms_comment_editor')) {
                                    tinyMCE.get('exms_comment_editor').setContent(res.data.comment);
                                } else {
                                    $('#exms_comment_editor').val(res.data.comment);
                                }
                                $('.exms-comment-list').html(`
                                    <div class="exms-comment-item">
                                        ${res.data.comment}
                                    </div>
                                `);

                                let encodedComment = encodeURIComponent( res.data.comment );
                                let qid     = $( '.exms-qid' ).val();
                                let uid     = $( '.exms-uid' ).val();
                                let quiz    = $( '.exms-quiz' ).val();
                                let attempt = $( '.exms-attempt-number' ).val();
                                let link = $( '.exms-add-comment' ).filter(
                                    '[data-qid="'+qid+'"]' +
                                    '[data-uid="'+uid+'"]' +
                                    '[data-quiz="'+quiz+'"]' +
                                    '[data-attempt="'+attempt+'"]'
                                );

                                link.attr( 'data-comment', encodedComment ).data( 'comment', encodedComment );
                                
                                $('.exms-comment-file-preview').remove();
                            } else {
                                $( '.exms-unsave-message' ).html( res.data || EXMS_REPORTS.error_occur_text ).show().change();
                                $( '.exms-save-message' ).html( "" ).hide().change();
                            }
                            btn.prop( 'disabled', false ).text( 'Submit' );
                        }
                    });
                });
            },

            /**
             * Storing Post type relationship data
             */
            storingPostRelations: function() {
                if( localStorage.getItem("EXMS_CACHED_DATA") ) {
                    localStorage.removeItem("EXMS_CACHED_DATA");
                }
                $.ajax( {
                    url: EXMS_REPORTS.ajaxURL,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'exms_get_all_child_posts'
                    },
                    success: function( response ) {
                        EXMS_CACHED_DATA = response;
                        localStorage.setItem( "EXMS_CACHED_DATA", JSON.stringify( response ) );
                    }
                } );
            },

            /**
             * Reports Filters
             */
            reportFilters: function() {
                $( "#exms_search_query" ).on( "change", function() {
                    let filter = $( this ).val();
                    let dynamicSelect = $( "#exms_dynamic_filter" );
                    dynamicSelect.empty().append( '<option value="">' + "Select Option" + '</option>' );

                    $( ".exms-hierarchy-field" ).hide().change();
                    $( ".exms-course-hierarchy-field" ).hide().change();
                    $(".exms-custom-date-field").hide().change();

                    if( filter && filter !== "choose_filters" ) {
                        $(".exms-dynamic-field").show().change();

                        let options = [];
                        let selectName = "exms_dynamic_filter";

                        switch( filter ) {
                            case "exms_students":
                                selectName = "exms_user_name";
                                options = EXMS_REPORTS.filters.users.map( user => ( {
                                    value: user.ID,
                                    label: user.display_name
                                } ) );
                                break;

                            case "exms_quiz_name":
                                selectName = "exms_quiz_name";
                                options = EXMS_REPORTS.filters.quizzes.map( q => ( {
                                    value: q.ID,
                                    label: q.post_title
                                } ) );
                                break;

                            case "exms_group_name":
                                selectName = "exms_group_name";
                                $( ".exms-hierarchy-field" ).show().change();
                                options = EXMS_REPORTS.filters.groups.map( g => ( {
                                    value: g.ID,
                                    label: g.post_title
                                } ) );
                                break;

                            case "exms_course_name":
                                selectName = "exms_course_name";
                                $( ".exms-course-hierarchy-field" ).show().change();
                                options = EXMS_REPORTS.filters.courses.map( c => ( {
                                    value: c.ID,
                                    label: c.post_title
                                } ) );
                                break;

                            case "exms_tags":
                                selectName = "exms_quiz_tags";
                                options = EXMS_REPORTS.filters.tags.map( t => ( {
                                    value: t.term_id,
                                    label: t.name
                                } ) );
                                break;

                            case "exms_category":
                                selectName = "exms_quiz_category";
                                options = EXMS_REPORTS.filters.cats.map( cat => ( {
                                    value: cat.term_id,
                                    label: cat.name
                                } ) );
                                break;

                            case "exms_date":
                                selectName = "exms_date_field";
                                options = EXMS_REPORTS.filters.dates.map( d => ( {
                                    value: d.value,
                                    label: d.label
                                } ) );
                                break;
                        }

                        dynamicSelect.attr( "name", selectName );

                        options.forEach( opt => {
                            dynamicSelect.append( `<option value="${opt.value}">${opt.label}</option>` );
                        } );
                    }
                });

                $( document ).on( "change", "#exms_dynamic_filter", function() {
                    if ( $( this ).val() === "custom_date" ) {
                        $( ".exms-custom-date-field" ).show().change();
                        $( ".exms-custom-date-field input" ).prop( "disabled", false );
                    } else {
                        $( ".exms-custom-date-field" ).hide().change();
                        $( ".exms-custom-date-field input" ).prop( "disabled", true ).val( "" );
                    }
                });
            },
			
            /**
             * Filters ajax
             */
            filterButton: function() {
                $("button[name='exms_search_submit']").on("click", function(e) {
                    e.preventDefault();

                    let userId    = $("select[name='exms_user_name']").val();
                    let quizId    = $("select[name='exms_quiz_name']").val();
                    let groupId   = $("select[name='exms_group_name']").val();
                    let courseId  = $("select[name='exms_course_name']").val();
                    let tagId     = $("select[name='exms_quiz_tags']").val();
                    let catId     = $("select[name='exms_quiz_category']").val();
                    let period    = $("select[name='exms_date_field']").val();
                    let startDate = $("input[name='exms_start_date']").val();
                    let endDate   = $("input[name='exms_end_date']").val();

                    let hierarchy = {};
                    let courseHierarchy = {};

                    $(".exms-hierarchy-field .exms-hierarchy-select").each(function() {
                        let name = $(this).attr("name").replace("[]", "");
                        let val  = $(this).val();
                        if (val) {
                            hierarchy[name] = val;
                        }
                    });

                    $(".exms-course-hierarchy-field .exms-hierarchy-select").each(function() {
                        let name = $(this).attr("name").replace("[]", "");
                        let val  = $(this).val();
                        if (val) {
                            courseHierarchy[name] = val;
                        }
                    });

                    $.ajax({
                        url: EXMS_REPORTS.ajaxURL,
                        method: "POST",
                        data: {
                            action: 'exms_filter_action',
                            exms_user_name: userId,
                            exms_quiz_name: quizId,
                            exms_group_name: groupId,
                            exms_course_name: courseId,
                            exms_quiz_tags: tagId,
                            exms_quiz_category: catId,
                            exms_date_field: period,
                            exms_start_date: startDate,
                            exms_end_date: endDate,
                            hierarchy: hierarchy,
                            course_hierarchy: courseHierarchy,
                            page: 1,
                        },
                        success: function(response) {
                            if (response.success) {
                                let html = '';
                                $('.exms-report-data-table tbody').empty();
                                $('.exms-search-form').removeClass('active');

                                $.each(response.data.items, function(i, row) {
                                    html += `<tr>
                                        <td>${row.cb}</td>
                                        <td>${row.exms_name}</td>
                                        <td>${row.exms_user}</td>
                                        <td>${row.exms_percentage}</td>
                                        <td>${row.exms_date}</td>
                                        <td>${row.exms_action}</td>
                                    </tr>`;
                                });
                                $(".exms-report-data-table tbody").html(html);

                                let paginationHtml = '';
                                if(response.data.total_pages > 1) {
                                    paginationHtml += '<span class="displaying-num">' + response.data.total + ' items</span>';
                                    paginationHtml += '<span class="pagination-links">';

                                    if (response.data.current_page > 1) {
                                        paginationHtml += '<a class="first-page button exms-page-btn" data-page="1" href="#">&laquo;</a>';
                                        paginationHtml += '<a class="prev-page button exms-page-btn" data-page="' + (response.data.current_page - 1) + '" href="#">&lsaquo;</a>';
                                    } else {
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&laquo;</span>';
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&lsaquo;</span>';
                                    }

                                    paginationHtml += '<span class="paging-input">';
                                    paginationHtml += '<span class="current-page">' + response.data.current_page + '</span> of <span class="total-pages">' + response.data.total_pages + '</span>';
                                    paginationHtml += '</span>';

                                    if (response.data.current_page < response.data.total_pages) {
                                        paginationHtml += '<a class="next-page button exms-page-btn" data-page="' + (response.data.current_page + 1) + '" href="#">&rsaquo;</a>';
                                        paginationHtml += '<a class="last-page button exms-page-btn" data-page="' + response.data.total_pages + '" href="#">&raquo;</a>';
                                    } else {
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&rsaquo;</span>';
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&raquo;</span>';
                                    }

                                    paginationHtml += '</span>';
                                }

                                $(".tablenav-pages").html(paginationHtml);

                            }
                        }
                    });
                });

                $("#cb-select-all").off("change").on("change", function() {
                    var checked = $(this).is(":checked");
                    $(".exms-report-data-table tbody .exms-select").prop("checked", checked);
                });
            },

            /**
             * Filter Pagination
             */
            filterPagination: function() {
                $(document).on("click", ".exms-page-btn", function(e) {
                    e.preventDefault();
                    let page = $(this).data("page");

                    let userId    = $("select[name='exms_user_name']").val();
                    let quizId    = $("select[name='exms_quiz_name']").val();
                    let groupId   = $("select[name='exms_group_name']").val();
                    let courseId  = $("select[name='exms_course_name']").val();
                    let tagId     = $("select[name='exms_quiz_tags']").val();
                    let catId     = $("select[name='exms_quiz_category']").val();
                    let period    = $("select[name='exms_date_field']").val();
                    let startDate = $("input[name='exms_start_date']").val();
                    let endDate   = $("input[name='exms_end_date']").val();

                    let hierarchy = {};
                    let courseHierarchy = {};

                    $(".exms-hierarchy-field .exms-hierarchy-select").each(function() {
                        let name = $(this).attr("name").replace("[]", "");
                        let val  = $(this).val();
                        if (val) {
                            hierarchy[name] = val;
                        }
                    });

                    $(".exms-course-hierarchy-field .exms-hierarchy-select").each(function() {
                        let name = $(this).attr("name").replace("[]", "");
                        let val  = $(this).val();
                        if (val) {
                            courseHierarchy[name] = val;
                        }
                    });

                    $.ajax({
                        url: EXMS_REPORTS.ajaxURL,
                        method: "POST",
                        data: {
                            action: 'exms_filter_action',
                            exms_user_name: userId,
                            exms_quiz_name: quizId,
                            exms_group_name: groupId,
                            exms_course_name: courseId,
                            exms_quiz_tags: tagId,
                            exms_quiz_category: catId,
                            exms_date_field: period,
                            exms_start_date: startDate,
                            exms_end_date: endDate,
                            hierarchy: hierarchy,
                            course_hierarchy: courseHierarchy,
                            page: page,
                        },
                        success: function(response) {
                            if (response.success) {
                                let html = '';
                                $.each(response.data.items, function(i, row) {
                                    html += `<tr>
                                        <td>${row.exms_name}</td>
                                        <td>${row.exms_user}</td>
                                        <td>${row.exms_percentage}</td>
                                        <td>${row.exms_date}</td>
                                        <td>${row.exms_action}</td>
                                    </tr>`;
                                });
                                $(".exms-report-data-table tbody").html(html);

                                let paginationHtml = '';
                                if (response.data.total_pages > 1) {
                                    paginationHtml += '<span class="displaying-num">' + response.data.total + ' items</span>';
                                    paginationHtml += '<span class="pagination-links">';

                                    if (response.data.current_page > 1) {
                                        paginationHtml += '<a class="first-page button exms-page-btn" data-page="1" href="#">&laquo;</a>';
                                        paginationHtml += '<a class="prev-page button exms-page-btn" data-page="' + (response.data.current_page - 1) + '" href="#">&lsaquo;</a>';
                                    } else {
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&laquo;</span>';
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&lsaquo;</span>';
                                    }
                                    paginationHtml += '<span class="paging-input">';
                                    paginationHtml += '<span class="current-page">' + response.data.current_page + '</span> of <span class="total-pages">' + response.data.total_pages + '</span>';
                                    paginationHtml += '</span>';
                                    if (response.data.current_page < response.data.total_pages) {
                                        paginationHtml += '<a class="next-page button exms-page-btn" data-page="' + (response.data.current_page + 1) + '" href="#">&rsaquo;</a>';
                                        paginationHtml += '<a class="last-page button exms-page-btn" data-page="' + response.data.total_pages + '" href="#">&raquo;</a>';
                                    } else {
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&rsaquo;</span>';
                                        paginationHtml += '<span class="tablenav-pages-navspan button disabled">&raquo;</span>';
                                    }

                                    paginationHtml += '</span>';
                                }
                                $(".tablenav-pages").html(paginationHtml);
                            }
                        }
                    });
                });
            },

            /**
             * Displaying Filter by group post types according to the relationship
             */
            filterPostTypes: function() {

                $( document ).on( 'change', 'select[name="exms_group_name"]', function() {
                    let groupId = $( this ).val();
                    let wrapper = $( '.exms-hierarchy-field' );
                    let firstSelect = wrapper.find( '.exms-hierarchy-select' ).first();

                    wrapper.find( '.exms-hierarchy-select' ).each(function(){
                        let nType = $(this).attr('name').replace('[]','').replace('exms-','');
                        nType = nType.charAt(0).toUpperCase() + nType.slice(1);
                        $(this).empty()
                            .append('<option value="">Select '+ nType +'</option>')
                            .prop('disabled', true);
                    });

                    if( groupId && EXMS_CACHED_DATA[groupId] && EXMS_CACHED_DATA[groupId]['exms-courses'] ) {
                        let courses = EXMS_CACHED_DATA[groupId]['exms-courses'];
                        if( courses.length > 0 ) {
                            $.each( courses, function( i, item ) {
                                firstSelect.append( '<option value="'+ item.id +'">'+ item.title +'</option>' );
                            });
                            firstSelect.prop('disabled', false);
                        }
                    }
                });

                $( document ).on( 'change', '.exms-hierarchy-select', function() {
                    let self = $( this );
                    let selectedVal = self.val();
                    let wrapper = self.closest( '.exms-hierarchy-field' );
                    let parentData = EXMS_CACHED_DATA[selectedVal] || {};

                    wrapper.find('.exms-hierarchy-select').each(function(idx){
                        if ( $( this ).hasClass( 'select2-hidden-accessible' ) ) {
                            $( this ).select2( 'destroy' );
                        }
                        $( this ).select2( {
                            placeholder: function(){
                                return $( this ).find( 'option:first' ).text();
                            },
                            allowClear: false
                        });
                        if( idx > wrapper.find('.exms-hierarchy-select').index(self) ) {
                            let nType = $(this).attr('name').replace('[]','').replace('exms-','');
                            nType = nType.charAt(0).toUpperCase() + nType.slice(1);
                            $(this).empty()
                                .append('<option value="">Select '+ nType +'</option>')
                                .prop('disabled', true);
                        }
                    });

                    $.each(parentData, function(childKey, items){
                        let childSelect = wrapper.find('.exms-hierarchy-select[name="'+ childKey +'[]"]');
                        if( childSelect.length ) {
                            let childType = childKey.replace('exms-','');
                            childType = childType.charAt(0).toUpperCase() + childType.slice(1);
                            childSelect.empty().append('<option value="">Select '+ childType +'</option>');
                            $.each(items, function(i, item){
                                childSelect.append('<option value="'+ item.id +'">'+ item.title +'</option>');
                            });
                            if( items.length > 0 ) {
                                childSelect.prop('disabled', false);
                            }
                        }
                    });

                });
            },

            /**
             * Displaying Filter by course post types according to the relationship
             */
            courseFilterPostType: function() {
                $( document ).on( 'change', 'select[name="exms_course_name"]', function() {
                    let courseId = $( this ).val();
                    let wrapper = $( '.exms-course-hierarchy-field' );
                    let firstSelect = wrapper.find( '.exms-hierarchy-select' ).first();

                    wrapper.find( '.exms-hierarchy-select' ).each(function(){
                        let nType = $(this).attr('name').replace('[]','').replace('exms-','');
                        nType = nType.charAt(0).toUpperCase() + nType.slice(1);
                        $(this).empty().append('<option value="">Select '+ nType +'</option>').prop('disabled', true);
                    });

                    if( courseId && EXMS_CACHED_DATA[courseId] ) {
                        let parentData = EXMS_CACHED_DATA[courseId];
                        $.each(parentData, function(childKey, items){
                            let childSelect = wrapper.find('.exms-hierarchy-select[name="'+ childKey +'[]"]');
                            if( childSelect.length ) {
                                let childType = childKey.replace('exms-','');
                                childType = childType.charAt(0).toUpperCase() + childType.slice(1);
                                childSelect.empty().append('<option value="">Select '+ childType +'</option>');
                                $.each(items, function(i, item){
                                    childSelect.append('<option value="'+ item.id +'">'+ item.title +'</option>');
                                });
                                if( items.length > 0 ) {
                                    childSelect.prop('disabled', false);
                                }
                            }
                        });
                    }
                });

                $( document ).on( 'change', '.exms-hierarchy-select', function() {
                    let self = $( this );
                    let selectedVal = self.val();
                    let wrapper = self.closest( '.exms-course-hierarchy-field' );

                    let index = wrapper.find('.exms-hierarchy-select').index(self);
                    wrapper.find('.exms-hierarchy-select').slice(index+1).each(function(){
                        let nType = $(this).attr('name').replace('[]','').replace('exms-','');
                        nType = nType.charAt(0).toUpperCase() + nType.slice(1);
                        $(this).empty().append('<option value="">Select '+ nType +'</option>').prop('disabled', true);
                    });

                    if( selectedVal && EXMS_CACHED_DATA[selectedVal] ) {
                        let parentData = EXMS_CACHED_DATA[selectedVal];
                        $.each(parentData, function(childKey, items){
                            let childSelect = wrapper.find('.exms-hierarchy-select[name="'+ childKey +'[]"]');
                            if( childSelect.length ) {
                                let childType = childKey.replace('exms-','');
                                childType = childType.charAt(0).toUpperCase() + childType.slice(1);
                                childSelect.empty().append('<option value="">Select '+ childType +'</option>');
                                $.each(items, function(i, item){
                                    childSelect.append('<option value="'+ item.id +'">'+ item.title +'</option>');
                                });
                                if( items.length > 0 ) {
                                    childSelect.prop('disabled', false);
                                }
                            }
                        });
                    }
                });
            },

            formatTimeDisplay: function(timeString) {

                if (!timeString || typeof timeString !== 'string') return '-';
                let parts = timeString.split(':').map(Number);

                let hours = 0, minutes = 0, seconds = 0;

                if (parts.length === 3) {
                    [hours, minutes, seconds] = parts;
                } else if (parts.length === 2) {
                    [minutes, seconds] = parts;
                } else if (parts.length === 1) {
                    [seconds] = parts;
                } else {
                    return '-';
                }

                let formatted = '';
                if (hours > 0) formatted += hours + ' hr ';
                if (minutes > 0) formatted += minutes + ' mins ';
                if (seconds >= 0) formatted += seconds + ' secs';

                return formatted.trim();
            }
		}
		EXMS_Report_Settings.init();
	});
})( jQuery );