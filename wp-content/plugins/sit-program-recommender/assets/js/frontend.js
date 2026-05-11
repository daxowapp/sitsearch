jQuery(document).ready(function($) {
    'use strict';
    
    const SITChatRecommender = {
        sessionId: null,
        conversationHistory: [],
        currentStep: 0,
        userProfile: {},
        isTyping: false,
        totalQuestions: 10,
        
        init: function() {
            this.bindEvents();
            // Simulate initialization delay
            setTimeout(() => {
                this.showWelcomeScreen();
            }, 800);
        },
        
        bindEvents: function() {
            $(document).on('click', '.sit-start-chat', this.startChat.bind(this));
            $(document).on('click', '.sit-chat-option', this.selectChatOption.bind(this));
            $(document).on('click', '.sit-restart-chat', this.restartChat.bind(this));
            $(document).on('click', '#sit-name-submit', this.submitStudentName.bind(this));
            $(document).on('keypress', '#sit-student-name', (e) => {
                if (e.which === 13) this.submitStudentName(e);
            });
            $(document).on('keypress', '#sit-text-answer', (e) => {
                if (e.which === 13) this.submitTextAnswer(e);
            });
            $(document).on('click', '#sit-text-submit', this.submitTextAnswer.bind(this));
        },
        
        showWelcomeScreen: function() {
            const welcomeHtml = `
                <div class="sit-welcome-screen" style="animation: fadeIn 0.5s ease;">
                    <div class="sit-bot-initialize" style="min-height: auto; padding: 60px 40px; text-align: center;">
                        <h2 style="font-size: 32px; font-weight: 800; margin-bottom: 16px;">AI Study Path Advisor</h2>
                        <p style="font-size: 18px; color: var(--sit-text-muted); max-width: 600px; margin: 0 auto 30px; line-height: 1.6;">
                            I'm your personal AI advisor! I'll conduct a comprehensive assessment to discover your perfect academic trajectory in Türkiye.
                        </p>

                        <div class="sit-kvkk-welcome" style="max-width: 550px; margin: 0 auto 30px; text-align: left; background: rgba(0,0,0,0.03); border-radius: 12px; padding: 20px;">
                            <p style="font-size: 13px; color: #666; line-height: 1.6; margin: 0 0 12px;">
                                By starting this assessment, your name and responses will be processed to generate personalized program recommendations pursuant to the Turkish Personal Data Protection Law No. 6698 (KvKK).
                            </p>
                            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 14px; color: #333;">
                                <input type="checkbox" id="sit-kvkk-consent" style="margin-top: 3px; width: 18px; height: 18px; accent-color: #e20a17; flex-shrink: 0;">
                                <span>I have read and accept the <a href="/privacy-notice-data-processing-policy-kvkk-compliance/" target="_blank" style="color: #e20a17; text-decoration: underline;">KvKK Clarification Text</a> and consent to the processing of my personal data. <span style="color: #e20a17;">*</span></span>
                            </label>
                        </div>

                        <button class="sit-start-chat sit-btn sit-btn-primary" style="font-size: 18px; padding: 16px 32px;" disabled>
                            Start AI Assessment <span style="margin-left: 8px;">→</span>
                        </button>
                        <p class="sit-kvkk-hint" style="font-size: 12px; color: #999; margin-top: 10px;">Please accept the KvKK consent above to continue</p>
                    </div>
                </div>
            `;
            $('#sit-recommender').html(welcomeHtml);

            // Enable/disable start button based on KvKK consent
            $(document).on('change', '#sit-kvkk-consent', function() {
                const $btn = $('.sit-start-chat');
                const $hint = $('.sit-kvkk-hint');
                if (this.checked) {
                    $btn.prop('disabled', false);
                    $hint.fadeOut(200);
                } else {
                    $btn.prop('disabled', true);
                    $hint.fadeIn(200);
                }
            });
        },
        
        startChat: function(e) {
            if (e) e.preventDefault();
            this.sessionId = 'chat_session_' + Date.now();
            this.conversationHistory = [];
            this.currentStep = 0;
            this.userProfile = {};
            this.showChatScreen();
            this.startConversation();
        },

        restartChat: function(e) {
            if (e) e.preventDefault();
            this.startChat();
        },
        
        showChatScreen: function() {
            let dotsHtml = '';
            for(let i=0; i<this.totalQuestions; i++) {
                dotsHtml += `<span class="sit-dot ${i===0 ? 'active' : ''}"></span>`;
            }

            const chatHtml = `
                <div class="sit-chat-screen">
                    <div class="sit-chat-header">
                        <div class="sit-chat-title">
                            <div class="sit-chat-icon"><span style="font-size: 24px;">🤖</span></div>
                            AI Study Advisor
                        </div>
                        <div class="sit-progress-dots">
                            ${dotsHtml}
                        </div>
                    </div>
                    <div class="sit-chat-messages" id="sit-chat-messages"></div>
                    <div class="sit-chat-options" id="sit-chat-options"></div>
                </div>
            `;
            $('#sit-recommender').html(chatHtml);
        },
        
        startConversation: function() {
            this.trackUsage('assessment_started', {});
            this.addBotMessage("Hi there! 👋 I'm your AI advisor. Before we begin, what should I call you?");
            this.showNamePrompt();
        },
        
        showNamePrompt: function() {
            const nameHtml = `
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="text" id="sit-student-name" class="sit-input" placeholder="Enter your name..." />
                    <button id="sit-name-submit" class="sit-btn sit-btn-primary">Start</button>
                </div>
            `;
            $('#sit-chat-options').html(nameHtml);
            setTimeout(() => $('#sit-student-name').focus(), 100);
        },
        
        submitStudentName: function(e) {
            e.preventDefault();
            const name = ($('#sit-student-name').val() || '').trim();
            if (!name) {
                $('#sit-student-name').addClass('sit-input-error');
                return;
            }
            this.userProfile.name = name;
            this.addUserMessage(name);
            $('#sit-chat-options').empty();
            
            setTimeout(() => {
                this.addBotMessage(`Hi ${name}! 👋 I'm excited to help you discover your perfect study path.`);
                setTimeout(() => {
                    this.addBotMessage('I\'ll ask you a series of contextual questions to understand your academic goals. Let\'s start!');
                    this.showFirstQuestion();
                }, 1000);
            }, 500);
        },
        
        showFirstQuestion: function() {
            this.getNextQuestionFromAI();
        },
        
        getNextQuestionFromAI: function() {
            this.showTypingIndicator();
            
            const context = {
                student_name: this.userProfile.name,
                current_step: this.currentStep,
                conversation_history: this.conversationHistory,
                total_questions: this.totalQuestions
            };
            
            if (typeof sitRecommender !== 'undefined' && sitRecommender.openrouterEnabled) {
                $.ajax({
                    url: sitRecommender.apiUrl + 'chat/question',
                    method: 'POST',
                    headers: { 'X-WP-Nonce': sitRecommender.nonce },
                    data: { context: JSON.stringify(context) },
                    success: (response) => {
                        this.hideTypingIndicator();
                        if (response.success && response.data) {
                            this.displayAIQuestion(response.data);
                        } else {
                            this.showFallbackQuestion();
                        }
                    },
                    error: () => {
                        this.hideTypingIndicator();
                        this.showFallbackQuestion();
                    }
                });
            } else {
                this.hideTypingIndicator();
                this.showFallbackQuestion();
            }
        },
        
        displayAIQuestion: function(data) {
            if (data.question) {
                this.addBotMessage(data.question);
            }
            if (data.options && data.options.length > 0) {
                this.showChatOptions(data.options);
            } else {
                this.showTextInput();
            }
        },
        
        showFallbackQuestion: function() {
            const fallbacks = [
                {
                    question: "What type of subjects interest you most?",
                    options: [
                        { id: 'tech', text: '💻 Technology', description: 'Computers, AI, software' },
                        { id: 'biz', text: '💼 Business', description: 'Management, finance' },
                        { id: 'creative', text: '🎨 Creative', description: 'Design, arts, media' },
                        { id: 'sci', text: '🔬 Science', description: 'Biology, physics, research' }
                    ]
                },
                {
                    question: "How do you prefer to learn?",
                    options: [
                        { id: 'hands', text: '🔧 Hands-on', description: 'Labs, practical work' },
                        { id: 'theory', text: '📚 Theoretical', description: 'Deep study, research' }
                    ]
                }
            ];
            
            const q = fallbacks[Math.min(this.currentStep, fallbacks.length - 1)];
            this.addBotMessage(q.question);
            this.showChatOptions(q.options);
        },

        showTextInput: function() {
            const html = `
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="text" id="sit-text-answer" class="sit-input" placeholder="Type your answer..." />
                    <button id="sit-text-submit" class="sit-btn sit-btn-primary">Send</button>
                </div>
            `;
            $('#sit-chat-options').html(html);
            setTimeout(() => $('#sit-text-answer').focus(), 100);
        },

        submitTextAnswer: function(e) {
            e.preventDefault();
            const answer = ($('#sit-text-answer').val() || '').trim();
            if (!answer) {
                $('#sit-text-answer').addClass('sit-input-error');
                return;
            }
            this.processUserAnswer('text', answer, answer);
        },
        
        showChatOptions: function(options) {
            let html = '<div class="sit-chat-options-grid">';
            options.forEach(opt => {
                html += `
                    <div class="sit-chat-option" data-id="${opt.id}">
                        <div class="sit-option-main">${opt.text}</div>
                        ${opt.description ? `<div class="sit-option-desc">${opt.description}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
            $('#sit-chat-options').html(html);
        },
        
        selectChatOption: function(e) {
            const $opt = $(e.currentTarget);
            this.processUserAnswer($opt.data('id'), $opt.find('.sit-option-main').text(), $opt.find('.sit-option-main').text());
        },
        
        processUserAnswer: function(id, text, display) {
            this.addUserMessage(display);
            $('#sit-chat-options').empty();
            
            this.conversationHistory.push({
                step: this.currentStep,
                question_number: this.currentStep + 1,
                answer_id: id,
                answer_text: text,
                timestamp: new Date().toISOString()
            });
            
            this.currentStep++;
            this.updateProgress();
            
            if (this.currentStep >= this.totalQuestions) {
                this.showTypingIndicator();
                setTimeout(() => {
                    this.hideTypingIndicator();
                    this.addBotMessage("Excellent! 🎉 Analyzing your profile...");
                    this.generateRecommendations();
                }, 1000);
            } else {
                this.getNextQuestionFromAI();
            }
        },
        
        updateProgress: function() {
            $('.sit-dot').each(function(idx) {
                $(this).toggleClass('active', idx <= SITChatRecommender.currentStep);
            });
        },
        
        generateRecommendations: function() {
            this.showTypingIndicator();
            
            if (typeof sitRecommender !== 'undefined') {
                $.ajax({
                    url: sitRecommender.apiUrl + 'chat/recommend',
                    method: 'POST',
                    headers: { 'X-WP-Nonce': sitRecommender.nonce },
                    data: {
                        conversation_history: JSON.stringify(this.conversationHistory),
                        user_profile: JSON.stringify(this.userProfile),
                        student_name: this.userProfile.name,
                        total_questions: this.totalQuestions,
                        session_id: this.sessionId
                    },
                    success: (res) => {
                        this.hideTypingIndicator();
                        if (res.success && res.data) {
                            this.showAIRecommendations(res.data);
                        } else {
                            this.addBotMessage("Something went wrong generating your recommendations.");
                        }
                    },
                    error: () => {
                        this.hideTypingIndicator();
                        this.addBotMessage("Connection error while fetching recommendations.");
                    }
                });
            } else {
                this.hideTypingIndicator();
                this.addBotMessage("API not configured properly.");
            }
        },
        
        showAIRecommendations: function(aiData) {
            const name = this.userProfile.name || 'Student';
            
            $('#sit-recommender').fadeOut(300, function() {
                let html = `
                    <div class="sit-results-screen">
                        <div class="sit-results-header">
                            <h2>🎓 Your Academic Path</h2>
                            <p style="font-size: 18px; color: var(--sit-text-muted);">Here are the best programs for you, ${name}</p>
                        </div>
                        <div class="sit-recommendations">
                `;
                
                if (aiData.recommendations && aiData.recommendations.length > 0) {
                    aiData.recommendations.forEach((rec, idx) => {
                        const confidence = rec.confidence || (95 - (idx * 5));
                        
                        let programsHtml = '';
                        if (aiData.programs_by_field && aiData.programs_by_field[rec.field]) {
                            const progs = aiData.programs_by_field[rec.field].slice(0, 2);
                            progs.forEach(p => {
                                programsHtml += `<a href="${p.url}" target="_blank" class="sit-btn sit-btn-primary" style="padding: 8px 16px; font-size: 14px;">${p.name}</a>`;
                            });
                        }

                        let reasons = '';
                        if (rec.reasons && rec.reasons.length > 0) {
                            reasons = `
                                <div class="sit-rec-reasons">
                                    <strong>Why it fits:</strong>
                                    <ul>${rec.reasons.map(r => `<li>${r}</li>`).join('')}</ul>
                                </div>
                            `;
                        }

                        html += `
                            <div class="sit-recommendation-card">
                                <div class="sit-rec-header">
                                    <h3>${rec.field}</h3>
                                    <span class="sit-match-percentage">${confidence}% Match</span>
                                </div>
                                <div class="sit-rec-content">
                                    <p style="margin-bottom: 20px;">${rec.why_good_fit || rec.explanation}</p>
                                    ${reasons}
                                    ${programsHtml ? `<div class="sit-program-links">${programsHtml}</div>` : ''}
                                </div>
                            </div>
                        `;
                    });
                }
                
                html += `
                        </div>
                        <div style="text-align: center; margin-top: 40px;">
                            <button class="sit-restart-chat sit-btn sit-btn-secondary">Start Over</button>
                        </div>
                    </div>
                `;
                
                $(this).html(html).fadeIn(300);
            });
        },
        
        addBotMessage: function(msg) {
            const html = `<div class="sit-chat-bubble sit-bot-message">
                <div class="sit-avatar">🤖</div>
                <div class="sit-message">${msg}</div>
            </div>`;
            $('#sit-chat-messages').append(html);
            this.scrollToBottom();
        },
        
        addUserMessage: function(msg) {
            const html = `<div class="sit-chat-bubble sit-user-message">
                <div class="sit-avatar">U</div>
                <div class="sit-message">${msg}</div>
            </div>`;
            $('#sit-chat-messages').append(html);
            this.scrollToBottom();
        },
        
        showTypingIndicator: function() {
            if ($('#sit-typing').length) return;
            const html = `<div class="sit-chat-bubble sit-bot-message" id="sit-typing">
                <div class="sit-avatar">🤖</div>
                <div class="sit-typing-indicator"><span></span><span></span><span></span></div>
            </div>`;
            $('#sit-chat-messages').append(html);
            this.scrollToBottom();
        },
        
        hideTypingIndicator: function() {
            $('#sit-typing').remove();
        },
        
        scrollToBottom: function() {
            const $msgs = $('#sit-chat-messages');
            if ($msgs.length) {
                $msgs.scrollTop($msgs[0].scrollHeight);
            }
        },
        
        trackUsage: function(action, data) {
            if (typeof sitRecommender !== 'undefined' && sitRecommender.trackStats) {
                $.post(sitRecommender.ajaxUrl, {
                    action: 'sit_track_usage',
                    action_type: action,
                    data: JSON.stringify(data),
                    nonce: sitRecommender.nonce
                });
            }
        }
    };
    
    // Initialize
    if ($('#sit-recommender').length) {
        SITChatRecommender.init();
    }
});
