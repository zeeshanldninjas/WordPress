( function( $ ) {
    'use strict';
    $( document ).ready(function() {
        let totalSeconds = 0;
        let currentActiveQuestionNumber = 0;
        let EXMSQUIZ = {

            currentQuestion: 0,
            containers: null,
            quizContainer: null,
            timerInterval: null,

            /**
             * Initialize the functionality
             */
            init: function() {
                this.saveData();
                this.startQuiz();
                this.questionHint();
                this.HandleBuyQuiz();
                this.submitQuestion();
                this.showQuestionSubmitPopup();
                this.showPreviousQuestion();
                this.closeConfirmationBox();
                this.viewAnswer();
                this.addLoaderClass();
                this.quizReattempt();
                this.EnrollAsAdmin();
            },

            /**
             * enroll on quiz / course as an admin
             */
            EnrollAsAdmin: function() {

                /**
                 * close enrollment popup
                 */
                $( document ).on( 'click', '.exms-close-quiz-auto-popup', function() {
                    location.reload();
                } );

                $( document ).on( 'click', '#exms-quiz-enroll-as-admin', function() {

                    let self = $(this);
                    let text = self.text();
                    self.text( text + '.....' );
                    let submitData = {
                        action: 'exms_enroll_as_an_admin',
                        nonce: exms_quiz.nonce,
                        quiz_id: exms_quiz.quiz_id,
                        user_id: exms_quiz.user_id
                    };

                    $.ajax({
                        url: exms_quiz.ajax_url,
                        type: 'POST',
                        data: submitData,
                        dataType: 'json',
                        success: ( response ) => {
                            location.reload();
                        },
                    });
                } );
            },

            /**
             * Quiz Re-attempt
             */
            quizReattempt: function() {

                $( document ).on( 'click', '.exms-quiz-reattempt-btn', function() {
                    
                    let quizEndTime = exms_quiz.quiz_end_time;
                    let currentTime = Math.floor(Date.now() / 1000); 
                    let is_delete_able = exms_quiz.is_delete_able;

                    if( is_delete_able || ( quizEndTime && quizEndTime < currentTime ) ) {
                        localStorage.removeItem( `question_number_${exms_quiz.quiz_id}_${exms_quiz.user_id}` );
                        localStorage.removeItem( `quiz_saved_user_answers_${exms_quiz.quiz_id}_${exms_quiz.user_id}` );
                    }

                    $( '.exms-page-loader' ).css( 'display', 'flex' );
                    let startQuizBtn = '<a href="javascript:void(0);" id="quiz-start-button" class="exms-quiz-start-btn" style="display: none;">Start Quiz</a>';
                    $(this).after( startQuizBtn );
                    $( '#quiz-start-button' ).click();
                    $( '.exms-quiz-box' ).css( 'display', 'flex' );

                    $('.exms-quiz-result').fadeOut(1000, function() {
                        $(this).remove();
                        $( '.exms-page-loader' ).css( 'display', 'none' );
                        jQuery('html, body').animate({
                            scrollTop: jQuery( '.exms-quiz-box' ).offset().top - 100
                        }, 600);
                    });
                } );
            },

            /**
             * Add loader class 
             */
            addLoaderClass: function() {

                $( document ).on( 'click', '.exms-quiz-submit', function() {
                    $( '.exms-submit-question' ).addClass( 'exms-show-loader' );
                } );

                $( document ).on( 'click', '.exms-prev-btn', function() {
                    $( '.exms-submit-question' ).removeClass( 'exms-show-loader' );
                } );
            },

            /**
             * view user submitted answer 
             */
            viewAnswer: function() {

                $( document ).on( 'click', '.exms-view-answer', function(e) {

                    e.preventDefault();
                    $( '.exms-skeleton' ).show();

                    setTimeout( function() {
                        $( '.exms-skeleton' ).hide();
                        EXMSQUIZ.viewSubmittedAnswer();
                    }, 1000 );
                } );
            },

            /**
             * close confirmation box
             */
            closeConfirmationBox: function() {

                $( document ).on( 'click', '.exms-cancel-btn', function() {
                    EXMSQUIZ.showModel();
                } );
            },

            saveData: function() {
                let allData = exms_quiz.all_data;
                localStorage.setItem( 'quiz_all_data', JSON.stringify( allData ) );
            },

            /**
             * display submit popup
             */
            showQuestionSubmitPopup: function() {

                $( document ).on( 'click', '.exms-next-btn,.exms-quiz-submit', function() {

                    let self = $(this);
                    $( '.exms-model-qiz-submit-btn' ).addClass( 'exms-submit-question' );
                    $( '.exms-submit-question' ).removeClass( 'exms-model-qiz-submit-btn' );
                    
                    let questionID = $( '.exms-question-wrapper' ).attr( 'data-question-id' );
                    let savedAnswersData = localStorage.getItem( `quiz_saved_user_answers_${exms_quiz.quiz_id}_${exms_quiz.user_id}` );
                    savedAnswersData = savedAnswersData ? JSON.parse(savedAnswersData) : {};

                    if( 'result_after_each_question' == exms_quiz.quiz_summary_result || self.hasClass( 'exms-quiz-submit' ) ) {
                        EXMSQUIZ.showModel();                         
                    } else {
                        $( '.exms-submit-question' ).click();
                    }
                } );
            },

            /**
             * submit question 
             */
            submitQuestion: function() {
                
                $( document ).on( 'click', '.exms-submit-question', function(e) {

                    e.preventDefault();
                    let self = $(this);

                    let questionType = true;

                    if( $('.exms-show-loader').length ) {
                        $( '.exms-page-loader' ).css( 'display', 'flex' );
                    }

                    if( 'summary_at_end' == exms_quiz.quiz_summary_result && 'exms_all_at_once' == exms_quiz.question_display ) {
                        
                        let questionsData = [];
                        let uploadData = [];

                        $.each( $('.exms-question-wrapper'), function(index, elem) {
                            
                            let questionID = $(elem).attr('data-question-id');
                            let questionType = $(elem).attr('data-question-type');
                            let userAnswer = EXMSQUIZ.getQuestionAnswer( questionType, questionID );

                            if( 'file_upload' == questionType ) {

                                if( userAnswer ) {
                                    uploadData[questionID] = {
                                        question_type: questionType,
                                        user_answer: userAnswer,
                                        question_id: questionID
                                    };  
                                }
                            }

                            if( questionType ) {
                                questionsData[questionID] = {
                                    question_type: questionType,
                                    user_answer: userAnswer,
                                    question_id: questionID
                                };
                            }
                        } );

                        EXMSQUIZ.showModel();
                        EXMSQUIZ.handleQuizSubmission( questionsData, uploadData );
                        setTimeout( function() {
                            location.reload();
                        }, 1000 );
                        questionType = false;
                    }

                    if( true == questionType ) {

                        let self = $(this);
                        let questionID = $( '.exms-question-wrapper' ).attr( 'data-question-id' );
                        let questionType = $( '.exms-question-wrapper' ).attr( 'data-question-type' );
                        let questionScore = $( '.exms-question-wrapper' ).attr( 'data-question-score' );
                        let quizId = exms_quiz.quiz_id;
                        let questionTime = $( '#wpetimer' ).text();
                        let userID = exms_quiz.user_id;
                        let savedAnswersData = localStorage.getItem( `quiz_saved_user_answers_${quizId}_${userID}` );
                        savedAnswersData = savedAnswersData ? JSON.parse( savedAnswersData ) : {};
                        var nextQuestionNumber;
                        var questionNumber;
                        let savedQuestionNumber = localStorage.getItem(`question_number_${quizId}_${userID}`);

                        if( savedQuestionNumber === null  ) {
                            questionNumber = 0;
                        } else {
                            questionNumber = parseInt( savedQuestionNumber );
                        }

                        var questionCount = $( '.exms-question-section' ).attr( 'exms-question-count' );

                        if( ! savedAnswersData[questionID] ) {
        
                            if( 'result_after_each_question' == exms_quiz.quiz_summary_result ) {                   
                                EXMSQUIZ.saveCurrentAnswer( questionID, questionType, questionScore, quizId, questionTime );
                            }                        
                            localStorage.setItem( `question_number_${quizId}_${userID}`, questionNumber + 1 );
                            nextQuestionNumber = parseInt( questionNumber ) + 1;
                        } else {
                            nextQuestionNumber = parseInt ( savedQuestionNumber );
                        }

                        let userAnswer = EXMSQUIZ.getQuestionAnswer( questionType, questionID );

                        savedAnswersData[questionID] = {
                            quiz_id: quizId,
                            answer: userAnswer
                        };

                        localStorage.setItem( `quiz_saved_user_answers_${quizId}_${userID}`, JSON.stringify( savedAnswersData ) );
                        
                        if( 'result_after_each_question' == exms_quiz.quiz_summary_result ) {
                            EXMSQUIZ.showModel();

                            if( self.hasClass( 'exms-show-loader' ) ) {                                

                                setTimeout( function() {                                    
                                    EXMSQUIZ.handleQuizSubmission();
                                    localStorage.removeItem( `question_number_${quizId}_${userID}` );
                                    localStorage.removeItem( `quiz_saved_user_answers_${quizId}_${userID}` );
                                }, 1000 );
                                
                                setTimeout( function() {
                                    location.reload();
                                }, 2000 );   
                            }

                        } else {

                            let submissionData = new FormData();

                            if( Array.isArray(userAnswer) ) {
                                submissionData.append( 'user_answer', JSON.stringify(userAnswer) );
                            } else {
                                submissionData.append( 'user_answer', userAnswer );
                            }
                            submissionData.append( 'action', 'exms_save_question_answer_to_transient' );
                            submissionData.append( 'nonce', exms_quiz.nonce );
                            submissionData.append( 'question_id', questionID );
                            submissionData.append( 'question_number', questionNumber );
                            submissionData.append( 'user_id', exms_quiz.user_id );
                            submissionData.append( 'quiz_id', quizId );
                            submissionData.append( 'question_type', questionType );
                            
                            $.ajax( {
                                url: exms_quiz.ajax_url,
                                type: 'POST',
                                data: submissionData,
                                dataType: 'json',
                                processData: false,
                                contentType: false,
                                success: (response) => {}
                            } );

                            if( self.hasClass( 'exms-show-loader' ) ) {

                                EXMSQUIZ.showModel();
                                setTimeout( function() {                                    
                                    EXMSQUIZ.handleQuizSubmission();
                                    localStorage.removeItem( `question_number_${quizId}_${userID}` );
                                    localStorage.removeItem( `quiz_saved_user_answers_${quizId}_${userID}` );
                                }, 1000 );
                                
                                setTimeout( function() {
                                    location.reload();
                                }, 2000 );   
                            }
                        }

                        if($('.exms-quiz-submit').length) {

                            setTimeout(function() {
                                // EXMSQUIZ.handleQuizSubmission();
                                // localStorage.removeItem( `question_number_${quizId}_${userID}` );
                                // localStorage.removeItem( `quiz_saved_user_answers_${quizId}_${userID}` );
                                // location.reload();
                            }, 2000 ); 
                        } else {

                            if( nextQuestionNumber + 1 == questionCount ) {
                                EXMSQUIZ.changeButtonType( questionCount );
                            }

                            self.attr( 'data-question_number', nextQuestionNumber );
                            let savedData = localStorage.getItem('quiz_all_data');

                            if ( savedData ) {

                                savedData = JSON.parse( savedData );
                                let Question = savedData[nextQuestionNumber];
                                let nextTimer = savedData[nextQuestionNumber + 1] 
                                ? savedData[nextQuestionNumber + 1].timer 
                                : '';

                                if (Question) {
                                    EXMSQUIZ.getQuizQuestion(Question, nextTimer);
                                    currentActiveQuestionNumber = nextTimer;
                                    let timerElement = $('#wpetimer');
                                    let timerType = timerElement.data( 'timer-type' );
                                    if(timerType !== 'quiz_timer') {
                                        let totalTime = EXMSQUIZ.timeToSeconds(Question.timer);
                                        if (totalTime > 0) {
                                            clearInterval(EXMSQUIZ.timerInterval);
                                            timerElement.text(EXMSQUIZ.formatTime(totalTime));

                                            EXMSQUIZ.timerInterval = setInterval(function () {
                                                totalTime--;
                                                if( totalTime <= 0 ) {
                                                    clearInterval(EXMSQUIZ.timerInterval);
                                                    timerElement.text('00:00');

                                                    let nextBtn = $('.exms-next-btn:visible').first();
                                                    let submitQuestion = $('.exms-modal .exms-submit-question').first();
                                                    if(submitQuestion.length) {
                                                        submitQuestion.trigger('click');
                                                        $('.exms-submit-btn.exms-model-qiz-submit-btn').trigger('click');
                                                        EXMSQUIZ.closeModel();
                                                    } else {
                                                        EXMSQUIZ.showModel();
                                                        $('.exms-submit-btn.exms-model-qiz-submit-btn').trigger('click');
                                                    }
                                                } else {
                                                    timerElement.text(EXMSQUIZ.formatTime(totalTime));
                                                }
                                            }, 1000);
                                        }
                                    } else {
                                        let timeStr = timerElement.data( 'initial-time' ).toString().trim();
                                        let totalTime = EXMSQUIZ.timeToSeconds( timeStr );
                                        if( totalTime <= 0 ) {
                                            $( ".exms-next-btn" ).addClass( 'exms-quiz-submit' ); 
                                            let quizSubmitBtn = $('.exms-quiz-submit').first();
                                            if (quizSubmitBtn.length) {
                                                let submitModal = $('.exms-model-qiz-submit-btn').first();
                                                let submitQuestion = submitModal.addClass('exms-submit-question').removeClass( 'exms-model-qiz-submit-btn' );
                                                quizSubmitBtn.trigger('click');
                                                submitQuestion.trigger('click');
                                            }
                                        }
                                    }
                                }

                                $( '.exms-question-number-count-container .exms-question-number-count' ).html( nextQuestionNumber + 1 );
                            }
                        }
                    }  
                } );
            },

            /**
             * create a function to change next button into Submit button
             */
            changeButtonType: function( questionCount ) {

                $( '.exms-next-btn' ).addClass( 'exms-quiz-submit' );
                $( '.exms-quiz-submit' ).removeClass( 'exms-next-btn' );
                $( '.exms-quiz-submit' ).text( 'Submit Quiz' );
                if( 1 == questionCount ) {
                    $( '.exms-prev-btn' ).remove();
                }
            },
            
            /*
             * show previous question (read-only)
             */
            showPreviousQuestion: function() {

                $(document).on('click', '.exms-prev-btn', function(e) {
                    e.preventDefault();
                    // setTimeout( function() {
                    //     $( '.exms-answer-checkbox-9' ).prop('checked', true);
                    // }, 2000 );
                    
                    if( $('.exms-quiz-submit').length ) {
                        $('.exms-quiz-submit').html('Next <span class="dashicons dashicons-arrow-right-alt2"></span>');
                        $( '.exms-quiz-submit' ).addClass( 'exms-next-btn' );
                        $( '.exms-next-btn' ).removeClass( 'exms-quiz-submit' );
                    }

                    let questionNumber = parseInt($('.exms-submit-question').attr('data-question_number'));
                    
                    if (isNaN(questionNumber) || questionNumber <= 0) {
                        questionNumber = parseInt( localStorage.getItem(`question_number_${exms_quiz.quiz_id}_${exms_quiz.user_id}`) );
                    }

                    if (isNaN(questionNumber) || questionNumber <= 0) {
                        return;
                    }

                    let prevQuestionNumber = questionNumber - 1;
                    let savedData = localStorage.getItem('quiz_all_data');
                    // let userSavedAnswer = localStorage.getItem( 'quiz_saved_user_answers_11_1' );
                    let savedAnswersData = localStorage.getItem( `quiz_saved_user_answers_${exms_quiz.quiz_id}_${exms_quiz.user_id}` );
                    savedAnswersData = savedAnswersData ? JSON.parse(savedAnswersData) : {};
                    let savedAnswerObj = savedAnswersData[9] || null;
                    let savedAnswer = savedAnswerObj ? savedAnswerObj.answer : null;
                    // console.log( savedAnswer );
                    if (savedData) {

                        savedData = JSON.parse(savedData);
                        let question = savedData[prevQuestionNumber];
                        let nextTimer = savedData[prevQuestionNumber + 1] 
                            ? savedData[prevQuestionNumber + 1].timer 
                            : '';

                        if (question) {
                            
                            EXMSQUIZ.getQuizQuestion(question, currentActiveQuestionNumber);
                            
                            setTimeout(function() {

                                $.each($('.exms-question-wrapper .exms-options input[type="checkbox"]'), function(index, elem) {
                                    
                                    let val = $(elem).val();
                                    
                                    if ( savedAnswer.includes( val ) ) {
                                        $(elem).prop('checked', true);
                                    }
                                });

                                if( 'exms_one_at_time' != exms_quiz.question_display && 'summary_at_end' != exms_quiz.quiz_summary_result ) {
                                    $('.exms-submit-question').prop('disabled', true).addClass('disabled-btn');                      
                                }
                                // $('.exms-question-wrapper input, .exms-question-wrapper textarea, .exms-question-wrapper select').prop('disabled', true);
                                // $('.exms-submit-question').prop('disabled', true).addClass('disabled-btn');
                            }, 100);
                        }

                        $('.exms-question-number-count-container .exms-question-number-count').html(prevQuestionNumber + 1);
                        $('.exms-submit-question').attr('data-question_number', prevQuestionNumber);
                    }
                });
            },
            
            /**
             * Start the quiz when the start button is clicked
             */

            startQuiz: function() {

                $( document ).on( 'click', '#quiz-start-button', function() {

                    setTimeout(function () {
                        
                        let attemtedQuestion = parseInt( localStorage.getItem(`question_number_${exms_quiz.quiz_id}_${exms_quiz.user_id}`) );
                        
                        if( ! attemtedQuestion ) {
                            attemtedQuestion = 1;
                        } else {
                            attemtedQuestion = attemtedQuestion + 1;
                        }

                        $( '.exms-question-number-count' ).text( attemtedQuestion );
                    }, 500);
                    
                    // localStorage.removeItem( `quiz_saved_user_answers_${quizId}_${userID}` );
                    $( '.exms-quiz-detail' ).fadeOut( 300, () => {
                        $( '.exms-quiz-box' ).fadeIn( 300 ).css( 'display', 'flex' );
                    }).change();

                    let savedData = localStorage.getItem('quiz_all_data');
                    let questionArray = JSON.parse(savedData);
                    let questionCount = questionArray.filter(item => item.question_id !== undefined).length;
                    let count = questionArray.length;

                    if ( savedData ) {

                        if( exms_quiz.question_display === "exms_one_at_time" ) {
                                                        
                            let firstQuestion = 0;
                            let attemtedQuestion = parseInt( localStorage.getItem(`question_number_${exms_quiz.quiz_id}_${exms_quiz.user_id}`) );
                            
                            if( attemtedQuestion ) {
                                firstQuestion = questionArray[attemtedQuestion];
                            } else {
                                firstQuestion = questionArray[0];
                            }

                            let secondQuestionTimer = savedData[1]["timer"];
                            EXMSQUIZ.getQuizQuestion(firstQuestion, secondQuestionTimer);
                        } else {

                            let allQuestionsHTML = "";
                            $.each(questionArray, function(index, question) {
                                allQuestionsHTML += EXMSQUIZ.getAllQuizQuestion(index, question);
                            });

                            $( '.exms-question-section' ).html( allQuestionsHTML );
                            $('.exms-question-wrapper:last .exms-hint-sidebar').after(`
                                <div class="exms-quiz-all-buttons">
                                    <a href="javascript:void(0);" class="exms-quiz-nav-btn exms-quiz-submit" data-question_time="">
                                        ${exms_quiz.submit}
                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </a>
                                </div>
                            `);
                            $('.exms-quiz-buttons').remove();
                            $('.exms-sortable-list').sortable({
                                placeholder: 'exms-sortable-placeholder',
                                start: function(e, ui) {
                                    ui.placeholder.height(ui.item.height());
                                }
                            });
                        }
                    }
                    
                    EXMSQUIZ.quizTimer();

                    if( questionCount == 1 ) {
                        EXMSQUIZ.changeButtonType( questionCount );
                    } else {
                        $( '.exms-question-section' ).attr( 'exms-question-count', questionCount );
                    }

                    let submissionData = new FormData();
                    submissionData.append( 'action', 'exms_save_quiz_data_to_transient' );
                    submissionData.append( 'quiz_time', exms_quiz.quiz_time );
                    submissionData.append( 'is_quiz_timer_enabled', exms_quiz.quiz_timer );
                    submissionData.append( 'user_id', exms_quiz.user_id );
                    submissionData.append( 'quiz_id', exms_quiz.quiz_id );
                    submissionData.append( 'current_url', exms_quiz.current_url );
                    submissionData.append( 'post_type', exms_quiz.post_type );
                    
                    $.ajax( {
                        url: exms_quiz.ajax_url,
                        type: 'POST',
                        data: submissionData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: (response) => {}
                    } );
                } );
            },

            shuffleArray: function (array) {
                for (let i = array.length - 1; i > 0; i--) {
                    let j = Math.floor(Math.random() * (i + 1));
                    [array[i], array[j]] = [array[j], array[i]];
                }
                return array;
            },

            /**
             * Display all question at once
             */
            getAllQuizQuestion: function(index, getQuestion) {

                let savedAnswersData = localStorage.getItem( `quiz_saved_user_answers_${exms_quiz.quiz_id}_${exms_quiz.user_id}` );
                savedAnswersData = savedAnswersData ? JSON.parse(savedAnswersData) : {};
                let savedAnswerObj = savedAnswersData[getQuestion.question_id] || null;
                let savedAnswer = savedAnswerObj ? savedAnswerObj.answer : null;
                let hintText = getQuestion.hint && getQuestion.hint.trim() !== '' ? getQuestion.hint : '';

                let questionBody = "";
                if (getQuestion.question_type === 'fill_blank') {
                    let questionText = getQuestion.answers || '';
                    let questionWithInputs = questionText.replace(/\{(.*?)\}/g, function(match, answer) {
                        let charCount = answer.length;
                        let minWidth = 80;
                        let charWidth = 8;
                        let width = Math.max(minWidth, charCount * charWidth);
                        return `<input type="text" name="quiz-fill_blank_${getQuestion.question_id}[]" class="exms-answer-fill_blank_text_area" style="width:${width}px" />`;
                    });

                    questionBody += `<div class="exms-fill-blank-question">${questionWithInputs}</div>`;

                } else if (getQuestion.question_type === 'multiple_choice' || getQuestion.question_type === 'single_choice') {
                    let inputType = (getQuestion.question_type === 'multiple_choice') ? 'checkbox' : 'radio';
                    let inputs_html = '';

                    if (getQuestion.answers) {
                        let answersArray = getQuestion.answers.split(',').map(a => a.trim());
                        answersArray.forEach(function(answer, idx) {
                            let optionId = `option_${getQuestion.question_id}_${idx}`;
                            let inputName = (inputType === 'checkbox') 
                                ? `answer_${getQuestion.question_id}[]` 
                                : `answer_${getQuestion.question_id}`;

                            inputs_html += `
                                <div class="exms-options">
                                    <div class="exms-option">
                                        <input type="${inputType}" id="${optionId}" name="${inputName}" value="${answer}" class="exms-answer-${inputType}-${getQuestion.question_id}" />
                                        <label for="${optionId}">${answer}</label>
                                    </div>
                                </div>`;
                        });
                    }
                    questionBody += inputs_html;

                } else if (getQuestion.question_type === 'free_choice') {
                    questionBody += `
                    <div class="exms-answer-textarea">
                        <textarea id="quiz-answer-textarea-${getQuestion.question_id}" name="quiz-answer-textarea" style="resize:none;" rows="4" cols="50">${savedAnswer || ''}</textarea>
                    </div>`;

                } else if (getQuestion.question_type === 'true_false') {
                    questionBody += `
                    <div class="exms-answer-true-false">
                        <input type="radio" id="true_${getQuestion.question_id}" name="quiz-true-false-${getQuestion.question_id}" value="true">
                        <label for="true_${getQuestion.question_id}">True</label>
                        <input type="radio" id="false_${getQuestion.question_id}" name="quiz-true-false-${getQuestion.question_id}" value="false">
                        <label for="false_${getQuestion.question_id}">False</label>
                    </div>`;
                
                } else if (getQuestion.question_type === 'file_upload' || getQuestion.question_type === 'essay') {
                    questionBody += `
                    <div class="exms-answer-file-upload">
                        <input type="file" id="quiz-file-upload-${getQuestion.question_id}" name="quiz-file-upload-${getQuestion.question_id}" />
                    </div>`;
                
                } else if (getQuestion.question_type === 'range') {
                    questionBody += `
                    <div class="exms-answer-range">
                        <input type="range" min="${exms_quiz.min_range}" max="${exms_quiz.max_range}" value="${savedAnswer || exms_quiz.min_range}" 
                            oninput="this.nextElementSibling.textContent=this.value">
                        <span class="exms-range-value">${savedAnswer || exms_quiz.min_range}</span>
                    </div>`;
                
                } else if (getQuestion.question_type === 'sorting_choice') {
                    let answersArray = [];
                    if (savedAnswer && Array.isArray(savedAnswer)) {
                        answersArray = savedAnswer;
                    } else if (getQuestion.answers) {
                        answersArray = EXMSQUIZ.shuffleArray(getQuestion.answers.split(',').map(a => a.trim()));
                    }

                    questionBody += `<div class="exms-answer-sorting-choice"><ul class="exms-sortable-list">`;
                    answersArray.forEach(function(answer, i) {
                        questionBody += `
                            <li class="exms-sortable-item exms-sortable-item-${getQuestion.question_id}" id="item-${i}">
                                <span class="exms-drag-handle">☰</span>
                                <span class="exms-sorting-answer">${answer}</span>
                                <input type="hidden" name="exms-quiz-sorting-choice[]" value="${answer}">
                            </li>`;
                    });
                    questionBody += `</ul></div>`;
                }



                let wrapperHTML = `
                    <div class="exms-question-wrapper"
                        data-question="${getQuestion.question_title || ''}"
                        data-question-type="${getQuestion.question_type || ''}"
                        data-question-id="${getQuestion.question_id || ''}"
                        data-question-score="${getQuestion.points_for_question || 0}">
                        
                        <div class="exms-question-title-wrapper">
                            <div class="exms-quiz-mobile-timer-area">
                                <span class="exms-quiz-timer-icon dashicons dashicons-clock"></span>
                                <span>${getQuestion.time_limit || ''}</span>
                            </div>
                        </div>

                        <div class="exms-question-number-count-wrapper">
                            <div class="exms-question-number-count-container">
                                <span class="exms-question-number-count">${index + 1 || ''}</span>
                            </div>
                            <div class="exms-question-text-cntainer">${getQuestion.question_content || ''}</div>
                            <div>
                                <button class="exms-question-hint-btn fullwidth-hint ${hintText ? '' : 'disabled'}" 
                                    data-question-hint="${hintText}">
                                    Hint
                                </button>
                            </div>
                        </div>
                        <div class="exms-hint-sidebar">
                            <span class="exms-hint-close">&times;</span>
                            <p class="exms-hint-text">💡 ${hintText}</p>
                        </div>
                        ${questionBody}
                    </div>
                `;
                    return wrapperHTML;
            },

            /**
             * Display one question at a time
             */
            getQuizQuestion: function(getQuestion, secondQuestionTimer) {

                let wrapper = $('.exms-question-wrapper');

                if ( wrapper.length ) {

                    wrapper.attr( 'data-question', getQuestion.question_title || '' ).attr( 'data-question-type', getQuestion.question_type || '' ).attr( 'data-question-id', getQuestion.question_id || '' ).attr( 'data-question-score', getQuestion.points_for_question || 0 );
                    let attemtedQuestion = parseInt( localStorage.getItem(`question_number_${exms_quiz.quiz_id}_${exms_quiz.user_id}`) );
                    $( '.exms-question-number-count' ).html( attemtedQuestion + 1 );

                    let savedAnswersData = localStorage.getItem( `quiz_saved_user_answers_${exms_quiz.quiz_id}_${exms_quiz.user_id}` );
                    savedAnswersData = savedAnswersData ? JSON.parse(savedAnswersData) : {};
                    let savedAnswerObj = savedAnswersData[getQuestion.question_id] || null;
                    let savedAnswer = savedAnswerObj ? savedAnswerObj.answer : null;

                    // if ( getQuestion.question_content ) {
                        if( getQuestion.question_type === 'fill_blank' ) {
                            
                            $('.exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-range, .exms-answer-sorting-choice').remove();
                            let questionText = getQuestion.answers || '';

                            let questionWithInputs = questionText.replace(/\{(.*?)\}/g, function( match, answer ) {
                                let charCount = answer.length;
                                let minWidth = 80;
                                let charWidth = 8;
                                let width = Math.max(minWidth, charCount * charWidth);
                                return '<input type="text" name="quiz-fill_blank_'+getQuestion.question_id+'[]" class="exms-answer-fill_blank_text_area" style="width:' + width + 'px" />';
                            } );
                            wrapper.find('.exms-hint-sidebar').after('<div class="exms-fill-blank-question">' + questionWithInputs + '</div>');

                            if (savedAnswer && Array.isArray(savedAnswer)) {
                                $('.exms-answer-fill_blank_text_area').each(function(i) {
                                    $(this).val(savedAnswer[i] || '');
                                });
                            }

                        } else if( getQuestion.question_type == 'multiple_choice' || getQuestion.question_type == 'single_choice' ) {
                            
                            $('.exms-options, .exms-fill-blank-question, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-range, .exms-answer-sorting-choice').remove();

                            let inputType = ( getQuestion.question_type === 'multiple_choice' ) ? 'checkbox' : 'radio';
                            let inputs_html = '';
                            if ( getQuestion.answers ) {
                                let answersArray = getQuestion.answers.split(',').map(a => a.trim());

                                answersArray.forEach(function(answer, idx) {
                                    let optionId = `option_${getQuestion.question_id}_${idx}`;
                                    let inputName = ( inputType === 'checkbox' ) 
                                        ? `answer_${getQuestion.question_id}[]` 
                                        : `answer_${getQuestion.question_id}`;

                                    inputs_html += `
                                        <div class="exms-options">
                                            <div class="exms-option">
                                                <input type="${inputType}"
                                                    id="${optionId}"
                                                    name="${inputName}" 
                                                    value="${answer}" 
                                                    class="exms-answer-${inputType}-${getQuestion.question_id}" />
                                                <label for="${optionId}">
                                                    ${answer}
                                                </label>
                                            </div>
                                        </div>
                                    `;
                                });
                            }
                            wrapper.find('.exms-hint-sidebar').after(inputs_html);

                            if (savedAnswer) {
                                if (Array.isArray(savedAnswer)) {
                                    savedAnswer.forEach(val => {
                                        $(`.exms-answer-${inputType}[value="${val}"]`).prop('checked', true);
                                    });
                                } else {
                                    $(`.exms-answer-${inputType}[value="${savedAnswer}"]`).prop('checked', true);
                                }
                            }

                        } else if( getQuestion.question_type == 'free_choice' ) {
                            
                            $('.exms-answer-textarea, .exms-fill-blank-question, .exms-options, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-range, .exms-answer-sorting-choice').remove();
                            let inputs_html = `
                                <div class="exms-answer-textarea">
                                    <textarea id="quiz-answer-textarea-${getQuestion.question_id}" name="quiz-answer-textarea" style="resize: none;" rows="4" cols="50"></textarea>
                                </div>
                            `;
                            wrapper.find('.exms-hint-sidebar').after(inputs_html);

                            if (savedAnswer) {
                                $('#quiz-answer-textarea').val(savedAnswer);
                            }

                        } else if( getQuestion.question_type == 'true_false' ) {
                            
                            $('.exms-answer-true-false, .exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-file-upload, .exms-answer-range, .exms-answer-sorting-choice').remove();
                            let inputs_html = `
                                <div class="exms-answer-true-false">
                                    <input type="radio" id="true" name="quiz-true-false" value="true">
                                    <label for="true">True</label>

                                    <input type="radio" id="false" name="quiz-true-false" value="false">
                                    <label for="false">False</label>
                                </div>
                            `;
                            wrapper.find('.exms-hint-sidebar').after(inputs_html);

                            if (savedAnswer) {
                                $(`input[name="quiz-true-false"][value="${savedAnswer}"]`).prop('checked', true);
                            }

                        } else if( getQuestion.question_type == 'file_upload' || getQuestion.question_type == 'essay' ) {
                            
                            $('.exms-answer-file-upload, .exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-range, .exms-answer-sorting-choice').remove();
                            let inputs_html = `
                                <div class="exms-answer-file-upload">
                                    <input type="file" id="quiz-file-upload-${getQuestion.question_id}" name="quiz-file-upload" />
                                </div>
                            `;
                            wrapper.find('.exms-hint-sidebar').after(inputs_html);

                        } else if( getQuestion.question_type == 'range' ) {
                            
                            $('.exms-answer-range, .exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-sorting-choice').remove();
                            let inputs_html = `
                                <div class="exms-answer-range">
                                    <input 
                                        type="range" 
                                        id="quiz-answer-range-${getQuestion.question_id}" 
                                        name="quiz-answer-range-${getQuestion.question_id}" 
                                        min="${exms_quiz.min_range}" 
                                        max="${exms_quiz.max_range}" 
                                        value="${savedAnswer || exms_quiz.min_range}" 
                                        oninput="document.getElementById('range-value-${getQuestion.question_id}').textContent = this.value"
                                    >
                                    <span class="exms-range-value" id="range-value-${getQuestion.question_id}">
                                        ${savedAnswer || exms_quiz.min_range}
                                    </span>
                                </div>
                            `;
                            wrapper.find('.exms-hint-sidebar').after(inputs_html);

                        } else if( getQuestion.question_type === 'sorting_choice' ) {
                            
                            $('.exms-answer-sorting-choice, .exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-range').remove();
                            let inputs_html = `<div class="exms-answer-sorting-choice"><ul class="exms-sortable-list">`;

                            let answersArray = [];
                            if (savedAnswer && Array.isArray(savedAnswer)) {
                                answersArray = savedAnswer;
                            } else if (getQuestion.answers) {
                                answersArray = EXMSQUIZ.shuffleArray(getQuestion.answers.split(',').map(a => a.trim()));
                            }

                            answersArray.forEach(function(answer, i) {
                                inputs_html += `
                                    <li class="exms-sortable-item exms-sortable-item-${getQuestion.question_id}" id="item-${i}">
                                        <span class="exms-drag-handle">☰</span>
                                        <span class="exms-sorting-answer">${answer}</span>
                                        <input type="hidden" name="exms-quiz-sorting-choice[]" value="${answer}">
                                    </li>
                                `;
                            });

                            inputs_html += `</ul></div>`;
                            wrapper.find('.exms-hint-sidebar').after(inputs_html);

                            $('.exms-sortable-list').sortable({
                                placeholder: 'exms-sortable-placeholder',
                                start: function(e, ui) {
                                    ui.placeholder.height(ui.item.height());
                                }
                            });

                        } else {
                            $('.exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-range, .exms-answer-sorting-choice').remove();
                        }
                        wrapper.find('.exms-question-text-cntainer').html(getQuestion.question_content);
                    // } 
                    // else {
                    //     $('.exms-answer-input, .exms-fill-blank-question, .exms-options, .exms-answer-textarea, .exms-answer-true-false, .exms-answer-file-upload, .exms-answer-range, .exms-answer-sorting-choice').remove();
                    //     let inputs_html = `
                    //         <div class="exms-answer-input">
                    //             <input type="text" id="quiz-answer" name="quiz-answer">
                    //         </div>
                    //     `;
                    //     wrapper.find('.exms-question-number-count-wrapper').after(inputs_html);

                    //     if (savedAnswer) {
                    //         $('#quiz-answer').val(savedAnswer);
                    //     }
                    // }

                    wrapper.find('.exms-question-hint-btn').each( function(){
                        if( getQuestion.hint && getQuestion.hint.trim() !== '' ) {
                            $( this ).removeClass( 'disabled' ).attr( 'data-question-hint', getQuestion.hint );
                        } else {
                            $( this ).addClass( 'disabled' ).attr( 'data-question-hint', '' );
                        }
                    });

                    wrapper.find('.exms-hint-text').text('💡 ' + (getQuestion.hint || ''));


                    if( secondQuestionTimer ) {
                        wrapper.find('.exms-quiz-mobile-timer-area span:last-child').text( secondQuestionTimer );
                        wrapper.find('.exms-next-btn').attr( 'data-question_time', secondQuestionTimer );
                    } else {
                        wrapper.find('.exms-quiz-mobile-timer-area span:last-child').text('');
                        wrapper.find('.exms-next-btn').attr('data-question_time', '');
                    }

                    wrapper.find('.current-question').text(getQuestion.question_number || '');
                }
            },

            /**
             * Quiz Timer 
             */
            quizTimer: function () {
                    let timerElement = $( '#wpetimer' );
                    if ( !timerElement ) return;

                    let timeStr = timerElement.data( 'initial-time' ).toString().trim();
                    if ( !timeStr ) return;
                    totalSeconds = EXMSQUIZ.timeToSeconds( timeStr );

                    if ( isNaN( totalSeconds ) || totalSeconds <= 0 ) {
                        timerElement.text( "00:00" );
                        timerElement.data( 'timer-zero', true );
                        return false;
                    }

                    timerElement.removeData( 'timer-zero' );

                    clearInterval( EXMSQUIZ.timerInterval );

                    EXMSQUIZ.timerInterval = setInterval( function () {
                        totalSeconds--;

                        if ( totalSeconds <= 0 ) {

                            clearInterval( EXMSQUIZ.timerInterval );
                            timerElement.text( "00:00" );

                            let timerType = timerElement.data('timer-type');
                            if(timerType === 'quiz_timer') {
                                $( '.exms-modal' ).html( exms_quiz.quiz_end_message + '<button class="exms-close-quiz-auto-popup">'+exms_quiz.quiz_end_btn+'</button>' );
                                EXMSQUIZ.showModel();
                            } else {
                                let nextBtn = $('.exms-next-btn').first();
                                let question_timer = nextBtn.attr('data-question_time') || "0:00";
                                let totalTime = EXMSQUIZ.timeToSeconds(question_timer);
                                let submitModal = $('.exms-model-qiz-submit-btn').first();
                                let submitQuestion = submitModal.addClass('exms-submit-question').removeClass( 'exms-model-qiz-submit-btn' );
                                if( nextBtn.length ) {
                                    submitQuestion.trigger('click');
                                    EXMSQUIZ.closeModel();
                                }
                            }

                        } else {

                            let now = Math.floor(Date.now() / 1000);

                            if( exms_quiz.quiz_end_time > now ) {

                                let targetTime = exms_quiz.quiz_end_time;
                                let diffSeconds = exms_quiz.quiz_end_time - now;
                                totalSeconds = diffSeconds;
                                timerElement.text( EXMSQUIZ.formatTime(totalSeconds) );
                            } else {
                                timerElement.text( EXMSQUIZ.formatTime( totalSeconds ) );                                
                            }
                        }
                    }, 1000 );
            },

            /**
             * Save the current question's answer via AJAX
             */
            saveCurrentAnswer: function( questionID, questionType, questionScore, quizId, questionTime = 0 ) {

                // Get the user answer of the current question
                let answer = EXMSQUIZ.getQuestionAnswer( questionType, questionID );
                
                // Prepare FormData for file and non-file answers
                let submissionData = new FormData();
                submissionData.append('action', 'exms_answer_submit');
                submissionData.append('nonce', exms_quiz.answer_nonce);
                submissionData.append('quiz_id', quizId);
                submissionData.append('question_type', questionType);
                submissionData.append('question_id', questionID);
                submissionData.append('question_score', questionScore);
                submissionData.append('user_taken_time', questionTime );

                // Handle file upload separately
                if (questionType === 'file_upload') {

                    if (answer instanceof File) {
                        submissionData.append('answer', answer);
                    } else {
                        submissionData.append('answer', '');
                    }
                } else {

                    if( Array.isArray(answer) ) {
                        submissionData.append( 'answer', JSON.stringify(answer) );
                    } else {
                        submissionData.append( 'answer', answer );
                    }
                }

                // Send AJAX request
                $.ajax( {
                    url: exms_quiz.ajax_url,
                    type: 'POST',
                    data: submissionData,
                    dataType: 'json',
                    processData: false, // Don't process data
                    contentType: false, // Let the browser set the content type (for file upload)
                    success: (response) => {}
                } );
            },

            /**
             * Handle quiz completion and show the result
             */
            handleQuizSubmission: function( quizData = {}, uploadData = {} ) {
                
                // $( '#exms-modal' ).hide();
                let quizID = exms_quiz.quiz_id;
                let submissionData = new FormData();
                submissionData.append( 'action', 'exms_quiz_submit' );
                submissionData.append( 'nonce', exms_quiz.nonce );
                submissionData.append( 'quiz_id', quizID );
                submissionData.append( 'course_id', exms_quiz.course_id );
                submissionData.append( 'current_url', exms_quiz.current_url );
                submissionData.append( 'post_type', exms_quiz.post_type );
                
                if( 'on' == exms_quiz.quiz_timer ) {
                    let timeTaken = $( '#wpetimer' ).text();
                    submissionData.append( 'time_taken', timeTaken );
                }

                if( Array.isArray( quizData ) ) {
                    submissionData.append( 'quiz_data', JSON.stringify( quizData ) );
                }

                if( Array.isArray( uploadData ) ) {
                    uploadData = uploadData.filter( item => item !== undefined );
                  
                    $.each( $( uploadData ), function( index, item ) {
                        submissionData.append( 'upload_quiz_data_'+item.question_id, item.user_answer);
                    } );
                }

                // Send AJAX request
                $.ajax( {
                    url: exms_quiz.ajax_url,
                    type: 'POST',
                    data: submissionData,
                    dataType: 'json',
                    processData: false, // Don't process data
                    contentType: false, // Let the browser set the content type (for file upload)
                    success: (response) => {}
                } );
            },

            /**
             * collect the answer
             */
            collectAnswers: function() {
                let answers = {};
                this.questionAttempt = 0;
                $( '.exms-question-wrapper' ).each( ( index, element ) => {
                    let $question = $( element );
                    let questionId = $question.data( 'question-id' );
                    let questionType = $question.data( 'question-type' );
                    answers[ questionId ] = this.getQuestionAnswer( $question, questionType );
                    let answer = answers[questionId];
                    if (
                        ( Array.isArray( answer ) && answer.length > 0 ) ||
                        ( typeof answer === 'string' && answer.trim() !== '' ) ||
                        ( typeof answer === 'number' || typeof answer === 'boolean' )
                    ) {
                        this.questionAttempt++;
                    }
                });
                return answers;
            },

            /**
             * get the answer according to the question type
             */
            getQuestionAnswer: function( question_type, questionID ) {
                
                switch( question_type ) {
                    case 'multiple_choice':
                        return $( '.exms-answer-checkbox-'+questionID+':checked' ).map( function() {
                            return $( this ).val();
                        }).get();
                    case 'single_choice':
                        return $( '.exms-answer-radio-'+questionID+':checked').val();
                    case 'true_false':
                        // return question.find( 'input[type="radio"]:checked' ).val() || '';
                    case 'fill_blank':

                        return $("input[name='quiz-fill_blank_"+questionID+"[]']")
                        .map(function() {
                            return $(this).val();
                        }).get();
                    //case 'essay':
                    case 'free_choice':
                        return $( '#quiz-answer-textarea-'+questionID+'' ).val();
                     case 'range':
                        // return question.find( 'input[type="range"]' ).val();
                    case 'sorting_choice':
                        
                        let sortingArray = [];

                        $( '.exms-sortable-item-'+questionID+'' ).each( function( index, elem ) {
                            let text = $(elem).find( '.exms-sorting-answer' ).text().trim();
                            sortingArray.push(text);
                        } );
                        return sortingArray;
                    case 'file_upload':
                        let fileInput = $( '#quiz-file-upload-'+questionID+'' )[0];
                        return fileInput.files[0];
                    default:
                        // let defaultInput = question.find('input[type="text"]').first();
                        // return defaultInput.length ? defaultInput.val().trim() : '';
                }
            },

            /**
             * View the Submitted Answer
             */
            viewSubmittedAnswer: function() {

                let self = $(this);
                let quiz_id          = exms_quiz.quiz_id;
                let next             = exms_quiz.next;
                let prev             = exms_quiz.previous;
                let back             = exms_quiz.back_to_quiz;
                let correct          = exms_quiz.correct;
                let selectCorrect    = exms_quiz.select_correct;
                let selectWrong      = exms_quiz.select_wrong;
                let questionNum      = exms_quiz.question;

                let submitData = {
                    action: 'exms_quiz_answer_view',
                    nonce: exms_quiz.answer_view_nonce,
                    quiz_id: quiz_id,
                };

                self.prop( 'disabled', true ).change();

                $.ajax({
                    url: exms_quiz.ajax_url,
                    type: 'POST',
                    data: submitData,
                    dataType: 'json',
                    success: ( response ) => {
                        const questions = response?.data?.data || [];
                        if ( !Array.isArray( questions ) || questions.length === 0 ) return;
                        let currentIndex = 0;

                        EXMSQUIZ.renderQuestion( currentIndex, questions, prev, back, '', '', next, correct );

                        $( document ).off( 'click.wpeNextReview' ).on( 'click.wpeNextReview', '.exms-next-review-btn', function () {

                            if ( currentIndex < questions.length - 1 ) {
                                currentIndex++;
                                EXMSQUIZ.renderQuestion( currentIndex, questions, prev, back, selectCorrect, selectWrong, next, correct );
                            } else {
                                $( '.exms-answer-review-box' ).fadeOut( 300, function() {
                                    $( '.exms-quiz-result' ).fadeIn( 300 );
                                } ).change();
                                $('html, body').animate({ scrollTop: 300 }, 'slow');
                            }
                        } );

                        $( document ).off( 'click.wpePrevReview' ).on( 'click.wpePrevReview', '.exms-prev-review-btn', function () {
                            if ( currentIndex > 0 ) {
                                currentIndex--;
                                EXMSQUIZ.renderQuestion( currentIndex, questions, prev, back, selectCorrect, selectWrong, next, correct );
                            } else {
                                $( '.exms-answer-review-box' ).fadeOut( 300, function() {
                                    $( '.exms-quiz-result' ).fadeIn( 300 );
                                }).change();
                            }
                        } );

                        self.prop( 'disabled', false ).change();
                    },
                    error: ( xhr, status, error ) => {
                        console.error( 'Error fetching answers:', xhr, status, error );
                        self.prop( 'disabled', false );
                    }
                });
            },

            /**
             * create a function to render questions 
             */
            renderQuestion:function( index, questions, prev, back, selectCorrect = '', selectWrong = '', next, correct ) {
              
                let questionData = questions[index];
                let questionType = questionData.question_type;
                let question = questionData.question_content;
                let answers = questionData.answers;
                let userAnswer = questionData.user_answer;
                let questionTitle = questionData.question_title;
                let inputFields = '';
                let correctAnswer = '';
                let questionIsCorrect = questionData.user_is_correct;
                let questionStatus = questionData.user_is_correct == 1 ? 'correct' : 
                        questionData.user_is_correct == 0 ? 'wrong' : '';
                let statusColor = questionStatus == 'correct' ? '#6db46d' : 
                        questionStatus == 'wrong' ? 'red' : '';
                let selectedAnswer = '';
                let answerText = answers.map(x => x.text);
              
                switch( questionType ) {

                    case 'single_choice':

                        if ( Array.isArray( answers ) ) {
                            let correctIndex = answers.findIndex(item => item.correct === true);
                            let correctData = answers[correctIndex].text;

                            $.each(answers, function( index, ans ) { 
                                
                                let border = '';
                                if( ( correctData === ans.text && userAnswer.answer === correctData ) ) {
                                    border = '#22c55e';
                                } else if( ans.text == userAnswer.answer && correctData != userAnswer.answer ) {
                                    border = '#ef4444';
                                } else if( ans.text == correctData ) {
                                    border = '#22c55e';
                                }

                                selectedAnswer = (ans.text == userAnswer.answer) ? 'Selected' : '';
                                inputFields += `
                                <div class="exms-submit-option" style="border: 2px solid ${border};">
                                    <span class="badge">
                                    ${ans.text}
                                    </span>
                                    <span class="exms-selected-text">${selectedAnswer}</span>
                                </div>
                                `;
                            } );

                            if( ! questionStatus ) {
                                questionStatus = exms_quiz.not_attempt;
                                statusColor = 'red';
                            }
                            inputFields += `                                
                            <div class="question-status-wrapper" style="background-color: ${statusColor};">
                            ${questionStatus}
                            </div>`;
                        }
                        break;
                    case 'multiple_choice':

                        let correctAnswers = $.grep( answers, function(item) {
                            return item.correct === true;
                        } );
                        
                        $.each(answers, function( index, ans ) {

                            let border = '';
                            if( questionStatus && userAnswer.answer[index] && ans.text && 'correct' == questionStatus ) {
                                border = '#22c55e';
                            } else if( userAnswer.answer.length === 0 ) {
                                if ($.inArray( correctAnswers[index]?.text || '', answerText ) !== -1 ) {
                                    border = '#22c55e';
                                }
                            } else if( 'wrong' == questionStatus ) {

                                if( $.inArray( correctAnswers[index]?.text || '', answerText ) !== -1 ) {
                                    border = '#22c55e';
                                } else {
                                    border = 'red';
                                }
                            }

                            selectedAnswer = $.inArray( ans.text, userAnswer.answer ) !== -1 ? 'Selected' : '';
                            inputFields += `
                            <div class="exms-submit-option" style="border: 2px solid ${border};">
                            <span class="badge">
                            ${ans.text}
                            </span>
                            <span class="exms-selected-text">${selectedAnswer}</span>
                            </div>
                            `;
                        } );

                        if( ! questionStatus ) {
                            questionStatus = exms_quiz.not_attempt;
                            statusColor = 'red';
                        }

                        inputFields += `                                
                        <div class="question-status-wrapper" style="background-color: ${statusColor};">
                        ${questionStatus}
                        </div>`;
                        break;
                    case 'sorting_choice':
                        
                        let border = '';
                        let reversed = answerText.reverse();
                        $.each( userAnswer.answer, function( index, ans ) {
                            
                            if( ans == reversed[index] ) {
                                border = '#22c55e';
                            } else {
                                border = 'red';
                            }
                            inputFields += `
                            <div class="exms-submit-option" style="border: 2px solid ${border};">
                            <span class="badge">
                            ${ans}
                            </span>
                            </div>
                            `;
                        } );

                        if( ! questionStatus ) {
                            questionStatus = 'Not Attempted';
                        }
                        
                        inputFields += `                                
                        <div class="question-status-wrapper" style="background-color: ${statusColor};">
                        ${questionStatus}
                        </div>`;
                        break;
                    case 'file_upload':

                        let fileURL = '';
                        if( 'undefined' == userAnswer.answer.name || ! userAnswer.answer.name ) {
                            fileURL = '';
                        } else {
                            fileURL = userAnswer.answer.name;
                        }
                        inputFields += `
                        <div class="exms-submit-option">
                            <a href="${questionData.user_file_url}" class="badge" download>
                                ${fileURL}
                            </a>
                            <span class="exms-file-status">${questionIsCorrect}</span>
                        </div>
                        `;
                        break;
                    case 'fill_blank':

                        let correctAnswerFB = '';
                        if( 'wrong' == questionStatus && answers[0].text ) {
                            let correctAnswer = answers[0].text.replace(/\{(.*?)\}/g, '<span class="exms-fill-blank">$1</span>');
                            correctAnswerFB += ` 
                            <div class="exms-submit-option" style="border: 2px solid #22c55e;">
                            <span>${correctAnswer}</span>
                            </div>`;
                        }
                        let i = 0;
                        let replacedText = answers[0].text.replace(/\{(.*?)\}/g, function(match, p1) {
                            let val = typeof userAnswer.answer !== "undefined" ? userAnswer.answer[i] : p1;
                            i++;
                            if( ! val || 'undefined' == val ) {
                                val = '';
                            }
                            return `<span class="exms-fill-blank">${val}</span>`;
                        });

                        if( ! userAnswer.answer ) {
                            questionStatus = 'Not attempt';
                        }

                        inputFields += `
                        <div class="exms-submit-option">
                            <span>${replacedText}</span>
                        </div>
                        ${correctAnswerFB}
                        <div class="question-status-wrapper" style="background-color: ${statusColor};">
                        ${questionStatus}
                        </div>
                        `;
                        break;
                    case 'free_choice':
                        
                        let matches = '';
                        let values = '';
                        let correctAnswerFC = '';
                        let borderFC = questionStatus === 'wrong' ? 'red' : '2px solid #22c55e';
                       
                        if( answers[0].text && ( 'wrong' == questionStatus || ! questionStatus ) ) {

                            matches = answers[0].text.match(/"([^"]+)"/g);
                            values = $.map(matches, function(v) {
                                return v.replace(/"/g, '').split('|')[0];
                            } );

                            if( $.inArray(userAnswer.answer, values) === -1 ) {
                                $.each( $( values ), function( index, elem ) {

                                    correctAnswerFC += `
                                    <div class="exms-submit-option" style="border: 2px solid #22c55e;">
                                    <span>${elem}</span>
                                    </div>
                                    `;
                                } );
                            }
                        }

                        if( ! questionStatus ) {
                            questionStatus = 'Not attempt';
                        }
                        let userFreeChoiceAnswer = '';
                        if( ! userAnswer.answer ) {
                            userFreeChoiceAnswer = '--';
                        } else {
                            userFreeChoiceAnswer = userAnswer.answer;
                        }

                        inputFields += `
                        <div class="exms-submit-option" style="border: 2px solid ${borderFC};">
                        <span>${userFreeChoiceAnswer}</span>
                        </div>
                        ${correctAnswerFC}
                        <div class="question-status-wrapper" style="background-color: ${statusColor};">
                        ${questionStatus}
                        </div>
                        `;
                        break;
                    default: 
                        console.log("Invalid day");
                }

                let html = `
                <div class="exms-question-review" data-index="${index}">
                <div class="exms-circles">
                <span class="exms-circle green" aria-label="green circle"></span>
                <span class="exms-weong-answer-text">${exms_quiz.correct_answer}</span>
                <span class="exms-circle red" aria-label="red circle"></span>
                <span class="exms-weong-answer-text">${exms_quiz.wrong_answer}</span>
                </div>
                <h3 class="exms-question-number">${index + 1}/${questions.length}</h3>
                <p class="exms-question-title">${questionTitle}</p>
                <div class="exms-question-content">${question}</div>
                <div class="exms-answer-block">${inputFields}</div>
                <div class="exms-navigation">
                <button class="exms-prev-review-btn">${index === 0 ? back : prev }</button>
                <button class="exms-next-review-btn">${index === questions.length - 1 ? back : next }</button>
                </div>
                </div>
                `;
                $('.exms-answer-review-box').html(html).fadeIn(300).change();
            },


            /**
             * Return back to the Quiz
             */
            returnBackToQuiz: function () {

                $( '.exms-quiz-result' ).fadeOut( 300, function() {
                    $( '.exms-quiz-detail' ).fadeIn( 300 );
                } ).change();
                $( '.exms-quiz-box' ).fadeOut( 300 ).change();
                self.closeHint()

                /**
                 * Reset all input fields within the quiz
                 */
                $( '.exms-quiz-box input[type="radio"], .exms-quiz-box input[type="checkbox"]' ).prop( 'checked', false ).change();
                $( '.exms-quiz-box input[type="text"], .exms-quiz-box textarea' ).val( '' ).change();
                $( '.exms-quiz-box .selected' ).removeClass( 'selected' ).change();
                $( '.exms-quiz-box input[type="range"]' ).each( function () {
                    const min = $( this ).attr( 'min' ) || 0;
                    $( this ).val( min ).change();
                    $( '#range-value-' + this.id.replace( 'quiz-answer-range-', '' ) ).text( min ).change();
                });

                /**
                 * Clear any active timers
                 */
                if ( this.timerInterval ) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }

                /**
                 * Reset timer displays to initial time
                 */
                let initialTime = $( '#wpetimer' ).data( 'initial-time' ) || '30:00';
                let initialmobileTime = $( '.exms-time-remain' ).data( 'initial-time' ) || '30:00';
                $( '#wpetimer' ).text( initialTime ).change();
                $( '.exms-time-remain' ).text( initialmobileTime ).change();

                /**
                 * Reset timer progress indicators
                 */
                $( '.exms-timer' ).css( 'background', 'conic-gradient( #552CA8 0% 100%, #D9DDFD 100% 100% )' ).change();
                $( '.exms-progress-fill' ).css( {
                    'width': '100%',
                    'background-color': '#552CA8'
                } ).change();

                /**
                 * Reset current question and navigation
                 */
                this.currentQuestion = 0;
                this.containers.hide().eq( 0 ).show().change();
                this.updateNavigation();

                /**
                 * Reset internal counters and UI elements
                 */
                this.timeTaken = 0;
                this.questionAttempt = 0;
                let currentQ = this.currentQuestion + 1;
                $( '.current-question' ).text( currentQ ).change();
                $( '#exms-questionNav li' ).each( function( index ) {
                    if ( ( index + 1 ) <= currentQ ) {
                        $( this ).addClass( 'exms-completed' ).change();
                    } else {
                        $( this ).removeClass( 'exms-completed' ).change();
                    }
                });
                $( '.exms-submit-btn' ).prop( 'disabled', false ).change();
            },

            /**
             * Show the modal for quiz completion
             */
            showModel: function() {

                if (jQuery('#exms-modal').css('display') === 'none') {
                    jQuery('#exms-modal').fadeIn(400);
                } else {
                    jQuery('#exms-modal').fadeOut(400);
                }
            },

            /**
             * Close the modal
             */
            closeModel: function() {
                $( '#exms-modal' ).fadeOut( 300 ).change();
            },

            /**
             * Question hint display & close btn
             */
            questionHint: function() {
                $( document ).on( 'click', '.exms-question-hint-btn', function( e ) {
                    e.preventDefault();
                    let hint = $( this ).data( 'question-hint' );
                    EXMSQUIZ.showHint( hint , this );
                } );

                $( document ).on( 'click', '.exms-hint-close', function( e ) {
                    EXMSQUIZ.closeHint( this );
                } );
            },

            /**
             * Display the hint box 
             */
            showHint: function( hint , el ) {
                
                if(!hint || hint.trim() === '' ) return;
                let parent = $( el ).closest( '.exms-question-wrapper' );

                parent.find('.exms-hint-text').html(
                  '<span class="exms-hint-bulb">💡</span> ' + (hint || '')
                  ).change();
                parent.find( '.exms-hint-sidebar' ).fadeIn(300);
            },

            /**
             * close the hint box
             */
            closeHint: function( el ) {

                $( el ).closest( '.exms-hint-sidebar' ).fadeOut( 200 );
            },

            /**
             * Handle quiz Buy
             */
            HandleBuyQuiz: function() {

                $(document).on('click', '#quiz-buynow-button', function () {

                    let currentRequest = null;
                    let self = $(this);
                    let Quiz_id = self.attr('data-quiz-id');

                    $('body').append(
                        '<div class="exms-loader-overlay">' +
                            '<div class="exms-loader-spinner"></div>' +
                        '</div>'
                    );

                    currentRequest = $.ajax({
                        url: exms_quiz.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'exms_buy_quiz',
                            quiz_id : Quiz_id,
                            nonce: exms_quiz.quiz_buy_nonce  
                        },
                        success: function (response) {
                            if (response.status === 'not_logged_in') {
                                setTimeout(function () {
                                    $('.exms-loader-overlay').remove();

                                    $('body').append(
                                        '<div class="exms-login-message">' +
                                            response.message +
                                            ' <a href="' + response.login_url + '" target="_blank" class="exms-login-link">Login Now</a>' +
                                        '</div>'
                                    );

                                    setTimeout(function () {
                                        $('.exms-login-message').fadeOut(300, function () {
                                            $(this).remove();
                                        });
                                    }, 3000);
                                }, 1000);
                            } 
                            else if (response.status === 'success' || response.status === 'true') {
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
                            else if (response.status === 'show_payment_popup') {
                                $('.exms-loader-overlay').remove();

                                if (!$('#exms-payment-popup').length) {
                                    $('body').append(response.popup_html);
                                }

                                $('#exms-payment-popup').fadeIn();
                            } 
                            else {
                                $('.exms-loader-overlay').remove();
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
                    $('#exms-payment-popup').fadeOut();
                    $( '#exms-payment-popup-overlay' ).remove();
                });
            },

            timeToSeconds: function(t) {
                const parts = t.split(':').map(Number);
                if (parts.length === 3) {
                    return parts[0] * 3600 + parts[1] * 60 + parts[2];
                } else if (parts.length === 2) {
                    return parts[0] * 60 + parts[1];
                }
                return 0;
            },

            formatTime: function(seconds) {
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = seconds % 60;

                if (h > 0) {
                    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                } else {
                    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                }
            },
        };
        EXMSQUIZ.init();
    });
} )( jQuery );