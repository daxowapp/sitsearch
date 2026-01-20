jQuery(document).ready(function($) {
    'use strict';
    
    const SITRecommender = {
        sessionId: null,
        currentQuestionIndex: 0,
        questions: [],
        answers: {},
        recommendations: [],
        isGeneratingQuestions: false,
        numQuestions: 10,
        
        init: function() {
            this.bindEvents();
            this.showWelcomeScreen();
        },
        
        bindEvents: function() {
            $(document).on('click', '.sit-start-quiz', this.startQuiz.bind(this));
            $(document).on('click', '.sit-answer-option', this.selectAnswer.bind(this));
            $(document).on('click', '.sit-next-question', this.nextQuestion.bind(this));
            $(document).on('click', '.sit-prev-question', this.prevQuestion.bind(this));
            $(document).on('click', '.sit-get-recommendations', this.getRecommendations.bind(this));
            $(document).on('click', '.sit-restart-quiz', this.restartQuiz.bind(this));
            $(document).on('click', '.sit-view-programs', this.viewProgramsForField.bind(this));
            $(document).on('change', '.sit-num-questions', this.updateQuestionCount.bind(this));
            
            // Add debugging for all events
            console.log('SIT Recommender: Event bindings initialized');
        },
        
        showWelcomeScreen: function() {
            const welcomeHtml = `
                <div class="sit-welcome-screen">
                    <div class="sit-welcome-content">
                        <h2>🎓 Discover Your Ideal Study Path</h2>
                        <p>Our AI-powered assessment will analyze your interests, skills, and goals to recommend the perfect academic programs for you.</p>
                        
                        <div class="sit-quiz-options">
                            <label for="sit-num-questions">Number of Questions:</label>
                            <select id="sit-num-questions" class="sit-num-questions">
                                <option value="5">5 Questions (Quick)</option>
                                <option value="10" selected>10 Questions (Recommended)</option>
                                <option value="15">15 Questions (Detailed)</option>
                                <option value="20">20 Questions (Comprehensive)</option>
                            </select>
                        </div>
                        
                        <div class="sit-ai-notice">
                            <div class="sit-ai-badge">🤖 AI-Powered</div>
                            <p>Questions are dynamically generated using advanced AI to provide personalized insights into your academic interests and career aspirations.</p>
                        </div>
                        
                        <button class="sit-start-quiz sit-btn sit-btn-primary">
                            <span class="sit-btn-text">Start Assessment</span>
                            <span class="sit-btn-loading" style="display: none;">
                                <span class="sit-spinner"></span> Generating Questions...
                            </span>
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(welcomeHtml);
        },
        
        updateQuestionCount: function(e) {
            this.numQuestions = parseInt($(e.target).val());
            console.log('Number of questions updated to:', this.numQuestions);
        },
        
        startQuiz: function(e) {
            e.preventDefault();
            
            if (this.isGeneratingQuestions) return;
            
            // Get the current selected number of questions
            this.numQuestions = parseInt($('.sit-num-questions').val()) || 10;
            console.log('Starting quiz with', this.numQuestions, 'questions');
            
            this.isGeneratingQuestions = true;
            const $btn = $('.sit-start-quiz');
            $btn.find('.sit-btn-text').hide();
            $btn.find('.sit-btn-loading').show();
            $btn.prop('disabled', true);
            
            // Debug: Test API connection first
            console.log('Testing API connection...', sitRecommender.apiUrl);
            
            // Test endpoint first
            $.ajax({
                url: sitRecommender.apiUrl + 'test',
                method: 'GET',
                success: (response) => {
                    console.log('API test successful:', response);
                    this.proceedWithQuizStart();
                },
                error: (xhr) => {
                    console.error('API test failed:', xhr);
                    let errorMsg = 'API connection failed. ';
                    if (xhr.status === 404) {
                        errorMsg += 'REST API routes not found. Please check if the plugin is properly activated.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += xhr.responseJSON.message;
                    } else {
                        errorMsg += `HTTP ${xhr.status}: ${xhr.statusText}`;
                    }
                    this.showError(errorMsg);
                    this.isGeneratingQuestions = false;
                    $btn.find('.sit-btn-text').show();
                    $btn.find('.sit-btn-loading').hide();
                }
            });
        },
        
        proceedWithQuizStart: function() {
            // Initialize chat-based assessment
            this.sessionId = 'chat_session_' + Date.now();
            this.conversationHistory = [];
            this.currentStep = 0;
            this.userProfile = {};
            
            this.showChatScreen();
            this.startConversation();
        },
                    
                    <div class="sit-quiz-navigation">
                        <button class="sit-prev-question sit-btn sit-btn-secondary" style="display: none;">
                            Previous
                        </button>
                        <button class="sit-next-question sit-btn sit-btn-primary" style="display: none;">
                            Next Question
                        </button>
                        <button class="sit-get-recommendations sit-btn sit-btn-success" style="display: none;">
                            Get My Recommendations
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(quizHtml);
            this.displayCurrentQuestion();
        },
        
        displayCurrentQuestion: function() {
            if (this.currentQuestionIndex >= this.questions.length) {
                this.showRecommendationButton();
                return;
            }
            
            const question = this.questions[this.currentQuestionIndex];
            const progress = ((this.currentQuestionIndex + 1) / this.questions.length) * 100;
            
            // Update progress
            $('.sit-progress-fill').css('width', progress + '%');
            $('.sit-current-q').text(this.currentQuestionIndex + 1);
            
            // Build question HTML
            let optionsHtml = '';
            question.options.forEach(option => {
                const isSelected = this.answers[question.id] === option.id;
                optionsHtml += `
                    <div class="sit-answer-option ${isSelected ? 'selected' : ''}" 
                         data-question-id="${question.id}" 
                         data-answer-id="${option.id}">
                        <div class="sit-option-radio">
                            <span class="sit-radio-mark ${isSelected ? 'checked' : ''}"></span>
                        </div>
                        <div class="sit-option-text">${option.text}</div>
                    </div>
                `;
            });
            
            const questionHtml = `
                <div class="sit-question">
                    <h3 class="sit-question-text">${question.question}</h3>
                    <div class="sit-question-options">
                        ${optionsHtml}
                    </div>
                </div>
            `;
            
            $('.sit-question-container').html(questionHtml);
            
            // Debug: Check if elements were created
            console.log('Question HTML inserted');
            console.log('Answer options found:', $('.sit-answer-option').length);
            
            // Test click binding manually
            $('.sit-answer-option').off('click').on('click', function(e) {
                console.log('Direct click handler triggered!', this);
                SITRecommender.selectAnswer.call(SITRecommender, e);
            });
            
            // Update navigation buttons
            $('.sit-prev-question').toggle(this.currentQuestionIndex > 0);
            $('.sit-next-question').toggle(this.currentQuestionIndex < this.questions.length - 1 && this.answers[question.id]);
            $('.sit-get-recommendations').toggle(this.currentQuestionIndex === this.questions.length - 1 && this.answers[question.id]);
        },
        
        selectAnswer: function(e) {
            console.log('Answer option clicked!', e.currentTarget);
            const $option = $(e.currentTarget);
            const questionId = $option.data('question-id');
            const answerId = $option.data('answer-id');
            
            console.log('Question ID:', questionId, 'Answer ID:', answerId);
            
            // Update UI
            $option.siblings().removeClass('selected').find('.sit-radio-mark').removeClass('checked');
            $option.addClass('selected').find('.sit-radio-mark').addClass('checked');
            
            // Store answer
            this.answers[questionId] = answerId;
            console.log('Current answers:', this.answers);
            
            // Submit answer to server (skip for test)
            // this.submitAnswer(questionId, answerId);
            
            // Update navigation
            $('.sit-next-question').toggle(this.currentQuestionIndex < this.questions.length - 1);
            $('.sit-get-recommendations').toggle(this.currentQuestionIndex === this.questions.length - 1);
        },
        
        submitAnswer: function(questionId, answerId) {
            $.ajax({
                url: sitRecommender.apiUrl + '/quiz/answer',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': sitRecommender.nonce
                },
                data: {
                    session_id: this.sessionId,
                    question_id: questionId,
                    answer_id: answerId
                },
                success: (response) => {
                    if (!response.success) {
                        console.error('Failed to submit answer:', response.message);
                    }
                },
                error: (xhr) => {
                    console.error('Error submitting answer:', xhr.responseJSON);
                }
            });
        },
        
        nextQuestion: function(e) {
            e.preventDefault();
            if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
                this.displayCurrentQuestion();
            }
        },
        
        prevQuestion: function(e) {
            e.preventDefault();
            if (this.currentQuestionIndex > 0) {
                this.currentQuestionIndex--;
                this.displayCurrentQuestion();
            }
        },
        
        getRecommendations: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            $btn.prop('disabled', true).html('<span class="sit-spinner"></span> Analyzing Your Answers...');
            
            $.ajax({
                url: sitRecommender.apiUrl + 'test-recommend',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': sitRecommender.nonce
                },
                data: {
                    session_id: this.sessionId
                },
                success: (response) => {
                    if (response.success) {
                        this.recommendations = response.recommendations;
                        this.programsByField = response.programs_by_field;
                        this.showResults();
                    } else {
                        this.showError('Failed to generate recommendations: ' + (response.message || 'Unknown error'));
                    }
                },
                error: (xhr) => {
                    let errorMsg = 'Failed to analyze your answers. ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += xhr.responseJSON.message;
                    }
                    this.showError(errorMsg);
                },
                complete: () => {
                    $btn.prop('disabled', false).html('Get My Recommendations');
                }
            });
        },
        
        showResults: function() {
            let resultsHtml = `
                <div class="sit-results-screen">
                    <div class="sit-results-header">
                        <h2>🎯 Your Personalized Study Recommendations</h2>
                        <p>Based on your answers, here are the fields of study that best match your interests and goals:</p>
                    </div>
                    
                    <div class="sit-recommendations">
            `;
            
            this.recommendations.forEach((rec, index) => {
                const confidenceClass = rec.confidence >= 80 ? 'high' : rec.confidence >= 60 ? 'medium' : 'low';
                const studyAreas = this.programsByField[rec.field] || [];
                const totalPrograms = studyAreas.reduce((sum, area) => sum + area.count, 0);
                const programCountClass = totalPrograms > 0 ? '' : 'zero';
                
                resultsHtml += `
                    <div class="sit-recommendation-card">
                        <div class="sit-rec-header">
                            <h3 class="sit-rec-title">
                                <span class="sit-rec-rank">#${index + 1}</span>
                                ${rec.field}
                            </h3>
                            <div class="sit-confidence-badge ${confidenceClass}">
                                ${rec.confidence}% Match
                            </div>
                        </div>
                        
                        <div class="sit-rec-content">
                            <div class="sit-rec-fit">
                                <h4>Why This Field Suits You:</h4>
                                <p>${rec.why_good_fit}</p>
                            </div>
                            
                            <div class="sit-rec-reasons">
                                <h4>Key Reasons:</h4>
                                <ul>
                                    ${rec.reasons.map(reason => `<li>${reason}</li>`).join('')}
                                </ul>
                            </div>
                            
                            <div class="sit-rec-careers">
                                <h4>Career Prospects:</h4>
                                <p>${rec.career_prospects}</p>
                            </div>
                            
                            <div class="sit-rec-programs">
                                <div class="sit-programs-info">
                                    <span class="sit-program-count ${programCountClass}">${totalPrograms} programs available</span>
                                </div>
                                ${totalPrograms > 0 ? `
                                    <button class="sit-view-programs sit-btn sit-btn-primary" 
                                            data-field="${rec.field}">
                                        View Study Areas
                                    </button>
                                ` : `
                                    <button class="sit-search-external sit-btn sit-btn-secondary" 
                                            data-field="${rec.field}"
                                            onclick="window.open('http://search.studyinturkiye.com/study-areas', '_blank')">
                                        Browse All Study Areas
                                    </button>
                                `}
                            </div>
                            
                            ${studyAreas.length > 0 ? `
                                <div class="sit-study-areas">
                                    <h4>Related Study Areas:</h4>
                                    <div class="sit-area-list">
                                        ${studyAreas.map(area => `
                                            <div class="sit-area-item">
                                                <div class="sit-area-info">
                                                    <span class="sit-area-name">${area.name}</span>
                                                    <span class="sit-area-count">${area.count} programs</span>
                                                </div>
                                                <a href="${area.url}" target="_blank" class="sit-area-link">
                                                    Explore <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            
            resultsHtml += `
                    </div>
                    
                    <div class="sit-results-actions">
                        <button class="sit-restart-quiz sit-btn sit-btn-secondary">
                            Take Assessment Again
                        </button>
                        <button class="sit-save-results sit-btn sit-btn-outline">
                            Save Results (PDF)
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(resultsHtml);
        },
        
        viewProgramsForField: function(e) {
            e.preventDefault();
            const field = $(e.currentTarget).data('field');
            const programs = this.programsByField[field] || [];
            
            if (programs.length === 0) {
                this.showError('No programs found for ' + field);
                return;
            }
            
            // Use your existing search plugin shortcode to display programs
            // This integrates with your SIT Search plugin
            let programsHtml = `
                <div class="sit-programs-screen">
                    <div class="sit-programs-header">
                        <h2>📚 ${field} Programs</h2>
                        <p>Here are the available programs in ${field}:</p>
                        <button class="sit-back-to-results sit-btn sit-btn-secondary">
                            ← Back to Recommendations
                        </button>
                    </div>
                    
                    <div class="sit-programs-list">
            `;
            
            programs.forEach(program => {
                programsHtml += `
                    <div class="sit-program-card">
                        <div class="sit-program-image">
                            ${program.featured_image ? `<img src="${program.featured_image}" alt="${program.title}">` : '<div class="sit-program-placeholder">📖</div>'}
                        </div>
                        <div class="sit-program-content">
                            <h3><a href="${program.permalink}" target="_blank">${program.title}</a></h3>
                            <p>${program.excerpt || 'Program description not available.'}</p>
                            <div class="sit-program-meta">
                                ${program.meta.sit_program_school ? `<span class="sit-meta-item">🏫 ${program.meta.sit_program_school[0]}</span>` : ''}
                                ${program.meta.sit_program_duration ? `<span class="sit-meta-item">⏱️ ${program.meta.sit_program_duration[0]} years</span>` : ''}
                                ${program.meta.sit_program_mode ? `<span class="sit-meta-item">📚 ${program.meta.sit_program_mode[0]}</span>` : ''}
                            </div>
                            <a href="${program.permalink}" class="sit-btn sit-btn-primary" target="_blank">
                                Learn More
                            </a>
                        </div>
                    </div>
                `;
            });
            
            programsHtml += `
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(programsHtml);
            
            // Bind back button
            $(document).on('click', '.sit-back-to-results', () => {
                this.showResults();
            });
        },
        
        restartQuiz: function(e) {
            e.preventDefault();
            this.sessionId = null;
            this.currentQuestionIndex = 0;
            this.questions = [];
            this.answers = {};
            this.recommendations = [];
            this.showWelcomeScreen();
        },
        
        showError: function(message) {
            const errorHtml = `
                <div class="sit-error-screen">
                    <div class="sit-error-content">
                        <div class="sit-error-icon">⚠️</div>
                        <h3>Oops! Something went wrong</h3>
                        <p>${message}</p>
                        <button class="sit-restart-quiz sit-btn sit-btn-primary">
                            Try Again
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(errorHtml);
        }
    };
    
    // Initialize the recommender
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('SIT container found:', $('.sit-recommender-container').length);
    
    if ($('.sit-recommender-container').length) {
        console.log('Initializing SIT Recommender...');
        SITRecommender.init();
    } else {
        console.error('SIT Recommender container not found!');
    }
});
