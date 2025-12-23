( function( $ ) {
    'use strict';
    $( document ).ready(function() { 

        let EXMSuizReview = { 

            student: false,
            /**
             *  Initialize the functionality
             */
            init: function() {

                this.bindEvents();            
            },

            /**
             * Bind events
             */
            bindEvents: function() {

                let self = this;

                $( document ).on( 'click', '.exms-filter-btn', function() {
                    self.showQuizzes();
                });

                $( document ).on( 'click', '.exms-more-details-btn', function() {
                    self.showQuestions( $( this ) );
                });

				$( document ).on( 'click', '.exms-review-btn', function() {
                    self.showModel( $( this ) );
                });

                $( document ).on( 'click', '.exms-accept-btn', function() {
                    self.saveCurrentQuestion();
                });

                $( document ).on( 'change', '#exms-group-select', function () {
                    self.getGroupCourses( $( this ) ); 
                });

                $( document ).on( 'change', '#exms-courses-select', function () {
                    self.getCoursesLesson( $( this ) ); 
                });

                $( document ).on( 'change', '#exms-lesson-select', function () {
                    self.getLessonQuiz( $( this ) ); 
                });

                $( document ).on( 'change', '#exms-quiz-select', function () {
                    self.getQuizStudent( $( this ) ); 
                });

                $( document ).on( 'click', '.exms-submit-btn', function() {
                    self.submitQuiz( $( this ) );
                });

				$( document ).on( 'click', '.exms-pop-close-btn', function() {
                    self.closeModel();
                });
            },


            /**
             * Show the Filtered Quiz
             */
            showQuizzes: function() {
                
                const selectedGroup = $( 'select[name="exms-group"]' ).val();
                const selectedCourses = $( 'select[name="exms-courses"]' ).val();
                const selectedLessons = $( 'select[name="exms-lessons"]' ).val();
                const selectedPostType = $( 'select[name="exms-post-type"]' ).val();
                const selectedQuiz = $( 'select[name="exms-quiz"]' ).val();
                const selectedStudent = $( 'select[name="exms-student"]' ).val();
                const user_id = $( '#exms-quiz-select' ).data( 'user-id' );

                let filteredData = {
                    action: 'exms_filter_quiz',
                    nonce: exms_quiz_review.filter_nonce,
                    groups: selectedGroup,
                    courses: selectedCourses,
                    lessons: selectedLessons,
                    postType: selectedPostType,
                    quiz: selectedQuiz,
                    student: selectedStudent,
                    user_id: user_id
                };
                
                $( '.exms-filter-btn' ).prop( 'disabled', true ).change();

                    $.ajax( {
                        url: exms_quiz_review.ajax_url,
                        type: 'POST',
                        data: filteredData,
                        dataType: 'json',
                        success: ( response ) => {

                            const quizList = response.data.data;
                            const student = response.data.student;

                            if ( student ){
                                this.student = true ;
                            }

                            const container = $( '#exms-quiz-lists' );
                            container.empty().change(); 

                            if ( quizList && quizList.length > 0 ) {
                                quizList.forEach( ( quiz ) => {
                                    const quizTitle = quiz.quiz_title || 'No Title Available';
                                    const quizContent = quiz.quiz_content || 'No Content Available';
                                    const quizID = quiz.id;
                                    const quizTime = EXMSuizReview.formatTime( quiz.time ) || 'No Time Available';
                                    const studentName = quiz.user_name|| 'unknown'; 
                                    const user_id = quiz.user_id|| 'unknown'; 
                                    const btn_txt = exms_quiz_review.more_info_btn ;
                                    const time_title = exms_quiz_review.time_title ;

                                    const quizHtml = `
                                            <div class="exms-quiz-item">
                                                <div class="exms-quiz-detail-wrapper">
                                                    <div class="exms-student-info">
                                                        <div>
                                                            <div class="exms-quiz-title">${studentName}</div>    
                                                                <div>
                                                                    <h2 class="exms-quiz-title-heading">${quizTitle}</h2>
                                                                    <p class="exms-quiz-title" id="exms-quiz-review-detail" style="font-weight: 500;">
                                                                        ${quizContent}
                                                                    </p>
                                                                </div>
                                                                <div class="exms-time-taken"> <span class="dashicons dashicons-clock"></span>${time_title} <span>${quizTime}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <button class="exms-more-details-btn" data-quiz-id=${quizID} data-user-id=${user_id}>${btn_txt}</button>
                                                </div>
                                            </div>
                                            <div class= "exms-card exms-quiz-question-list"></div>
                                            
                                    `;
                                    container.append( quizHtml ).change();
                                });
                            } else {
                                const no_result = exms_quiz_review.no_result;
                                container.html(`
                                        <div class="exms-quiz-item">
                                            <p>${no_result}</p>
                                        </div>
                                `);
                            }

                            $( '.exms-quiz-list' ).slideDown( 500 ).change();

                            $( '.exms-filter-btn' ).prop( 'disabled', false ).change();

                        },
                        error: ( xhr, status, error ) => {
                            console.error( 'Error While Filter Quiz:', xhr, status, error );
                            $( '.exms-filter-btn' ).prop( 'disabled', false ).change();
                        }
                    });
            },

            /**
             * show the quiz questions
             */
            showQuestions: function( button ) {

                const $btn = $( button );
                const quizID = $btn.data( 'quiz-id' );
                const user_id = $btn.data( 'user-id' );
                const quizTitle = exms_quiz_review.quiz_detail; 
                const correctSelected = exms_quiz_review.selected_correct_answer;
                const wrongSelected = exms_quiz_review.selected_wrong_answer;
                const correct = exms_quiz_review.correct_answer;
                const review = exms_quiz_review.review_answer;
                const submit = exms_quiz_review.submit_quiz;

                const $existingList = $( '.exms-quiz-question-list' );

                if ( $existingList.length && $existingList.is( ':visible' ) ) {
                    $existingList.slideUp(300 ).change();
                    $btn.prop( 'disabled', false ).change();
                    return;
                }

                let submitData = {
                    action: 'exms_quiz_questions',
                    nonce: exms_quiz_review.questions_nonce,
                    quiz_id: quizID,
                    user_id: user_id
                };

                $btn.prop( 'disabled', true ).change();

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: ( response ) => {

                        const questions = response.data.data;
                        if ( !Array.isArray( questions ) ) return;  

                        let html = `
                            <div class="exms-card exms-quiz-question-list">
                                <h3 class="exms-card-header">
                                    <div class= "exms-quiz-header-title-wrapper">
                                        <span>
                                            <span class="exms-card-header-icon">
                                                <span class="dashicons dashicons-text-page"></span>
                                            </span>
                                        ${quizTitle} 
                                        </span>
                                        <span class="exms-quiz-question">${questions[0]?.quiz_content || ''}</span>
                                    </div>
                                </h3>`;

                                questions.forEach( ( question, index ) => {
                                    let inputHTML = '';

                                    switch ( question.question_type ) {

                                        case 'essay':
                                        case 'free_choice':

                                            inputHTML = `
                                                <textarea readonly class="exms-textarea exms-simple-text" rows="4">${question.user_answer.answer}</textarea>
                                            `;
                                            break;

                                        case 'multiple_choice':
                                        case 'single_choice':

                                            const answers = question.answers || [];
                                            const selected = question.question_type === 'multiple_choice'
                                                ? question.user_answer.answer || []
                                                : [ question.user_answer.answer || '' ];

                                            inputHTML = answers.map( ( answer ) => {
                                                const isSelected = selected.includes( answer.text );
                                                const isCorrect = answer.correct === true;

                                                let classes = 'exms-option';
                                                let badges = '';

                                                if ( isSelected && isCorrect ) {
                                                    classes += ' exms-selected-correct exms-correct';
                                                    badges += `<span class="exms-status-badge exms-status-badge-correct">${correctSelected}</span>`;
                                                } else if ( isSelected && !isCorrect ) {
                                                    classes += ' exms-selected exms-wrong';
                                                    badges += `<span class="exms-status-badge exms-status-badge-wrong">${wrongSelected}</span>`;
                                                } else if ( !isSelected && isCorrect ) {
                                                    classes += ' exms-correct';
                                                    badges += `<span class="exms-status-badge exms-status-badge-correct">${correct}</span>`;
                                                }

                                                return `
                                                    <div class="${classes}">
                                                        <span>${answer.text}</span>
                                                        ${badges}
                                                    </div>
                                                `;
                                            } ).join('');
                                            break;

                                        default:
                                            inputHTML = `<input type="text" class="exms-input exms-simple-text" readonly />`;
                                    }

                                    html += `
                                        <div class="exms-question-container">
                                            <p class="exms-question-text">
                                                <span class="exms-question-number">${index + 1}</span> 
                                                ${question.question_title}
                                            </p>
                                            <p class="exms-quiz-question-title">${question.question_content}</p>
                                            ${inputHTML}
                                            <button class="exms-review-btn" data-quiz-id=${quizID} data-question-id=${question.question_id} data-user-id=${user_id} >${review}</button>
                                        </div>
                                    `;
                                });
                                
                                if ( !this.student ){

                                    html += `
                                        <div class="exms-actions">
                                            <button class="exms-submit-btn" data-quiz-id=${quizID} data-user-id=${user_id}>${submit}</button>
                                        </div>`;
                                }
                                html += `</div>`;
                        const $quizItem = $( button ).closest( '.exms-quiz-item' );
                        const $html = $( html );
                            $quizItem.after( $html ).change();
                            $html.slideDown( 500 ).change();
                        $btn.prop( 'disabled', false ).change();

                    },
                    error: ( xhr, status, error ) => {
                        console.error( 'Error fetching quiz questions:', xhr, status, error );
                        $btn.prop( 'disabled', false ).change();
                    }
                });
            },

            /**
             * Get the Group Courses
             */
            getGroupCourses: function( self ) {

                const group_id = self.val();
                const selectcourses = exms_quiz_review.courses
                if ( !group_id ) return;

                const submitData = {
                    action: 'exms_get_group_courses',
                    group_id: group_id,
                    nonce : exms_quiz_review.get_group_courses_nonce
                };

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: ( response ) => {
                        
                        const courses = response.data?.courses;
                        if ( !Array.isArray( courses ) ) return;
                        let optionsHTML = `<option disabled selected value="">${selectcourses}</option>`;

                        courses.forEach( ( data ) => {
                            const id = data.id;
                            const title = data.post_title;
                            optionsHTML += `<option value="${id}">${title}</option>`;
                        } );

                        $( 'select[name="exms-courses"]' ).html( optionsHTML ).change();
                    },
                    error: ( xhr, status, error ) => {
                        console.error( 'Error fetching Courses:', xhr, status, error );
                    }
                });
            },

            /**
             * Get the Courses Lessons
             */
            getCoursesLesson: function( self ) {

                const course_id = self.val();
                const selectlessons = exms_quiz_review.lessons
                if ( !course_id ) return;

                const submitData = {
                    action: 'exms_get_courses_lessons',
                    course_id: course_id,
                    nonce : exms_quiz_review.get_courses_lessons_nonce
                };

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: ( response ) => {
                        
                        const lessons = response.data?.lessons;

                        if ( !Array.isArray( lessons ) ) return;

                        let optionsHTML = `<option disabled selected value="">${selectlessons}</option>`;

                        lessons.forEach( ( data ) => {
                            const id = data.id;
                            const title = data.post_title;
                            optionsHTML += `<option value="${id}">${title}</option>`;
                        } );

                        $( 'select[name="exms-lessons"]' ).html( optionsHTML ).change();
                    },
                    error: ( xhr, status, error ) => {
                        console.error( 'Error fetching Lessons:', xhr, status, error );
                    }
                });
            },

            /**
             * Get the Courses Lessons
             */
            getLessonQuiz: function( self ) {

                const lesson_id = self.val();
                const selectquiz = exms_quiz_review.quiz
                if ( !lesson_id ) return;

                const submitData = {
                    action: 'exms_get_lessons_quiz',
                    lesson_id: lesson_id,
                    nonce : exms_quiz_review.get_lessons_quiz_nonce
                };

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: ( response ) => {
                        
                        const quiz = response.data?.quiz;

                        if ( !Array.isArray( quiz ) ) return;

                        let optionsHTML = `<option disabled selected value="">${selectquiz}</option>`;

                        quiz.forEach( ( data ) => {
                            const id = data.id;
                            const title = data.post_title;
                            optionsHTML += `<option value="${id}">${title}</option>`;
                        } );

                        $( 'select[name="exms-quiz"]' ).html( optionsHTML ).change();
                    },
                    error: ( xhr, status, error ) => {
                        console.error( 'Error fetching Quiz:', xhr, status, error );
                    }
                });
            },

            /**
             * Get the Quiz Student
             */
            getQuizStudent: function( self ) {

                const quiz_id = self.val();
                const selectstudent = exms_quiz_review.student
                if ( !quiz_id ) return;

                const submitData = {
                    action: 'exms_get_quiz_student',
                    quiz_id: quiz_id,
                    nonce : exms_quiz_review.get_quiz_student_nonce
                };

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: ( response ) => {

                        const student = response.data?.student;

                        if ( !Array.isArray( student ) ) return;

                        let optionsHTML = `<option disabled selected value="">${selectstudent}</option>`;

                        student.forEach( ( data ) => {
                            const id = data.student_id;
                            const title = data.student_name;
                            optionsHTML += `<option value="${id}">${title}</option>`;
                        } );

                        $( 'select[name="exms-student"]' ).html( optionsHTML ).change();
                    },
                    error: ( xhr, status, error ) => {
                        console.error( 'Error fetching Student:', xhr, status, error );
                    }
                });
            },

            /**
             * Show the modal and store current question info
             */
            showModel: function ( button ) {

                const $btn = $( button );
                const quiz_id = $btn.data( 'quiz-id' );
                const question_id = $btn.data( 'question-id' );
                const user_id = $btn.data('user-id');

                if ( this.student ) {
                    $( '#exms-points' ).prop( 'disabled', true ).change();
                    $( '#exms-remarks' ).prop( 'disabled', true ).change();
                    $( '.exms-accept-btn' ).hide().change(); 
                    $( '.exms-reject-btn' ).hide().change(); 
                }

                $( '#popup' ).data( {
                    quiz_id: quiz_id,
                    question_id: question_id,
                    user_id: user_id
                } );

                $btn.prop( 'disabled', true ).change();

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'exms_quiz_question_detail',
                        nonce: exms_quiz_review.question_detail_nonce,
                        quiz_id: quiz_id,
                        question_id: question_id,
                        user_id: user_id
                    },
                    success: function( response ) {

                        const data = response.data;
                        const points = data.points !== undefined ? parseFloat( data.points ).toString() : '';
                        const remarks = data.remark || '';

                        $( '#exms-points' ).val( points );
                        $( '#exms-remarks' ).val( remarks );
                        $( '.exms-overlay' )
                            .css( { display: 'flex', opacity: 0 } )
                            .addClass( 'exms_show' )
                            .animate( { opacity: 1 }, 300 ).change();
                        $btn.prop( 'disabled', false ).change();

                    },
                    error: function( xhr, status, error ) {
                        console.error( 'Error fetching question detail:', error );
                        $( '.exms-overlay' )
                            .css( { display: 'flex', opacity: 0 } )
                            .addClass( 'exms_show' )
                            .animate( { opacity: 1 }, 300 ).change();
                        $btn.prop( 'disabled', false ).change();
                    }
                });
            },

            /**
             * Save the current question review
             */
            saveCurrentQuestion: function () {

                const $popup = $( '#popup' );
                const quiz_id = $popup.data( 'quiz_id' );
                const question_id = $popup.data( 'question_id' );
                const user_id = $popup.data( 'user_id' );
                const remarks = $( '#exms-remarks' ).val();
                const points = $( '#exms-points' ).val();

                if ( !quiz_id || !question_id || !user_id ) {
                return;
                }

                $( '.exms-accept-btn' ).prop( 'disabled', true ).change();

                $.ajax( {
                url: exms_quiz_review.ajax_url,
                type: 'POST',
                data: {
                    action: 'exms_save_review',
                    nonce: exms_quiz_review.save_review_nonce,
                    quiz_id: quiz_id,
                    question_id: question_id,
                    user_id: user_id,
                    remarks: remarks,
                    points: points
                },
                success: function ( response ) {

                    EXMSuizReview.closeModel();
                    $( '.exms-accept-btn' ).prop( 'disabled', false ).change();    

                },
                error: function ( xhr, status, error ) {
                    console.error( 'Error saving review:', error );
                    $( '.exms-accept-btn' ).prop( 'disabled', false ).change();
                }
                });
            },

            /**
             * Submit the quiz 
             */
            submitQuiz: function ( button ) {

                const $btn = $( button );
                $btn.prop( 'disabled', true ).change();
                const quiz_id = $btn.data( 'quiz-id' );
                const user_id = $btn.data( 'user-id' );

                if ( !quiz_id || !user_id ) {
                    return;
                }

                $.ajax( {
                    url: exms_quiz_review.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'exms_quiz_review_submit',
                        nonce: exms_quiz_review.quiz_review_submit_nonce,
                        quiz_id: quiz_id,
                        user_id: user_id
                    },
                    success: function ( response ) {

                        $btn.prop( 'disabled', false ).change();
                        alert( " Quiz submitted successfully" );
                        
                    },
                    error: function ( xhr, status, error ) {
                        $btn.prop( 'disabled', false ).change();
                        console.error( 'Error submitting quiz:', error , xhr, status );
                    }
                } )
            },

            /**
             * Format the time
             */
            formatTime: function ( timeStr ) {

                if ( !timeStr ) return 'N/A';
                const parts = timeStr.split( ':' ).map( Number );

                let hours = 0, minutes = 0, seconds = 0;

                if ( parts.length === 3 ) {
                    [ hours, minutes, seconds ] = parts;
                } else if ( parts.length === 2 ) {
                    [minutes, seconds] = parts;
                } else if ( parts.length === 1 ) {
                    [seconds] = parts;
                }

                const h = hours > 0 ? `${hours} hour${hours > 1 ? 's' : ''}` : '';
                const m = minutes > 0 ? `${minutes} min` : '';
                const s = seconds > 0 ? `${seconds} sec` : '';

                return [ h, m, s ].filter( Boolean ).join( ' ' );
            },

            /**
             * Close the modal
             */
			closeModel: function() {
	
                $( '.exms-overlay' ).animate( { opacity: 0 }, 300, function () {
                    $( this ).removeClass( 'exms_show' ).css( 'display', 'none' ).change();
                });
			},
        };
        EXMSuizReview.init();
    });
} )( jQuery );