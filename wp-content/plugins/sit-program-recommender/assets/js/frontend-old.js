/**
 * SIT Program Recommender Frontend JavaScript
 * 
 * Handles the interactive quiz interface and program recommendations.
 */

(function($) {
    'use strict';
    
    // Main SIT Recommender object
    window.SITRecommender = {
        
        // Configuration
        config: {
            container: '#sit-recommender',
            apiUrl: sitRecommender.apiUrl,
            nonce: sitRecommender.nonce,
        },
        
        // State
        state: {
            sessionId: null,
            currentQuestionIndex: 0,
            questions: [],
            answers: {},
            recommendations: [],
            isGeneratingQuestions: false,
            
            // DOM elements
            elements: {
                container: $container,
                loadingOverlay: $container.find('.sit-loading-overlay'),
                screens: {
                    welcome: $container.find('.sit-welcome-screen'),
                    quiz: $container.find('.sit-quiz-screen'),
                    results: $container.find('.sit-results-screen'),
                    browse: $container.find('.sit-browse-screen'),
                    error: $container.find('.sit-error-screen')
                },
                buttons: {
                    startQuiz: $container.find('.sit-start-quiz'),
                    browsePrograms: $container.find('.sit-browse-programs'),
                    exitQuiz: $container.find('.sit-exit-quiz'),
                    prevQuestion: $container.find('.sit-prev-question'),
                    nextQuestion: $container.find('.sit-next-question'),
                    getRecommendations: $container.find('.sit-get-recommendations'),
                    retakeQuiz: $container.find('.sit-retake-quiz'),
                    toggleFilters: $container.find('.sit-toggle-filters'),
                    applyFilters: $container.find('.sit-apply-filters'),
                    clearFilters: $container.find('.sit-clear-filters'),
                    loadMore: $container.find('.sit-load-more'),
                    backToQuiz: $container.find('.sit-back-to-quiz'),
                    backToStart: $container.find('.sit-back-to-start'),
                    retryAction: $container.find('.sit-retry-action')
                },
                quiz: {
                    progressBar: $container.find('.sit-progress-fill'),
                    progressText: $container.find('.sit-progress-text'),
                    questionContainer: $container.find('.sit-question-container')
                },
                results: {
                    summary: $container.find('.sit-results-summary'),
                    container: $container.find('.sit-recommendations-container'),
                    filtersPanel: $container.find('.sit-filters-panel')
                },
                browse: {
                    searchInput: $container.find('.sit-search-input'),
                    searchBtn: $container.find('.sit-search-btn'),
                    results: $container.find('.sit-browse-results')
                },
                error: {
                    message: $container.find('.sit-error-message')
                }
            };
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;
            
            // Navigation buttons
            this.elements.buttons.startQuiz.on('click', function() {
                self.startQuiz();
            });
            
            this.elements.buttons.browsePrograms.on('click', function() {
                self.showScreen('browse');
                self.loadAllPrograms();
            });
            
            this.elements.buttons.exitQuiz.on('click', function() {
                self.showScreen('welcome');
                self.resetQuiz();
            });
            
            this.elements.buttons.prevQuestion.on('click', function() {
                self.previousQuestion();
            });
            
            this.elements.buttons.nextQuestion.on('click', function() {
                self.nextQuestion();
            });
            
            this.elements.buttons.getRecommendations.on('click', function() {
                self.getRecommendations();
            });
            
            this.elements.buttons.retakeQuiz.on('click', function() {
                self.showScreen('welcome');
                self.resetQuiz();
            });
            
            this.elements.buttons.toggleFilters.on('click', function() {
                self.elements.results.filtersPanel.toggle();
            });
            
            this.elements.buttons.applyFilters.on('click', function() {
                self.applyFilters();
            });
            
            this.elements.buttons.clearFilters.on('click', function() {
                self.clearFilters();
            });
            
            this.elements.buttons.backToQuiz.on('click', function() {
                self.showScreen('welcome');
            });
            
            this.elements.buttons.backToStart.on('click', function() {
                self.showScreen('welcome');
                self.resetQuiz();
            });
            
            this.elements.buttons.retryAction.on('click', function() {
                self.showScreen('welcome');
            });
            
            // Search functionality
            this.elements.browse.searchBtn.on('click', function() {
                self.searchPrograms();
            });
            
            this.elements.browse.searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    self.searchPrograms();
                }
            });
            
            // Dynamic answer selection
            this.elements.container.on('change', '.sit-answer-option input', function() {
                self.selectAnswer($(this));
            });
        },
        
        /**
         * Show loading state
         */
        showLoading: function(message) {
            this.state.isLoading = true;
            this.elements.loadingOverlay.find('.sit-loading-text').text(message || this.config.strings.loading);
            this.elements.loadingOverlay.show();
        },
        
        /**
         * Hide loading state
         */
        hideLoading: function() {
            this.state.isLoading = false;
            this.elements.loadingOverlay.hide();
        },
        
        /**
         * Show specific screen
         */
        showScreen: function(screenName) {
            this.state.currentScreen = screenName;
            this.elements.container.find('.sit-screen').hide();
            this.elements.screens[screenName].show();
        },
        
        /**
         * Show error screen
         */
        showError: function(message) {
            this.elements.error.message.text(message);
            this.showScreen('error');
        },
        
        /**
         * Start the quiz
         */
        startQuiz: function() {
            var self = this;
            
            this.showLoading(this.config.strings.loading);
            
            $.ajax({
                url: this.config.apiUrl + 'quiz/start',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': this.config.nonce
                },
                data: {
                    user_data: {} // Could collect additional user data here
                },
                success: function(response) {
                    if (response.success) {
                        self.state.sessionId = response.session_id;
                        self.state.questions = response.questions;
                        self.state.currentQuestionIndex = 0;
                        self.state.answers = {};
                        
                        self.showScreen('quiz');
                        self.renderQuestion();
                    } else {
                        self.showError(response.message || self.config.strings.error);
                    }
                },
                error: function() {
                    self.showError(self.config.strings.error);
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        /**
         * Render current question
         */
        renderQuestion: function() {
            var question = this.state.questions[this.state.currentQuestionIndex];
            if (!question) return;
            
            var html = '<div class="sit-question" data-question-id="' + question.id + '">';
            html += '<h3 class="sit-question-text">' + this.escapeHtml(question.question) + '</h3>';
            
            if (question.required) {
                html += '<span class="sit-required-indicator">' + this.config.strings.required + '</span>';
            }
            
            html += '<div class="sit-answer-options">';
            
            question.options.forEach(function(option) {
                var checked = this.state.answers[question.id] === option.id ? 'checked' : '';
                html += '<label class="sit-answer-option">';
                html += '<input type="radio" name="question_' + question.id + '" value="' + option.id + '" ' + checked + '>';
                html += '<span class="sit-option-text">' + this.escapeHtml(option.text) + '</span>';
                html += '</label>';
            }.bind(this));
            
            html += '</div></div>';
            
            this.elements.quiz.questionContainer.html(html);
            this.updateQuizNavigation();
            this.updateProgress();
        },
        
        /**
         * Select an answer
         */
        selectAnswer: function($input) {
            var questionId = parseInt($input.closest('.sit-question').data('question-id'));
            var answerId = $input.val();
            
            this.state.answers[questionId] = answerId;
            
            // Submit answer to server
            this.submitAnswer(questionId, answerId);
            
            this.updateQuizNavigation();
        },
        
        /**
         * Submit answer to server
         */
        submitAnswer: function(questionId, answerId) {
            var self = this;
            
            $.ajax({
                url: this.config.apiUrl + 'quiz/answer',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': this.config.nonce
                },
                data: {
                    session_id: this.state.sessionId,
                    question_id: questionId,
                    answer_id: answerId
                },
                success: function(response) {
                    if (response.success) {
                        self.updateProgress(response.progress);
                        
                        if (response.is_complete) {
                            self.elements.buttons.getRecommendations.show();
                            self.elements.buttons.nextQuestion.hide();
                        }
                    }
                },
                error: function() {
                    // Handle silently for now
                }
            });
        },
        
        /**
         * Go to next question
         */
        nextQuestion: function() {
            if (this.state.currentQuestionIndex < this.state.questions.length - 1) {
                this.state.currentQuestionIndex++;
                this.renderQuestion();
            }
        },
        
        /**
         * Go to previous question
         */
        previousQuestion: function() {
            if (this.state.currentQuestionIndex > 0) {
                this.state.currentQuestionIndex--;
                this.renderQuestion();
            }
        },
        
        /**
         * Update quiz navigation
         */
        updateQuizNavigation: function() {
            var currentQuestion = this.state.questions[this.state.currentQuestionIndex];
            var hasAnswer = this.state.answers[currentQuestion.id];
            var isLastQuestion = this.state.currentQuestionIndex === this.state.questions.length - 1;
            var allRequiredAnswered = this.checkAllRequiredAnswered();
            
            // Previous button
            this.elements.buttons.prevQuestion.toggle(this.state.currentQuestionIndex > 0);
            
            // Next button
            this.elements.buttons.nextQuestion.prop('disabled', !hasAnswer);
            this.elements.buttons.nextQuestion.toggle(!isLastQuestion || !allRequiredAnswered);
            
            // Get recommendations button
            this.elements.buttons.getRecommendations.toggle(isLastQuestion && allRequiredAnswered);
        },
        
        /**
         * Check if all required questions are answered
         */
        checkAllRequiredAnswered: function() {
            var self = this;
            return this.state.questions.every(function(question) {
                return !question.required || self.state.answers[question.id];
            });
        },
        
        /**
         * Update progress bar
         */
        updateProgress: function(progress) {
            if (typeof progress === 'undefined') {
                var answered = Object.keys(this.state.answers).length;
                progress = (answered / this.state.questions.length) * 100;
            }
            
            this.elements.quiz.progressBar.css('width', progress + '%');
            this.elements.quiz.progressText.text(Math.round(progress) + '%');
        },
        
        /**
         * Get recommendations
         */
        getRecommendations: function() {
            var self = this;
            
            this.showLoading('Generating your personalized recommendations...');
            
            $.ajax({
                url: this.config.apiUrl + 'recommend',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': this.config.nonce
                },
                data: {
                    session_id: this.state.sessionId,
                    filters: this.state.filters,
                    use_openai: false // Could be made configurable
                },
                success: function(response) {
                    if (response.success) {
                        self.state.recommendations = response.recommendations;
                        self.showScreen('results');
                        self.renderRecommendations();
                        self.updateResultsSummary(response.total_found);
                    } else {
                        self.showError(response.message || self.config.strings.error);
                    }
                },
                error: function() {
                    self.showError(self.config.strings.error);
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        /**
         * Render recommendations
         */
        renderRecommendations: function() {
            var html = '';
            
            if (this.state.recommendations.length === 0) {
                html = '<div class="sit-no-results">';
                html += '<h3>' + this.config.strings.noResults + '</h3>';
                html += '<p>Try adjusting your filters or retaking the assessment.</p>';
                html += '</div>';
            } else {
                html = '<div class="sit-recommendations-grid">';
                
                this.state.recommendations.forEach(function(rec) {
                    html += this.renderProgramCard(rec, true);
                }.bind(this));
                
                html += '</div>';
            }
            
            this.elements.results.container.html(html);
        },
        
        /**
         * Render a program card
         */
        renderProgramCard: function(program, isRecommendation) {
            var html = '<div class="sit-program-card';
            if (isRecommendation) {
                html += ' sit-recommendation-card';
            }
            html += '" data-program-id="' + program.id + '">';
            
            // Featured image
            if (program.featured_image) {
                html += '<div class="sit-program-image">';
                html += '<img src="' + program.featured_image + '" alt="' + this.escapeHtml(program.title) + '" loading="lazy">';
                
                if (isRecommendation && program.match_strength) {
                    var matchClass = program.match_strength.toLowerCase().replace(' ', '-');
                    html += '<div class="sit-match-badge sit-match-' + matchClass + '">';
                    html += this.escapeHtml(program.match_strength);
                    html += '</div>';
                }
                
                html += '</div>';
            }
            
            // Content
            html += '<div class="sit-program-content">';
            html += '<h3 class="sit-program-title">';
            html += '<a href="' + program.permalink + '">' + this.escapeHtml(program.title) + '</a>';
            html += '</h3>';
            
            // Meta information
            if (program.meta) {
                html += '<div class="sit-program-meta">';
                
                if (program.meta.school) {
                    html += '<span class="sit-meta-item sit-meta-school">';
                    html += '<i class="sit-icon-school"></i>';
                    html += this.escapeHtml(program.meta.school);
                    html += '</span>';
                }
                
                if (program.meta.level) {
                    html += '<span class="sit-meta-item sit-meta-level">';
                    html += '<i class="sit-icon-level"></i>';
                    html += this.escapeHtml(program.meta.level);
                    html += '</span>';
                }
                
                if (program.meta.duration) {
                    html += '<span class="sit-meta-item sit-meta-duration">';
                    html += '<i class="sit-icon-duration"></i>';
                    html += this.escapeHtml(program.meta.duration) + ' years';
                    html += '</span>';
                }
                
                html += '</div>';
            }
            
            // Excerpt
            if (program.excerpt) {
                html += '<p class="sit-program-excerpt">' + this.escapeHtml(program.excerpt) + '</p>';
            }
            
            // Recommendation reasons
            if (isRecommendation && program.reasons && program.reasons.length > 0) {
                html += '<div class="sit-recommendation-reasons">';
                html += '<h4>Why this program matches you:</h4>';
                html += '<ul>';
                program.reasons.forEach(function(reason) {
                    html += '<li>' + this.escapeHtml(reason) + '</li>';
                }.bind(this));
                html += '</ul>';
                html += '</div>';
            }
            
            // Match score
            if (isRecommendation && program.score) {
                var scorePercent = Math.round(program.score * 100);
                html += '<div class="sit-match-score">';
                html += '<span class="sit-score-label">Match Score:</span>';
                html += '<div class="sit-score-bar">';
                html += '<div class="sit-score-fill" style="width: ' + scorePercent + '%;"></div>';
                html += '</div>';
                html += '<span class="sit-score-value">' + scorePercent + '%</span>';
                html += '</div>';
            }
            
            // Actions
            html += '<div class="sit-program-actions">';
            html += '<a href="' + program.permalink + '" class="sit-btn sit-btn-primary">';
            html += this.config.strings.viewProgram;
            html += '</a>';
            html += '</div>';
            
            html += '</div>'; // .sit-program-content
            html += '</div>'; // .sit-program-card
            
            return html;
        },
        
        /**
         * Update results summary
         */
        updateResultsSummary: function(totalFound) {
            var message = 'Found ' + totalFound + ' programs that match your profile.';
            this.elements.results.summary.text(message);
        },
        
        /**
         * Load filter options
         */
        loadFilterOptions: function() {
            var self = this;
            
            $.ajax({
                url: this.config.apiUrl + 'filters',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.populateFilterOptions(response.filters);
                    }
                },
                error: function() {
                    // Handle silently
                }
            });
        },
        
        /**
         * Populate filter options
         */
        populateFilterOptions: function(filters) {
            var self = this;
            
            Object.keys(filters).forEach(function(filterType) {
                var $select = self.elements.container.find('.sit-filter-select[data-filter="' + filterType.replace('s', '') + '"]');
                
                if ($select.length && filters[filterType]) {
                    filters[filterType].forEach(function(option) {
                        $select.append('<option value="' + option + '">' + self.escapeHtml(option) + '</option>');
                    });
                }
            });
        },
        
        /**
         * Apply filters
         */
        applyFilters: function() {
            var filters = {};
            
            this.elements.container.find('.sit-filter-select').each(function() {
                var $select = $(this);
                var filterName = $select.data('filter');
                var value = $select.val();
                
                if (value) {
                    filters[filterName] = value;
                }
            });
            
            this.state.filters = filters;
            this.getRecommendations(); // Re-get recommendations with filters
        },
        
        /**
         * Clear filters
         */
        clearFilters: function() {
            this.elements.container.find('.sit-filter-select').val('');
            this.state.filters = {};
            this.getRecommendations();
        },
        
        /**
         * Load all programs for browsing
         */
        loadAllPrograms: function() {
            var self = this;
            
            this.showLoading();
            
            $.ajax({
                url: this.config.apiUrl + 'programs',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': this.config.nonce
                },
                data: {
                    per_page: 20
                },
                success: function(response) {
                    if (response.success) {
                        self.renderBrowseResults(response.programs);
                    } else {
                        self.showError(response.message || self.config.strings.error);
                    }
                },
                error: function() {
                    self.showError(self.config.strings.error);
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        /**
         * Search programs
         */
        searchPrograms: function() {
            var query = this.elements.browse.searchInput.val().trim();
            var self = this;
            
            if (!query) {
                this.loadAllPrograms();
                return;
            }
            
            this.showLoading();
            
            $.ajax({
                url: this.config.apiUrl + 'programs',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': this.config.nonce
                },
                data: {
                    search: query,
                    per_page: 20
                },
                success: function(response) {
                    if (response.success) {
                        self.renderBrowseResults(response.programs);
                    } else {
                        self.showError(response.message || self.config.strings.error);
                    }
                },
                error: function() {
                    self.showError(self.config.strings.error);
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        /**
         * Render browse results
         */
        renderBrowseResults: function(programs) {
            var html = '';
            
            if (programs.length === 0) {
                html = '<div class="sit-no-results">';
                html += '<h3>' + this.config.strings.noResults + '</h3>';
                html += '</div>';
            } else {
                html = '<div class="sit-programs-grid">';
                
                programs.forEach(function(program) {
                    html += this.renderProgramCard(program, false);
                }.bind(this));
                
                html += '</div>';
            }
            
            this.elements.browse.results.html(html);
        },
        
        /**
         * Reset quiz state
         */
        resetQuiz: function() {
            this.state.sessionId = null;
            this.state.questions = [];
            this.state.currentQuestionIndex = 0;
            this.state.answers = {};
            this.state.recommendations = [];
            this.state.filters = {};
            
            this.elements.quiz.progressBar.css('width', '0%');
            this.elements.quiz.progressText.text('0%');
            this.elements.quiz.questionContainer.empty();
        },
        
        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    };
    
})(jQuery);
