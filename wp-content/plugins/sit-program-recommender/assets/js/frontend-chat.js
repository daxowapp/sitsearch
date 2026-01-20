jQuery(document).ready(function($) {
    'use strict';
    
    const SITChatRecommender = {
        sessionId: null,
        conversationHistory: [],
        currentStep: 0,
        userProfile: {},
        isTyping: false,
        
        init: function() {
            this.bindEvents();
            this.showWelcomeScreen();
        },
        
        bindEvents: function() {
            $(document).on('click', '.sit-start-chat', this.startChat.bind(this));
            $(document).on('click', '.sit-chat-option', this.selectChatOption.bind(this));
            $(document).on('click', '.sit-restart-chat', this.restartChat.bind(this));
            console.log('SIT Chat Recommender: Event bindings initialized');
        },
        
        showWelcomeScreen: function() {
            const welcomeHtml = `
                <div class="sit-welcome-screen">
                    <div class="sit-welcome-content">
                        <h2>🤖 AI Study Path Advisor</h2>
                        <p>I'm your personal AI advisor! I'll have a conversation with you to understand your interests and recommend the perfect study programs.</p>
                        
                        <div class="sit-chat-preview">
                            <div class="sit-chat-bubble sit-bot-message">
                                <div class="sit-avatar">🤖</div>
                                <div class="sit-message">
                                    Hi! I'm here to help you find your ideal study path. Ready to start our conversation?
                                </div>
                            </div>
                        </div>
                        
                        <div class="sit-welcome-actions">
                            <button class="sit-start-chat sit-btn sit-btn-primary sit-btn-large">
                                <span class="sit-btn-icon">💬</span>
                                <span class="sit-btn-text">Start Conversation</span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(welcomeHtml);
        },
        
        startChat: function(e) {
            e.preventDefault();
            
            this.sessionId = 'chat_session_' + Date.now();
            this.conversationHistory = [];
            this.currentStep = 0;
            this.userProfile = {};
            
            this.showChatScreen();
            this.startConversation();
        
        showChatScreen: function() {
            const chatHtml = `
                <div class="sit-chat-screen">
                    <div class="sit-chat-header">
                        <div class="sit-chat-title">
                            <span class="sit-chat-icon">🤖</span>
                            <span>AI Study Advisor</span>
                        </div>
                        <div class="sit-chat-progress">
                            <div class="sit-progress-dots">
                                <span class="sit-dot active"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                            </div>
                        </div>
                        
                        <div class="sit-chat-options" id="sit-chat-options">
                            <!-- Options will be added here -->
                        </div>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(chatHtml);
        },
        
        startConversation: function() {
            // Add welcome message
            this.addBotMessage("Hi there! 👋 I'm excited to help you discover your perfect study path!");
            
            setTimeout(() => {
                this.addBotMessage("Let's start with something simple - what interests you most in general?");
                this.showFirstQuestion();
            }, 1500);
        },
        
        showFirstQuestion: function() {
            const options = [
                { id: 'technology', text: '💻 Technology & Innovation', description: 'Computers, AI, software, digital world' },
                { id: 'business', text: '💼 Business & Money', description: 'Entrepreneurship, finance, management' },
                { id: 'people', text: '🤝 Helping People', description: 'Healthcare, education, social work' },
                { id: 'creative', text: '🎨 Creative & Arts', description: 'Design, music, writing, visual arts' },
                { id: 'science', text: '🔬 Science & Research', description: 'Discovery, experiments, understanding nature' }
            ];
            
            this.showChatOptions(options);
        },
        
        showChatOptions: function(options) {
            let optionsHtml = '<div class="sit-chat-options-grid">';
            
            options.forEach(option => {
                optionsHtml += `
                    <div class="sit-chat-option" data-option-id="${option.id}">
                        <div class="sit-option-main">${option.text}</div>
                        <div class="sit-option-desc">${option.description}</div>
                    </div>
                `;
            });
            
            optionsHtml += '</div>';
            $('#sit-chat-options').html(optionsHtml);
        },
        
        selectChatOption: function(e) {
            const $option = $(e.currentTarget);
            const optionId = $option.data('option-id');
            const optionText = $option.find('.sit-option-main').text();
            
            // Add user message
            this.addUserMessage(optionText);
            
            // Clear options
            $('#sit-chat-options').empty();
            
            // Store user choice
            this.userProfile.currentInterest = optionId;
            this.conversationHistory.push({
                step: this.currentStep,
                question: this.currentStep === 0 ? 'general_interest' : 'follow_up',
                answer: optionId,
                text: optionText,
                user_choice: optionText
            });
            
            // Check if this is the final step (after 10+ questions)
            if (this.currentStep >= 9) {
                this.showTypingIndicator();
                setTimeout(() => {
                    this.hideTypingIndicator();
                    this.addBotMessage("Excellent! 🎉 I have everything I need to find your perfect study programs. Let me analyze your responses and generate personalized recommendations just for you!");
                    this.generateRecommendations();
                }, 1500);
                return;
            }
            
            // Generate AI follow-up
            this.generateFollowUp(optionId, optionText);
        },
        
        generateFollowUp: function(optionId, optionText) {
            this.showTypingIndicator();
            
            // Call AI endpoint for dynamic response
            console.log('Calling AI endpoint with:', {
                message: optionText,
                conversation_history: this.conversationHistory,
                current_step: this.currentStep,
                url: sitRecommender.apiUrl + 'chat/message'
            });
            
            $.ajax({
                url: sitRecommender.apiUrl + 'chat/message',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': sitRecommender.nonce
                },
                data: {
                    message: optionText,
                    conversation_history: this.conversationHistory,
                    current_step: this.currentStep
                },
                success: (response) => {
                    console.log('AI endpoint success response:', response);
                    this.hideTypingIndicator();
                    if (response.success) {
                        this.addBotMessage(response.response);
                        
                        if (response.is_final) {
                            // Generate final recommendations
                            this.generateRecommendations();
                        } else {
                            // Show next options
                            this.showChatOptions(response.options);
                            this.currentStep = response.next_step;
                            this.updateProgress();
                        }
                    } else {
                        console.error('AI endpoint returned error:', response);
                        this.addBotMessage("I'm sorry, I encountered an issue. Let me try a different approach.");
                        this.showGenericFollowUp(); // Use generic fallback
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AI endpoint AJAX error:', {xhr, status, error});
                    console.error('Response text:', xhr.responseText);
                    this.hideTypingIndicator();
                    this.addBotMessage("I'm having some technical difficulties. Let me continue with what I know.");
                    this.showGenericFollowUp(); // Use generic fallback
                }
            });
        },
        
        processFollowUp: function(optionId, optionText) {
            this.currentStep++;
            this.updateProgress();
            
            let response, options;
            
            // If no optionId provided, use generic fallback
            if (!optionId) {
                this.showGenericFollowUp();
                return;
            }
            
            switch(optionId) {
                case 'technology':
                    response = `Ah, I see you're drawn to technology! 🚀 That's fascinating - technology is shaping our future in incredible ways.`;
                    setTimeout(() => {
                        this.addBotMessage("What aspect of technology excites you most?");
                        options = [
                            { id: 'programming', text: '⌨️ Programming & Software', description: 'Creating apps, websites, systems' },
                            { id: 'ai', text: '🤖 Artificial Intelligence', description: 'Machine learning, robotics, automation' },
                            { id: 'hardware', text: '🔧 Hardware & Engineering', description: 'Electronics, circuits, devices' },
                            { id: 'data', text: '📊 Data & Analytics', description: 'Big data, statistics, insights' },
                            { id: 'cybersecurity', text: '🛡️ Cybersecurity', description: 'Protecting systems, ethical hacking' },
                            { id: 'games', text: '🎮 Game Development', description: 'Creating games, interactive media' }
                        ];
                        this.showChatOptions(options);
                    }, 1500);
                    break;
                    
                case 'business':
                    response = `Excellent choice! 💼 Business and entrepreneurship drive innovation and create opportunities.`;
                    setTimeout(() => {
                        this.addBotMessage("Which area of business appeals to you most?");
                        options = [
                            { id: 'management', text: '👔 Management & Leadership', description: 'Leading teams, strategic planning' },
                            { id: 'finance', text: '💰 Finance & Investment', description: 'Banking, markets, financial analysis' },
                            { id: 'marketing', text: '📢 Marketing & Sales', description: 'Branding, advertising, customer relations' },
                            { id: 'entrepreneurship', text: '🚀 Entrepreneurship', description: 'Starting businesses, innovation' },
                            { id: 'international', text: '🌍 International Business', description: 'Global trade, cross-cultural business' },
                            { id: 'hr', text: '👥 Human Resources', description: 'People management, organizational culture' }
                        ];
                        this.showChatOptions(options);
                    }, 1500);
                    break;
                    
                case 'people':
                    response = `How wonderful! 🤗 Helping others is one of the most rewarding career paths you can choose.`;
                    setTimeout(() => {
                        this.addBotMessage("How would you like to make a difference in people's lives?");
                        options = [
                            { id: 'healthcare', text: '🏥 Healthcare & Medicine', description: 'Treating patients, medical research' },
                            { id: 'education', text: '📚 Education & Teaching', description: 'Inspiring students, sharing knowledge' },
                            { id: 'psychology', text: '🧠 Psychology & Counseling', description: 'Mental health, therapy, understanding behavior' },
                            { id: 'social', text: '🤲 Social Work', description: 'Community support, advocacy, social justice' },
                            { id: 'nursing', text: '👩‍⚕️ Nursing & Care', description: 'Patient care, health support' },
                            { id: 'therapy', text: '🏃‍♂️ Physical Therapy', description: 'Rehabilitation, movement, recovery' }
                        ];
                        this.showChatOptions(options);
                    }, 1500);
                    break;
                    
                case 'creative':
                    response = `Amazing! 🎨 Creativity is the soul of innovation and brings beauty to the world.`;
                    setTimeout(() => {
                        this.addBotMessage("What type of creative expression calls to you?");
                        options = [
                            { id: 'design', text: '🎨 Visual Design', description: 'Graphic design, UI/UX, branding' },
                            { id: 'media', text: '🎬 Media & Film', description: 'Video production, photography, cinema' },
                            { id: 'fashion', text: '👗 Fashion & Style', description: 'Clothing design, fashion industry' },
                            { id: 'architecture', text: '🏛️ Architecture', description: 'Building design, urban planning' },
                            { id: 'music', text: '🎵 Music & Audio', description: 'Composition, performance, sound design' },
                            { id: 'writing', text: '✍️ Writing & Literature', description: 'Journalism, creative writing, communication' }
                        ];
                        this.showChatOptions(options);
                    }, 1500);
                    break;
                    
                case 'science':
                    response = `Fantastic! 🔬 Science is about discovering the mysteries of our universe and improving life.`;
                    setTimeout(() => {
                        this.addBotMessage("Which scientific field sparks your curiosity?");
                        options = [
                            { id: 'biology', text: '🧬 Biology & Life Sciences', description: 'Living organisms, genetics, ecology' },
                            { id: 'chemistry', text: '⚗️ Chemistry', description: 'Molecules, reactions, materials' },
                            { id: 'physics', text: '🌌 Physics & Astronomy', description: 'Universe, energy, fundamental forces' },
                            { id: 'environmental', text: '🌱 Environmental Science', description: 'Climate, sustainability, conservation' },
                            { id: 'medical', text: '🔬 Medical Research', description: 'Disease, treatments, biomedical science' },
                            { id: 'engineering', text: '⚙️ Engineering Sciences', description: 'Applied science, problem-solving, innovation' }
                        ];
                        this.showChatOptions(options);
                    }, 1500);
                    break;
            }
            
            this.addBotMessage(response);
        },
        
        showGenericFollowUp: function() {
            setTimeout(() => {
                this.addBotMessage("Let me ask you something to help narrow down the perfect programs for you.");
                const options = [
                    { id: 'practical', text: '🔧 Hands-on & Practical', description: 'Learning by doing, labs, projects' },
                    { id: 'theoretical', text: '📚 Research & Theory', description: 'Deep study, analysis, concepts' },
                    { id: 'creative', text: '🎨 Creative & Innovative', description: 'Design, creativity, new ideas' },
                    { id: 'leadership', text: '👥 Leadership & Management', description: 'Leading teams, organizing, planning' }
                ];
                this.showChatOptions(options);
            }, 1000);
        },
        
        addBotMessage: function(message) {
            const messageHtml = `
                <div class="sit-chat-bubble sit-bot-message">
                    <div class="sit-avatar">🤖</div>
                    <div class="sit-message">${message}</div>
                </div>
            `;
            
            $('#sit-chat-messages').append(messageHtml);
            this.scrollToBottom();
        },
        
        addUserMessage: function(message) {
            const messageHtml = `
                <div class="sit-chat-bubble sit-user-message">
                    <div class="sit-message">${message}</div>
                    <div class="sit-avatar">👤</div>
                </div>
            `;
            
            $('#sit-chat-messages').append(messageHtml);
            this.scrollToBottom();
        },
        
        showTypingIndicator: function() {
            const typingHtml = `
                <div class="sit-chat-bubble sit-bot-message sit-typing" id="sit-typing-indicator">
                    <div class="sit-avatar">🤖</div>
                    <div class="sit-message">
                        <div class="sit-typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            `;
            
            $('#sit-chat-messages').append(typingHtml);
            this.scrollToBottom();
            this.isTyping = true;
        },
        
        hideTypingIndicator: function() {
            $('#sit-typing-indicator').remove();
            this.isTyping = false;
        },
        
        updateProgress: function() {
            const totalSteps = 5;
            const progress = Math.min(this.currentStep, totalSteps);
            
            $('.sit-progress-dots .sit-dot').each(function(index) {
                if (index < progress) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        },
        
        generateRecommendations: function() {
            this.showTypingIndicator();
            
            setTimeout(() => {
                this.addBotMessage("Perfect! 🎉 Let me analyze everything we've discussed and find the ideal study programs for you...");
                
                setTimeout(() => {
                    // Call recommendation endpoint
                    $.ajax({
                        url: sitRecommender.apiUrl + 'chat/recommend',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': sitRecommender.nonce
                        },
                        data: {
                            conversation_history: this.conversationHistory,
                            user_profile: this.userProfile
                        },
                        success: (response) => {
                            this.hideTypingIndicator();
                            if (response.success) {
                                this.showRecommendations(response);
                            } else {
                                this.addBotMessage("I apologize, but I'm having trouble generating your recommendations right now. Please try again later.");
                            }
                        },
                        error: () => {
                            this.hideTypingIndicator();
                            this.addBotMessage("I'm experiencing technical difficulties. Please refresh the page and try again.");
                        }
                    });
                }, 2000);
            }, 1000);
        },
        
        showRecommendations: function(response) {
            // Clear chat and show results
            $('.sit-chat-container').fadeOut(300, () => {
                this.displayResults(response);
            });
        },
        
        displayResults: function(response) {
            // Use the same results display logic from the original system
            let resultsHtml = `
                <div class="sit-results-screen">
                    <div class="sit-results-header">
                        <h2>🎯 Your AI-Powered Study Recommendations</h2>
                        <p>Based on our conversation, here are the perfect study programs for you:</p>
                    </div>
                    
                    <div class="sit-recommendations">
            `;
            
            response.recommendations.forEach((rec, index) => {
                const confidenceClass = rec.confidence >= 80 ? 'high' : rec.confidence >= 60 ? 'medium' : 'low';
                const studyAreas = response.programs_by_field[rec.field] || [];
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
                        <button class="sit-restart-chat sit-btn sit-btn-secondary">
                            🔄 Start New Conversation
                        </button>
                        <button class="sit-btn sit-btn-primary" onclick="window.open('http://search.studyinturkiye.com/study-areas', '_blank')">
                            🔍 Browse All Programs
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(resultsHtml);
        },
        
        scrollToBottom: function() {
            const chatMessages = $('#sit-chat-messages');
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        },
        
        restartChat: function(e) {
            e.preventDefault();
            this.init();
        },
        
        showError: function(message) {
            const errorHtml = `
                <div class="sit-error-screen">
                    <div class="sit-error-content">
                        <div class="sit-error-icon">⚠️</div>
                        <h3>Oops! Something went wrong</h3>
                        <p>${message}</p>
                        <button class="sit-restart-chat sit-btn sit-btn-primary">
                            Try Again
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(errorHtml);
        }
    };
    
    // Initialize the chat recommender
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('SIT container found:', $('.sit-recommender-container').length);
    
    if ($('.sit-recommender-container').length) {
        console.log('Initializing SIT Chat Recommender...');
        SITChatRecommender.init();
    } else {
        console.error('SIT Recommender container not found!');
    }
});
