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
            $(document).on('click', '.sit-start-quiz', this.startChat.bind(this));
            $(document).on('click', '.sit-chat-option', this.selectChatOption.bind(this));
            $(document).on('click', '.sit-restart-chat', this.restartChat.bind(this));
            $(document).on('click', '#sit-name-submit', this.submitStudentName.bind(this));
            $(document).on('click', '.sit-browse-programs', function(e) {
                e.preventDefault();
                window.open('http://search.studyinturkiye.com/study-areas', '_blank');
            });
            $(document).on('keypress', '#sit-student-name', function(e) {
                if (e.which === 13) {
                    SITChatRecommender.submitStudentName(e);
                }
            });
            console.log('SIT Chat Recommender: Event bindings initialized');
        },
        
        showWelcomeScreen: function() {
            var welcomeHtml = '<div class="sit-welcome-screen">' +
                '<div class="sit-welcome-content">' +
                '<h2><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 32px; height: 32px; vertical-align: middle; margin-right: 8px;"> AI Study Path Advisor</h2>' +
                '<p>I\'m your personal AI advisor! I\'ll have a comprehensive conversation with you (10+ questions) to deeply understand your interests, skills, and goals to recommend the perfect academic programs.</p>' +
                '<div class="sit-chat-preview">' +
                '<div class="sit-chat-bubble sit-bot-message">' +
                '<div class="sit-avatar"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 24px; height: 24px; border-radius: 50%;"></div>' +
                '<div class="sit-message">Hi! I\'m here to conduct a thorough assessment to find your ideal study path. We\'ll explore your interests, strengths, values, and career goals through meaningful questions. Ready to start our conversation?</div>' +
                '</div>' +
                '</div>' +
                '<div class="sit-welcome-actions">' +
                '<button class="sit-start-chat sit-btn sit-btn-primary sit-btn-large">' +
                '<span class="sit-btn-icon">💬</span>' +
                '<span class="sit-btn-text">Start Comprehensive Assessment</span>' +
                '</button>' +
                '</div>' +
                '</div>' +
                '</div>';
            
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
        },
        
        showChatScreen: function() {
            var chatHtml = '<div class="sit-chat-screen">' +
                '<div class="sit-chat-header">' +
                '<div class="sit-chat-title">' +
                '<span class="sit-chat-icon"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 24px; height: 24px; vertical-align: middle;"></span>' +
                '<span>AI Study Advisor - Comprehensive Assessment</span>' +
                '</div>' +
                '<div class="sit-chat-progress">' +
                '<div class="sit-progress-dots">' +
                '<span class="sit-dot active"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '<span class="sit-dot"></span>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="sit-chat-container">' +
                '<div class="sit-chat-messages" id="sit-chat-messages"></div>' +
                '<div class="sit-chat-options" id="sit-chat-options"></div>' +
                '</div>' +
                '</div>';
            
            $('.sit-recommender-container').html(chatHtml);
        },
        
        startConversation: function() {
            this.trackUsage('assessment_started', {});
            
            // Debug OpenAI status
            this.debugOpenAIStatus();
            
            this.addBotMessage("Hi there! 👋 I'm your AI advisor. Before we begin, what should I call you?");
            this.showNamePrompt();
        },
        
        debugOpenAIStatus: function() {
            console.log('=== SIT OpenAI Debug Status ===');
            console.log('sitRecommender object:', typeof sitRecommender !== 'undefined' ? 'LOADED' : 'NOT LOADED');
            
            if (typeof sitRecommender !== 'undefined') {
                console.log('API URL:', sitRecommender.apiUrl);
                console.log('OpenAI Enabled:', sitRecommender.openaiEnabled);
                console.log('Nonce:', sitRecommender.nonce ? 'Present' : 'Missing');
                
                // Add visible debug message in chat
                if (sitRecommender.openaiEnabled) {
                    this.addBotMessage("🤖 <strong>OpenAI Status:</strong> ✅ ACTIVE - Questions will be generated by AI based on your responses!");
                    
                    // Add test button for OpenAI endpoint
                    var testHtml = '<div style="margin: 10px 0;">' +
                        '<button id="sit-test-openai" class="sit-btn sit-btn-secondary" style="font-size: 0.9em;">🔧 Test OpenAI Connection</button>' +
                        '</div>';
                    $('#sit-chat-options').html(testHtml);
                    
                    var self = this;
                    $('#sit-test-openai').on('click', function() {
                        self.testOpenAIConnection();
                    });
                } else {
                    this.addBotMessage("⚠️ <strong>OpenAI Status:</strong> ❌ NOT CONFIGURED - Using fallback questions. Add your OpenAI API key in admin settings for AI-powered questions.");
                }
            } else {
                console.log('sitRecommender object is completely missing');
                this.addBotMessage("🚨 <strong>System Status:</strong> Configuration error - sitRecommender not loaded. Using basic fallback mode.");
            }
            console.log('================================');
        },
        
        testOpenAIConnection: function() {
            var self = this;
            this.addBotMessage("🔧 Testing OpenAI connection...");
            
            var testContext = {
                student_name: 'Test Student',
                current_step: 0,
                conversation_history: [],
                total_questions: 10
            };
            
            $.ajax({
                url: sitRecommender.apiUrl + 'chat/question',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': sitRecommender.nonce
                },
                data: {
                    context: JSON.stringify(testContext)
                },
                success: function(response) {
                    console.log('SIT: Test OpenAI Response:', response);
                    if (response.success && response.data) {
                        self.addBotMessage("✅ <strong>OpenAI Test:</strong> SUCCESS! Connection is working properly.");
                        self.addBotMessage("📝 <strong>Test Question:</strong> " + response.data.question);
                    } else {
                        self.addBotMessage("❌ <strong>OpenAI Test:</strong> API responded but with error: " + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('SIT: Test OpenAI Error:', {xhr: xhr, status: status, error: error});
                    var errorMsg = 'Connection failed: ';
                    if (xhr.status === 404) {
                        errorMsg += 'REST API endpoint not found (404)';
                    } else if (xhr.status === 403) {
                        errorMsg += 'Permission denied (403)';
                    } else if (xhr.status === 500) {
                        errorMsg += 'Server error (500) - Check OpenAI API key';
                    } else {
                        errorMsg += 'HTTP ' + xhr.status + ' - ' + error;
                    }
                    self.addBotMessage("❌ <strong>OpenAI Test:</strong> " + errorMsg);
                }
            });
        },
        
        trackUsage: function(action, data) {
            if (typeof sitRecommender !== 'undefined' && sitRecommender.trackStats) {
                $.post(sitRecommender.ajaxUrl, {
                    action: 'sit_track_usage',
                    action_type: action,
                    data: JSON.stringify(data),
                    nonce: sitRecommender.nonce
                }).fail(function() {
                    console.log('Statistics tracking failed');
                });
            }
        },
        
        showNamePrompt: function() {
            var nameHtml = '<div class="sit-name-capture" id="sit-name-capture" style="display: flex; gap: 10px; align-items: center;">' +
                '<input type="text" id="sit-student-name" class="sit-input" placeholder="Enter your name" style="flex: 1;" />' +
                '<button id="sit-name-submit" class="sit-btn sit-btn-primary">Continue</button>' +
                '</div>';
            $('#sit-chat-options').html(nameHtml);
            $('#sit-student-name').focus();
        },
        
        submitStudentName: function(e) {
            e.preventDefault();
            var name = ($('#sit-student-name').val() || '').trim();
            if (!name) {
                $('#sit-student-name').addClass('sit-input-error');
                return;
            }
            this.userProfile.name = name;
            this.addUserMessage(name);
            $('#sit-chat-options').empty();
            
            // Personalized greeting and intro
            var self = this;
            setTimeout(function() {
                self.addBotMessage('Hi ' + name + '! 👋 I\'m excited to help you discover your perfect study path through a comprehensive assessment!');
                setTimeout(function() {
                    self.addBotMessage('I\'ll ask you about 10 meaningful questions to understand your interests, strengths, values, and career goals. This will help me give you the most accurate recommendations possible, ' + name + '.');
                    setTimeout(function() {
                        self.addBotMessage('Let\'s start with your core interests, ' + name + ' — what draws you in most?');
                        self.showFirstQuestion();
                    }, 1200);
                }, 900);
            }, 400);
        },
        
        showFirstQuestion: function() {
            // Get first question from OpenAI
            this.getNextQuestionFromAI();
        },
        
        getNextQuestionFromAI: function() {
            var self = this;
            this.showTypingIndicator();
            
            // Prepare conversation context for OpenAI
            var conversationContext = {
                student_name: this.userProfile.name,
                current_step: this.currentStep,
                conversation_history: this.conversationHistory,
                total_questions: 10
            };
            
            console.log('SIT: Attempting to get question from OpenAI, step:', this.currentStep);
            console.log('SIT: sitRecommender available:', typeof sitRecommender !== 'undefined');
            console.log('SIT: OpenAI enabled:', sitRecommender ? sitRecommender.openaiEnabled : 'N/A');
            console.log('SIT: API URL:', sitRecommender ? sitRecommender.apiUrl : 'N/A');
            console.log('SIT: Conversation context:', conversationContext);
            
            // Call WordPress REST API to get question from OpenAI
            if (typeof sitRecommender !== 'undefined' && sitRecommender.openaiEnabled) {
                console.log('SIT: Making AJAX call to OpenAI endpoint');
                $.ajax({
                    url: sitRecommender.apiUrl + 'chat/question',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': sitRecommender.nonce
                    },
                    data: {
                        context: JSON.stringify(conversationContext)
                    },
                    success: function(response) {
                        console.log('SIT: OpenAI API Response:', response);
                        self.hideTypingIndicator();
                        if (response.success && response.data) {
                            console.log('SIT: Using OpenAI generated question');
                            console.log('SIT: Question data:', response.data);
                            self.displayAIQuestion(response.data);
                        } else {
                            console.log('SIT: OpenAI response failed, using fallback');
                            console.log('SIT: Response details:', response);
                            self.addBotMessage('I apologize, but I\'m having trouble generating the next question. Let me try a different approach.');
                            self.showFallbackQuestion();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('SIT: AI Question API Error:', {xhr: xhr, status: status, error: error});
                        console.log('SIT: Response text:', xhr.responseText);
                        console.log('SIT: Response status:', xhr.status);
                        console.log('SIT: Response headers:', xhr.getAllResponseHeaders());
                        
                        self.hideTypingIndicator();
                        
                        // Add detailed error message in chat
                        var errorMsg = 'OpenAI API call failed: ';
                        if (xhr.status === 404) {
                            errorMsg += 'Endpoint not found (404). Check if REST API routes are registered.';
                        } else if (xhr.status === 403) {
                            errorMsg += 'Permission denied (403). Check nonce and permissions.';
                        } else if (xhr.status === 500) {
                            errorMsg += 'Server error (500). Check OpenAI API key and server logs.';
                        } else if (xhr.status === 0) {
                            errorMsg += 'Network error. Check if WordPress site is accessible.';
                        } else {
                            errorMsg += 'HTTP ' + xhr.status + ' - ' + error;
                        }
                        
                        self.addBotMessage('🚨 <strong>OpenAI Error:</strong> ' + errorMsg);
                        self.addBotMessage('Switching to fallback questions...');
                        self.showFallbackQuestion();
                    }
                });
            } else {
                // Fallback when API is not available or OpenAI not enabled
                console.log('SIT: OpenAI not available, using fallback questions');
                this.hideTypingIndicator();
                if (typeof sitRecommender === 'undefined') {
                    this.addBotMessage('Configuration error: sitRecommender not loaded. Using fallback questions.');
                } else if (!sitRecommender.openaiEnabled) {
                    this.addBotMessage('OpenAI is not configured. Please add your API key in the admin settings. Using fallback questions for now.');
                }
                this.showFallbackQuestion();
            }
        },
        
        displayAIQuestion: function(questionData) {
            // Add debug indicator for AI-generated questions
            this.addBotMessage("🤖 <em style='color: #666; font-size: 0.9em;'>[AI-Generated Question " + (this.currentStep + 1) + "/10]</em>");
            
            // Display the question from OpenAI
            if (questionData.question) {
                this.addBotMessage(questionData.question);
            }
            
            // Display the options from OpenAI
            if (questionData.options && questionData.options.length > 0) {
                this.showChatOptions(questionData.options);
            } else {
                // If no options provided, show text input
                this.showTextInput();
            }
        },
        
        showFallbackQuestion: function() {
            // Fallback questions when OpenAI is not available - ensure variety
            var fallbackQuestions = [
                {
                    question: "What type of subjects interest you most?",
                    options: [
                        { id: 'technology', text: '💻 Technology & Innovation', description: 'Computers, AI, software, digital world' },
                        { id: 'business', text: '💼 Business & Money', description: 'Entrepreneurship, finance, management' },
                        { id: 'people', text: '🤝 Helping People', description: 'Healthcare, education, social work' },
                        { id: 'creative', text: '🎨 Creative & Arts', description: 'Design, music, writing, visual arts' },
                        { id: 'science', text: '🔬 Science & Research', description: 'Discovery, experiments, understanding nature' }
                    ]
                },
                {
                    question: "How do you prefer to learn?",
                    options: [
                        { id: 'hands_on', text: '🔧 Hands-on Learning', description: 'Labs, experiments, practical work' },
                        { id: 'theoretical', text: '📚 Theoretical Study', description: 'Research, analysis, deep thinking' },
                        { id: 'collaborative', text: '👥 Group Projects', description: 'Teamwork and collaboration' },
                        { id: 'independent', text: '🎯 Independent Study', description: 'Self-directed learning' }
                    ]
                },
                {
                    question: "What motivates you most in your studies?",
                    options: [
                        { id: 'problem_solving', text: '🧩 Solving Complex Problems', description: 'Analytical thinking and solutions' },
                        { id: 'creativity', text: '🎨 Creative Expression', description: 'Innovation and artistic work' },
                        { id: 'helping_others', text: '❤️ Making a Difference', description: 'Positive impact on society' },
                        { id: 'financial_success', text: '💰 Career Success', description: 'Professional growth and income' }
                    ]
                },
                {
                    question: "Which work environment appeals to you?",
                    options: [
                        { id: 'office', text: '🏢 Corporate Office', description: 'Structured business environment' },
                        { id: 'laboratory', text: '🔬 Research Lab', description: 'Scientific research setting' },
                        { id: 'field_work', text: '🌍 Field Work', description: 'Outdoor or on-site work' },
                        { id: 'remote', text: '💻 Remote/Flexible', description: 'Work from anywhere setup' }
                    ]
                },
                {
                    question: "What's your preferred pace of work?",
                    options: [
                        { id: 'fast_paced', text: '⚡ Fast-Paced', description: 'Quick decisions and rapid changes' },
                        { id: 'steady', text: '📈 Steady Progress', description: 'Consistent and methodical approach' },
                        { id: 'project_based', text: '🎯 Project-Based', description: 'Intensive bursts with breaks' },
                        { id: 'flexible', text: '🔄 Flexible Schedule', description: 'Adaptable timing and rhythm' }
                    ]
                },
                {
                    question: "Which skills do you want to develop most?",
                    options: [
                        { id: 'technical', text: '⚙️ Technical Skills', description: 'Programming, engineering, tools' },
                        { id: 'communication', text: '🗣️ Communication', description: 'Speaking, writing, presentation' },
                        { id: 'leadership', text: '👑 Leadership', description: 'Managing teams and projects' },
                        { id: 'analytical', text: '📊 Analytical Thinking', description: 'Data analysis and research' }
                    ]
                },
                {
                    question: "What type of impact do you want to make?",
                    options: [
                        { id: 'global', text: '🌍 Global Impact', description: 'Worldwide influence and change' },
                        { id: 'local', text: '🏘️ Community Impact', description: 'Local community improvement' },
                        { id: 'industry', text: '🏭 Industry Innovation', description: 'Advancing specific fields' },
                        { id: 'personal', text: '👤 Individual Growth', description: 'Personal development focus' }
                    ]
                },
                {
                    question: "How important is work-life balance to you?",
                    options: [
                        { id: 'very_important', text: '⚖️ Very Important', description: 'Strong separation of work and life' },
                        { id: 'somewhat', text: '📝 Somewhat Important', description: 'Balanced but flexible approach' },
                        { id: 'career_focused', text: '🎯 Career Focused', description: 'Willing to prioritize career growth' },
                        { id: 'integrated', text: '🔄 Integrated Approach', description: 'Work and life blend naturally' }
                    ]
                },
                {
                    question: "What drives your curiosity most?",
                    options: [
                        { id: 'how_things_work', text: '🔧 How Things Work', description: 'Understanding mechanisms and systems' },
                        { id: 'human_behavior', text: '🧠 Human Behavior', description: 'Psychology and social dynamics' },
                        { id: 'future_trends', text: '🚀 Future Possibilities', description: 'Emerging technologies and trends' },
                        { id: 'cultural_diversity', text: '🌏 Cultural Understanding', description: 'Different perspectives and societies' }
                    ]
                },
                {
                    question: "What's your ideal learning outcome?",
                    options: [
                        { id: 'expertise', text: '🎓 Deep Expertise', description: 'Becoming a specialist in one area' },
                        { id: 'versatility', text: '🔄 Broad Knowledge', description: 'Understanding multiple disciplines' },
                        { id: 'practical_skills', text: '🛠️ Practical Skills', description: 'Immediately applicable abilities' },
                        { id: 'research_ability', text: '🔍 Research Skills', description: 'Ability to discover new knowledge' }
                    ]
                }
            ];
            
            // Ensure we don't exceed available questions
            var questionIndex = Math.min(this.currentStep, fallbackQuestions.length - 1);
            var question = fallbackQuestions[questionIndex];
            
            console.log('SIT: Using fallback question', questionIndex + 1, 'for step', this.currentStep);
            
            // Add debug indicator for fallback questions
            this.addBotMessage("📝 <em style='color: #666; font-size: 0.9em;'>[Fallback Question " + (this.currentStep + 1) + "/10 - OpenAI not available]</em>");
            
            this.addBotMessage(question.question);
            this.showChatOptions(question.options);
        },
        
        showTextInput: function() {
            var textInputHtml = '<div class="sit-text-input" style="display: flex; gap: 10px; align-items: center;">' +
                '<input type="text" id="sit-text-answer" class="sit-input" placeholder="Type your answer here..." style="flex: 1;" />' +
                '<button id="sit-text-submit" class="sit-btn sit-btn-primary">Send</button>' +
                '</div>';
            
            $('#sit-chat-options').html(textInputHtml);
            $('#sit-text-answer').focus();
            
            // Bind text input events
            var self = this;
            $('#sit-text-submit').on('click', function(e) {
                self.submitTextAnswer(e);
            });
            $('#sit-text-answer').on('keypress', function(e) {
                if (e.which === 13) {
                    self.submitTextAnswer(e);
                }
            });
        },
        
        submitTextAnswer: function(e) {
            e.preventDefault();
            var answer = ($('#sit-text-answer').val() || '').trim();
            if (!answer) {
                $('#sit-text-answer').addClass('sit-input-error');
                return;
            }
            
            // Process text answer same as option selection
            this.processUserAnswer('text_input', answer, answer);
        },
        
        showChatOptions: function(options) {
            var optionsHtml = '<div class="sit-chat-options-grid">';
            
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                optionsHtml += '<div class="sit-chat-option" data-option-id="' + option.id + '">' +
                    '<div class="sit-option-main">' + option.text + '</div>' +
                    '<div class="sit-option-desc">' + option.description + '</div>' +
                    '</div>';
            }
            
            optionsHtml += '</div>';
            $('#sit-chat-options').html(optionsHtml);
        },
        
        selectChatOption: function(e) {
            var $option = $(e.currentTarget);
            var optionId = $option.data('option-id');
            var optionText = $option.find('.sit-option-main').text();
            
            this.processUserAnswer(optionId, optionText, optionText);
        },
        
        processUserAnswer: function(optionId, optionText, displayText) {
            // Add user message
            this.addUserMessage(displayText);
            
            // Clear options
            $('#sit-chat-options').empty();
            
            // Store user choice in conversation history
            this.conversationHistory.push({
                step: this.currentStep,
                question_number: this.currentStep + 1,
                answer_id: optionId,
                answer_text: optionText,
                timestamp: new Date().toISOString()
            });
            
            // Increment step counter
            this.currentStep++;
            this.updateProgress();
            
            // Check if we've completed all 10 questions
            if (this.currentStep >= 10) {
                this.showTypingIndicator();
                var self = this;
                setTimeout(function() {
                    self.hideTypingIndicator();
                    self.addBotMessage("Excellent! 🎉 I have collected all 10 responses. Let me analyze your comprehensive profile and generate personalized study recommendations just for you!");
                    self.generateRecommendations();
                }, 1500);
                return;
            }
            
            // Get next question from OpenAI
            this.getNextQuestionFromAI();
        },
        
        addBotMessage: function(message) {
            var messageHtml = '<div class="sit-chat-bubble sit-bot-message">' +
                '<div class="sit-avatar"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 32px; height: 32px; border-radius: 50%;"></div>' +
                '<div class="sit-message">' + message + '</div>' +
                '</div>';
            
            $('#sit-chat-messages').append(messageHtml);
            this.scrollToBottom();
        },
        
        addUserMessage: function(message) {
            var messageHtml = '<div class="sit-chat-bubble sit-user-message">' +
                '<div class="sit-message">' + message + '</div>' +
                '<div class="sit-avatar">👤</div>' +
                '</div>';
            
            $('#sit-chat-messages').append(messageHtml);
            this.scrollToBottom();
        },
        
        scrollToBottom: function() {
            var chatMessages = $('#sit-chat-messages');
            if (chatMessages.length) {
                chatMessages.scrollTop(chatMessages[0].scrollHeight);
            }
        },
        
        generateFollowUp: function(optionId, optionText) {
            this.showTypingIndicator();
            var self = this;
            
            // Simulate AI processing time
            setTimeout(function() {
                self.hideTypingIndicator();
                self.processIntelligentFollowUp(optionId, optionText);
            }, 1500);
        },
        
        processIntelligentFollowUp: function(optionId, optionText) {
            this.currentStep++;
            this.updateProgress();
            
            var self = this;
            var response = this.getContextualResponse(optionId, optionText);
            var options = this.getFollowUpOptions(optionId, this.currentStep);
            
            setTimeout(function() {
                self.addBotMessage(response);
                if (options) {
                    self.showChatOptions(options);
                }
            }, 1000);
        },
        
        getContextualResponse: function(optionId, optionText) {
            var name = this.userProfile.name || '';
            switch(optionId) {
                case 'technology':
                    return 'I can see you\'re passionate about technology, ' + name + '! 🚀 That\'s exciting — technology is shaping our future in incredible ways. What aspect of technology draws you in most?';
                case 'business':
                    return 'Great choice with business, ' + name + '! 💼 You have an entrepreneurial mindset. The business world offers endless opportunities. What specific area of business interests you?';
                case 'people':
                    return 'How wonderful that you want to help people, ' + name + '! 🤗 That\'s such a meaningful path. Making a positive impact on others\' lives is incredibly rewarding. What type of impact do you want to make?';
                case 'creative':
                    return 'I love that you\'re drawn to creativity, ' + name + '! 🎨 Artistic expression is so important in our world. Your creative vision could inspire and move people. What form of creativity calls to you?';
                case 'science':
                    return 'Science is fascinating, ' + name + '! 🔬 You have a curious mind that wants to understand how our world works. Scientific discovery drives human progress. What scientific area intrigues you most?';
                default:
                    return 'That\'s a great choice, ' + name + '! Let me dive deeper to understand what specifically motivates you in this area.';
            }
        },
        
        getFollowUpOptions: function(optionId, step) {
            switch(optionId) {
                case 'technology':
                    return [
                        { id: 'programming', text: '⌨️ Programming & Software', description: 'Creating apps, websites, systems' },
                        { id: 'ai', text: '🤖 Artificial Intelligence', description: 'Machine learning, robotics, automation' },
                        { id: 'hardware', text: '🔧 Hardware & Engineering', description: 'Electronics, circuits, devices' },
                        { id: 'data', text: '📊 Data & Analytics', description: 'Big data, statistics, insights' }
                    ];
                case 'business':
                    return [
                        { id: 'management', text: '👔 Management & Leadership', description: 'Leading teams, strategic planning' },
                        { id: 'finance', text: '💰 Finance & Investment', description: 'Banking, markets, financial analysis' },
                        { id: 'marketing', text: '📢 Marketing & Sales', description: 'Branding, advertising, customer relations' },
                        { id: 'entrepreneurship', text: '🚀 Entrepreneurship', description: 'Starting businesses, innovation' }
                    ];
                default:
                    return [
                        { id: 'practical', text: '🔧 Hands-on & Practical', description: 'Learning by doing, labs, projects' },
                        { id: 'theoretical', text: '📚 Research & Theory', description: 'Deep study, analysis, concepts' },
                        { id: 'creative_work', text: '🎨 Creative & Innovative', description: 'Design, creativity, new ideas' },
                        { id: 'leadership', text: '👥 Leadership & Management', description: 'Leading teams, organizing, planning' }
                    ];
            }
        },
        
        updateProgress: function() {
            var totalSteps = 10;
            var progress = Math.min(this.currentStep, totalSteps);
            
            $('.sit-progress-dots .sit-dot').each(function(index) {
                if (index < progress) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        },
        
        generateRecommendations: function() {
            var self = this;
            this.showTypingIndicator();
            
            setTimeout(function() {
                var name = self.userProfile.name || '';
                self.addBotMessage('Perfect, ' + name + '! 🎉 Let me send all your responses to OpenAI to analyze and find the ideal study programs for you...');
                
                setTimeout(function() {
                    self.addBotMessage('I\'m now processing your 10 detailed responses using OpenAI to understand your unique profile and match you with the most suitable academic programs, ' + name + '.');
                    
                    setTimeout(function() {
                        // Send complete conversation to OpenAI for final recommendations
                        if (typeof sitRecommender !== 'undefined') {
                            $.ajax({
                                url: sitRecommender.apiUrl + 'chat/recommend',
                                method: 'POST',
                                headers: {
                                    'X-WP-Nonce': sitRecommender.nonce
                                },
                                data: {
                                    conversation_history: JSON.stringify(self.conversationHistory),
                                    user_profile: JSON.stringify(self.userProfile),
                                    student_name: self.userProfile.name,
                                    total_questions: 10,
                                    session_id: self.sessionId
                                },
                                success: function(response) {
                                    self.hideTypingIndicator();
                                    if (response.success && response.data) {
                                        // Track completion
                                        self.trackUsage('assessment_completed', {
                                            total_steps: self.currentStep,
                                            student_name: self.userProfile.name
                                        });
                                        
                                        // Show OpenAI recommendations
                                        if (response.data.analysis_explanation) {
                                            self.addBotMessage(response.data.analysis_explanation);
                                            setTimeout(function() {
                                                self.showAIRecommendations(response.data);
                                            }, 2000);
                                        } else {
                                            self.showAIRecommendations(response.data);
                                        }
                                    } else {
                                        console.error('OpenAI Recommendation error:', response);
                                        self.addBotMessage('I apologize, ' + name + ', but OpenAI is having trouble generating your recommendations right now. Let me provide some general suggestions based on your responses.');
                                        self.showFallbackRecommendations();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('OpenAI Recommendation AJAX error:', {xhr, status, error});
                                    self.hideTypingIndicator();
                                    self.addBotMessage('I\'m experiencing technical difficulties connecting to OpenAI, ' + name + '. Let me provide some recommendations based on your responses.');
                                    self.showFallbackRecommendations();
                                }
                            });
                        } else {
                            // Fallback for when API is not available
                            self.hideTypingIndicator();
                            self.addBotMessage('OpenAI integration is not available right now. Let me provide some recommendations based on your responses.');
                            self.showFallbackRecommendations();
                        }
                    }, 2000);
                }, 1500);
            }, 1000);
        },
        
        showAIRecommendations: function(aiData) {
            // Clear chat and show AI-generated results with original design
            $('.sit-chat-container').fadeOut(300, function() {
                var name = this.userProfile ? this.userProfile.name : 'Student';
                var resultsHtml = '<div class="sit-results-screen">' +
                    '<div class="sit-results-header">' +
                    '<h2>🎯 Your Study Recommendations</h2>' +
                    '<p>Based on your comprehensive assessment, ' + name + ', here are your personalized recommendations:</p>' +
                    '</div>' +
                    '<div class="sit-recommendations">';
                
                // Display AI recommendations with original percentage design
                if (aiData.recommendations && aiData.recommendations.length > 0) {
                    for (var i = 0; i < aiData.recommendations.length; i++) {
                        var rec = aiData.recommendations[i];
                        var confidence = rec.confidence || (95 - (i * 5)); // Decreasing confidence
                        
                        resultsHtml += '<div class="sit-recommendation-card">' +
                            '<div class="sit-rec-header">' +
                            '<h3>' + rec.field + '</h3>' +
                            '<div class="sit-match-percentage">' + confidence + '% Match</div>' +
                            '</div>' +
                            '<div class="sit-progress-bar">' +
                            '<div class="sit-progress-fill" style="width: ' + confidence + '%;"></div>' +
                            '</div>' +
                            '<div class="sit-rec-content">' +
                            '<p><strong>Why this suits you:</strong> ' + (rec.why_good_fit || rec.explanation || 'Perfect match based on your responses') + '</p>';
                        
                        if (rec.reasons && rec.reasons.length > 0) {
                            resultsHtml += '<div class="sit-rec-reasons">' +
                                '<strong>Key reasons:</strong>' +
                                '<ul>';
                            for (var j = 0; j < rec.reasons.length; j++) {
                                resultsHtml += '<li>' + rec.reasons[j] + '</li>';
                            }
                            resultsHtml += '</ul></div>';
                        }
                        
                        if (rec.career_prospects) {
                            resultsHtml += '<p><strong>Career prospects:</strong> ' + rec.career_prospects + '</p>';
                        }
                        
                        // Add program links using the mapping
                        if (aiData.programs_by_field && aiData.programs_by_field[rec.field]) {
                            var programs = aiData.programs_by_field[rec.field];
                            resultsHtml += '<div class="sit-program-links">';
                            for (var k = 0; k < programs.length && k < 2; k++) {
                                var program = programs[k];
                                resultsHtml += '<a href="' + program.url + '" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">' +
                                    program.name + ' (' + program.count + ' programs)</a>';
                            }
                            resultsHtml += '</div>';
                        }
                        
                        resultsHtml += '</div></div>';
                    }
                } else {
                    // Fallback if no recommendations in AI response
                    resultsHtml += '<div class="sit-recommendation-card">' +
                        '<h3>Computer Science & Technology</h3>' +
                        '<div class="sit-match-percentage">85% Match</div>' +
                        '<div class="sit-progress-bar"><div class="sit-progress-fill" style="width: 85%;"></div></div>' +
                        '<p>Perfect for technology enthusiasts who want to build the future.</p>' +
                        '<a href="http://search.studyinturkiye.com/results/?speciality=2663" target="_blank" class="sit-btn sit-btn-primary">View Programs</a>' +
                        '</div>' +
                        '<div class="sit-recommendation-card">' +
                        '<h3>Business Administration</h3>' +
                        '<div class="sit-match-percentage">75% Match</div>' +
                        '<div class="sit-progress-bar"><div class="sit-progress-fill" style="width: 75%;"></div></div>' +
                        '<p>Ideal for future leaders and entrepreneurs.</p>' +
                        '<a href="http://search.studyinturkiye.com/results/?speciality=2619" target="_blank" class="sit-btn sit-btn-primary">View Programs</a>' +
                        '</div>';
                }
                
                resultsHtml += '</div>' +
                    '<div class="sit-results-actions">' +
                    '<button class="sit-restart-chat sit-btn sit-btn-secondary">🔄 Start New Assessment</button>' +
                    '<button class="sit-btn sit-btn-primary sit-browse-programs">🔍 Browse All Programs</button>' +
                    '</div>' +
                    '</div>';
                
                $('.sit-recommender-container').html(resultsHtml);
            }.bind(this));
        },
        
        showFallbackRecommendations: function() {
            var name = this.userProfile.name || '';
            var interest = this.userProfile.currentInterest || 'your interests';
            
            var resultsHtml = '<div class="sit-results-screen">' +
                '<div class="sit-results-header">' +
                '<h2>🎯 Your Study Recommendations</h2>' +
                '<p>Based on our conversation, ' + name + ', here are programs that match ' + interest + ':</p>' +
                '</div>' +
                '<div class="sit-recommendations">' +
                '<div class="sit-recommendation-card">' +
                '<div class="sit-rec-header">' +
                '<h3>Computer Science & Technology</h3>' +
                '<div class="sit-match-percentage">85% Match</div>' +
                '</div>' +
                '<div class="sit-progress-bar">' +
                '<div class="sit-progress-fill" style="width: 85%;"></div>' +
                '</div>' +
                '<div class="sit-rec-content">' +
                '<p><strong>Why this suits you:</strong> Perfect for technology enthusiasts who want to build the future.</p>' +
                '<div class="sit-program-links">' +
                '<a href="http://search.studyinturkiye.com/results/?speciality=2663" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">Computer Science (86 programs)</a>' +
                '<a href="http://search.studyinturkiye.com/results/?speciality=2442" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">Technology & IT (498 programs)</a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="sit-recommendation-card">' +
                '<div class="sit-rec-header">' +
                '<h3>Business Administration</h3>' +
                '<div class="sit-match-percentage">75% Match</div>' +
                '</div>' +
                '<div class="sit-progress-bar">' +
                '<div class="sit-progress-fill" style="width: 75%;"></div>' +
                '</div>' +
                '<div class="sit-rec-content">' +
                '<p><strong>Why this suits you:</strong> Ideal for future leaders and entrepreneurs.</p>' +
                '<div class="sit-program-links">' +
                '<a href="http://search.studyinturkiye.com/results/?speciality=2619" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">Business Administration (556 programs)</a>' +
                '<a href="http://search.studyinturkiye.com/results/?speciality=2618" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">International Business (196 programs)</a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="sit-recommendation-card">' +
                '<div class="sit-rec-header">' +
                '<h3>Engineering</h3>' +
                '<div class="sit-match-percentage">70% Match</div>' +
                '</div>' +
                '<div class="sit-progress-bar">' +
                '<div class="sit-progress-fill" style="width: 70%;"></div>' +
                '</div>' +
                '<div class="sit-rec-content">' +
                '<p><strong>Why this suits you:</strong> Great for analytical minds who enjoy problem-solving and innovation.</p>' +
                '<div class="sit-program-links">' +
                '<a href="http://search.studyinturkiye.com/results/?speciality=2686" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">Civil Engineering (407 programs)</a>' +
                '<a href="http://search.studyinturkiye.com/results/?speciality=2685" target="_blank" class="sit-btn sit-btn-primary sit-btn-small">Electrical Engineering (328 programs)</a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="sit-results-actions">' +
                '<button class="sit-restart-chat sit-btn sit-btn-secondary">🔄 Start New Assessment</button>' +
                '<button class="sit-btn sit-btn-primary sit-browse-programs">🔍 Browse All Programs</button>' +
                '</div>' +
                '</div>';
            
            $('.sit-recommender-container').html(resultsHtml);
        },
        
        showRecommendations: function(response) {
            $('.sit-chat-container').fadeOut(300, function() {
                // Implementation for full API response would go here
                // For now, use fallback
                this.showFallbackRecommendations();
            }.bind(this));
        },
        
        showTypingIndicator: function() {
            var typingHtml = '<div class="sit-chat-bubble sit-bot-message sit-typing" id="sit-typing-indicator">' +
                '<div class="sit-avatar"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 32px; height: 32px; border-radius: 50%;"></div>' +
                '<div class="sit-message">' +
                '<div class="sit-typing-dots">' +
                '<span></span><span></span><span></span>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            $('#sit-chat-messages').append(typingHtml);
            this.scrollToBottom();
            this.isTyping = true;
        },
        
        hideTypingIndicator: function() {
            $('#sit-typing-indicator').remove();
            this.isTyping = false;
        },
        
        restartChat: function(e) {
            e.preventDefault();
            this.init();
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
