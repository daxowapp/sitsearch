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
            $(document).on('keypress', '#sit-student-name', (e) => {
                if (e.which === 13) {
                    this.submitStudentName(e);
                }
            });
            console.log('SIT Chat Recommender: Event bindings initialized');
        },
        
        showWelcomeScreen: function() {
            const welcomeHtml = `
                <div class="sit-welcome-screen">
                    <div class="sit-welcome-content">
                        <h2><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 32px; height: 32px; vertical-align: middle; margin-right: 8px;"> AI Study Path Advisor</h2>
                        <p>I'm Mr.Sit your personal AI advisor! I'll have a comprehensive conversation with you (10+ questions) to deeply understand your interests, skills, and goals to recommend the perfect academic programs.</p>
                        
                        <div class="sit-chat-preview">
                            <div class="sit-chat-bubble sit-bot-message">
                                <div class="sit-avatar"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 24px; height: 24px; border-radius: 50%;"></div>
                                <div class="sit-message">
                                    Hi! I'm here to conduct a thorough assessment to find your ideal study path. We'll explore your interests, strengths, values, and career goals through meaningful questions. Ready to start our conversation?
                                </div>
                            </div>
                        </div>
                        
                        <div class="sit-welcome-actions">
                            <button class="sit-start-chat sit-btn sit-btn-primary sit-btn-large">
                                <span class="sit-btn-icon">💬</span>
                                <span class="sit-btn-text">Start Comprehensive Assessment</span>
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
        },
        
        showChatScreen: function() {
            const chatHtml = `
                <div class="sit-chat-screen">
                    <div class="sit-chat-header">
                        <div class="sit-chat-title">
                            <span class="sit-chat-icon"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 24px; height: 24px; vertical-align: middle;"></span>
                            <span>AI Study Advisor - Comprehensive Assessment</span>
                        </div>
                        <div class="sit-chat-progress">
                            <div class="sit-progress-dots">
                                <span class="sit-dot active"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                                <span class="sit-dot"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sit-chat-container">
                        <div class="sit-chat-messages" id="sit-chat-messages">
                            <!-- Messages will be added here -->
                        </div>
                        
                        <div class="sit-chat-options" id="sit-chat-options">
                            <!-- Options will be added here -->
                        </div>
                    </div>
                </div>
            
            $('.sit-recommender-container').html(chatHtml);
        },
        
        startConversation: function() {
            this.trackUsage('assessment_started', {});
            this.addBotMessage("Hi there! 👋 I'm your AI advisor. Before we begin, what should I call you?");
            this.showNamePrompt();
        },
        
        showNamePrompt: function() {
            const nameHtml = '<div class="sit-name-capture" id="sit-name-capture" style="display: flex; gap: 10px; align-items: center;"><input type="text" id="sit-student-name" class="sit-input" placeholder="Enter your name" style="flex: 1;" /><button id="sit-name-submit" class="sit-btn sit-btn-primary">Continue</button></div>';
            $('#sit-chat-options').html(nameHtml);
            $('#sit-student-name').focus();
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
            
            // Personalized greeting and intro
            setTimeout(() => {
                this.addBotMessage('Hi ' + name + '! 👋 I\'m excited to help you discover your perfect study path through a comprehensive assessment!');
                setTimeout(() => {
                    this.addBotMessage('I\'ll ask you about 10 meaningful questions to understand your interests, strengths, values, and career goals. This will help me give you the most accurate recommendations possible, ' + name + '.');
                    setTimeout(() => {
                        this.addBotMessage('Let\'s start with your core interests, ' + name + ' — what draws you in most?');
                        this.showFirstQuestion();
                    }, 1200);
                }, 900);
            }, 400);
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
                    this.addBotMessage("Excellent! 🎉 I have everything I need to find your perfect study programs. Let me analyze your comprehensive profile and generate personalized recommendations just for you!");
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
            
            // Temporarily bypass AJAX and use local intelligent responses
            this.hideTypingIndicator();
            this.processIntelligentFollowUp(optionId, optionText);
        },
        
        processIntelligentFollowUp: function(optionId, optionText) {
            this.currentStep++;
            this.updateProgress();
            
            // Create intelligent, contextual responses based on user's choice
            let response, options;
            
            // Analyze user's conversation history for context
            const userInterests = this.conversationHistory.map(entry => entry.answer);
            const firstInterest = userInterests[0];
            
            setTimeout(() => {
                switch(this.currentStep) {
                    case 1:
                        response = this.getContextualResponse(optionId, optionText);
                        options = this.getFollowUpOptions(optionId, this.currentStep);
                        break;
                    case 2:
                        response = this.personalize(`That's very insightful, {name}! 🌟 Now I want to understand your learning style and natural strengths.`);
                        // Tailor options by first interest
                        options = this.getLearningStyleOptions(firstInterest);
                        break;
                    case 3:
                        response = this.personalize(`Perfect! 💡 Let me understand what kind of work environment and values are important to you, {name}.`);
                        options = this.getValuesOptions(firstInterest);
                        break;
                    case 4:
                        response = this.personalize(`Great insights, {name}! 🚀 Now tell me about your career dreams and long-term aspirations.`);
                        options = this.getCareerOptions(firstInterest);
                        break;
                    case 5:
                        response = this.personalize("Wonderful, {name}! 🌟 Let me ask about your preferred approach to challenges and problem-solving.");
                        options = this.getProblemSolvingOptions(firstInterest);
                        break;
                    case 6:
                        response = this.personalize("Great insight, {name}! 🎯 Now tell me about your ideal work-life balance and lifestyle preferences.");
                        options = this.getLifestyleOptions(firstInterest);
                        break;
                    case 7:
                        response = this.personalize("Perfect, {name}! 🌟 Let me understand your preferred study approach and program structure.");
                        options = this.getStudyApproachOptions(firstInterest);
                        break;
                    case 8:
                        response = this.personalize("Excellent, {name}! 🚀 One final question about your future goals and timeline.");
                        options = this.getTimelineOptions(firstInterest);
                        break;
                    default:
                        if (this.currentStep >= 9) {
                            response = this.personalize("Excellent, {name}! 🎉 I have everything I need to find your perfect study programs. Let me analyze your comprehensive profile and generate personalized recommendations just for you!");
                            this.generateRecommendations();
                            return;
                        } else {
                            response = this.personalize("That's very helpful, {name}! Let me ask you one more important question.");
                            options = this.getGenericOptions();
                        }
                        break;
                }
                
                this.addBotMessage(response);
                if (options) {
                    this.showChatOptions(options);
                }
            }, 1500);
        },
        
        getContextualResponse: function(optionId, optionText) {
            switch(optionId) {
                case 'technology':
                    return this.personalize("I can see you're passionate about technology, {name}! 🚀 That's exciting — technology is shaping our future in incredible ways. What aspect of technology draws you in most?");
                case 'business':
                    return this.personalize("Great choice with business, {name}! 💼 You have an entrepreneurial mindset. The business world offers endless opportunities. What specific area of business interests you?");
                case 'people':
                    return this.personalize("How wonderful that you want to help people, {name}! 🤗 That's such a meaningful path. Making a positive impact on others' lives is incredibly rewarding. What type of impact do you want to make?");
                case 'creative':
                    return this.personalize("I love that you're drawn to creativity, {name}! 🎨 Artistic expression is so important in our world. Your creative vision could inspire and move people. What form of creativity calls to you?");
                case 'science':
                    return this.personalize("Science is fascinating, {name}! 🔬 You have a curious mind that wants to understand how our world works. Scientific discovery drives human progress. What scientific area intrigues you most?");
                default:
                    return this.personalize("That's a great choice, {name}! Let me dive deeper to understand what specifically motivates you in this area.");
            }
        },
        
        getFollowUpOptions: function(optionId, step) {
            switch(optionId) {
                case 'technology':
                    return [
                        { id: 'programming', text: '⌨️ Programming & Software', description: 'Creating apps, websites, systems' },
                        { id: 'ai', text: '🤖 Artificial Intelligence', description: 'Machine learning, robotics, automation' },
                        { id: 'hardware', text: '🔧 Hardware & Engineering', description: 'Electronics, circuits, devices' },
                        { id: 'data', text: '📊 Data & Analytics', description: 'Big data, statistics, insights' },
                        { id: 'cybersecurity', text: '🛡️ Cybersecurity', description: 'Protecting systems, ethical hacking' }
                    ];
                case 'business':
                    return [
                        { id: 'management', text: '👔 Management & Leadership', description: 'Leading teams, strategic planning' },
                        { id: 'finance', text: '💰 Finance & Investment', description: 'Banking, markets, financial analysis' },
                        { id: 'marketing', text: '📢 Marketing & Sales', description: 'Branding, advertising, customer relations' },
                        { id: 'entrepreneurship', text: '🚀 Entrepreneurship', description: 'Starting businesses, innovation' },
                        { id: 'international', text: '🌍 International Business', description: 'Global trade, cross-cultural business' }
                    ];
                case 'people':
                    return [
                        { id: 'healthcare', text: '🏥 Healthcare & Medicine', description: 'Treating patients, medical research' },
                        { id: 'education', text: '📚 Education & Teaching', description: 'Inspiring students, sharing knowledge' },
                        { id: 'psychology', text: '🧠 Psychology & Counseling', description: 'Mental health, therapy, understanding behavior' },
                        { id: 'social', text: '🤲 Social Work', description: 'Community support, advocacy, social justice' },
                        { id: 'nursing', text: '👩‍⚕️ Nursing & Care', description: 'Patient care, health support' }
                    ];
                case 'creative':
                    return [
                        { id: 'design', text: '🎨 Visual Design', description: 'Graphic design, UI/UX, branding' },
                        { id: 'media', text: '🎬 Media & Film', description: 'Video production, photography, cinema' },
                        { id: 'fashion', text: '👗 Fashion & Style', description: 'Clothing design, fashion industry' },
                        { id: 'architecture', text: '🏛️ Architecture', description: 'Building design, urban planning' },
                        { id: 'music', text: '🎵 Music & Audio', description: 'Composition, performance, sound design' }
                    ];
                case 'science':
                    return [
                        { id: 'biology', text: '🧬 Biology & Life Sciences', description: 'Living organisms, genetics, ecology' },
                        { id: 'chemistry', text: '⚗️ Chemistry', description: 'Molecules, reactions, materials' },
                        { id: 'physics', text: '🌌 Physics & Astronomy', description: 'Universe, energy, fundamental forces' },
                        { id: 'environmental', text: '🌱 Environmental Science', description: 'Climate, sustainability, conservation' },
                        { id: 'medical', text: '🔬 Medical Research', description: 'Disease, treatments, biomedical science' }
                    ];
                default:
                    return [
                        { id: 'solve_problems', text: '🧩 Solving Complex Problems', description: 'Analytical challenges and puzzles' },
                        { id: 'help_others', text: '🤝 Helping Others Succeed', description: 'Making a positive impact on people' },
                        { id: 'create_things', text: '🎨 Creating & Building', description: 'Designing and making new things' },
                        { id: 'understand_world', text: '🔬 Understanding How Things Work', description: 'Research and discovery' }
                    ];
            }
        },

        // Tailored option generators by first interest
        getLearningStyleOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'code_projects', text: '💻 Build Software Projects', description: 'Apps, websites, automation' },
                        { id: 'algorithms', text: '🧠 Algorithm Challenges', description: 'Problem-solving & logic' },
                        { id: 'hardware_labs', text: '🔩 Hardware Labs', description: 'Circuits, devices, IoT' },
                        { id: 'data_projects', text: '📊 Data Projects', description: 'Analysis, dashboards, ML' },
                        { id: 'security_labs', text: '🛡️ Security Labs', description: 'Pentesting, protection' }
                    ];
                case 'business':
                    return [
                        { id: 'case_studies', text: '📈 Case Studies', description: 'Real business scenarios' },
                        { id: 'internships', text: '🏢 Internships', description: 'Hands-on industry exposure' },
                        { id: 'sales_sim', text: '🗣️ Sales Simulations', description: 'Pitching, negotiation' },
                        { id: 'finance_models', text: '💹 Financial Modeling', description: 'Valuation, analysis' },
                        { id: 'startup_projects', text: '🚀 Startup Projects', description: 'Build and iterate ideas' }
                    ];
                case 'people':
                    return [
                        { id: 'clinical', text: '🏥 Clinical Practice', description: 'Patient-centered learning' },
                        { id: 'classroom', text: '🏫 Classroom Practice', description: 'Teaching & pedagogy' },
                        { id: 'counseling', text: '🧠 Counseling Labs', description: 'Therapy simulations' },
                        { id: 'community', text: '🤲 Community Projects', description: 'Service & outreach' },
                        { id: 'research_psych', text: '🔎 Research & Assessment', description: 'Evidence-based work' }
                    ];
                case 'creative':
                    return [
                        { id: 'studio', text: '🎨 Studio Practice', description: 'Hands-on creation & critique' },
                        { id: 'portfolio', text: '🗂️ Portfolio Projects', description: 'Build standout work' },
                        { id: 'workshops', text: '🧩 Collaboration Workshops', description: 'Team creativity' },
                        { id: 'theory', text: '📚 Art & Design Theory', description: 'Concepts & history' },
                        { id: 'media_prod', text: '🎬 Media Production', description: 'Film, audio, animation' }
                    ];
                case 'science':
                    return [
                        { id: 'lab', text: '⚗️ Lab Experiments', description: 'Chem, bio, physics labs' },
                        { id: 'field', text: '🗺️ Field Research', description: 'Environmental & fieldwork' },
                        { id: 'maths', text: '∑ Mathematics/Theory', description: 'Models & proofs' },
                        { id: 'data_analysis', text: '📊 Data Analysis', description: 'Statistics & inference' },
                        { id: 'interdisciplinary', text: '🔗 Interdisciplinary', description: 'Science + tech/business' }
                    ];
                default:
                    return [
                        { id: 'hands_on', text: '🔧 Hands-on Learning', description: 'Labs, experiments, practical work' },
                        { id: 'theoretical', text: '📚 Theoretical Study', description: 'Research, analysis, deep thinking' },
                        { id: 'collaborative', text: '👥 Group Projects', description: 'Teamwork and collaboration' },
                        { id: 'independent', text: '🎯 Independent Study', description: 'Self-directed learning' },
                        { id: 'visual_creative', text: '🎨 Visual & Creative', description: 'Design, art, creative expression' }
                    ];
            }
        },

        getValuesOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'innovation', text: '🚀 Innovation', description: 'Create new tech, push limits' },
                        { id: 'impact', text: '🌍 Real-World Impact', description: 'Solve meaningful problems' },
                        { id: 'craft', text: '🛠️ Craft & Quality', description: 'Clean code, robust systems' },
                        { id: 'team', text: '🤝 Strong Teams', description: 'Collaboration & culture' },
                        { id: 'learning', text: '📚 Continuous Learning', description: 'Always improving' }
                    ];
                case 'business':
                    return [
                        { id: 'growth', text: '📈 Growth & Success', description: 'Scale companies & revenue' },
                        { id: 'leadership', text: '👔 Leadership', description: 'Lead teams & strategy' },
                        { id: 'ethics', text: '⚖️ Ethics & Trust', description: 'Responsible business' },
                        { id: 'global', text: '🌐 Global Reach', description: 'International markets' },
                        { id: 'wealth', text: '💰 High Earning', description: 'Financial outcomes' }
                    ];
                case 'people':
                    return [
                        { id: 'compassion', text: '💖 Compassion', description: 'Care & empathy' },
                        { id: 'service', text: '🤲 Service', description: 'Help communities' },
                        { id: 'evidence', text: '🔎 Evidence-Based', description: 'Best practices' },
                        { id: 'education', text: '📚 Education', description: 'Teach & empower' },
                        { id: 'advocacy', text: '🗣️ Advocacy', description: 'Voice for others' }
                    ];
                case 'creative':
                    return [
                        { id: 'originality', text: '✨ Originality', description: 'Unique perspective' },
                        { id: 'aesthetics', text: '🎭 Aesthetics', description: 'Beauty & style' },
                        { id: 'collab', text: '🤝 Collaboration', description: 'Create with others' },
                        { id: 'craftsmanship', text: '🪚 Craftsmanship', description: 'Master your tools' },
                        { id: 'expression', text: '🗯️ Expression', description: 'Voice & message' }
                    ];
                case 'science':
                    return [
                        { id: 'rigor', text: '📏 Scientific Rigor', description: 'Accuracy & method' },
                        { id: 'discovery', text: '🔬 Discovery', description: 'New knowledge' },
                        { id: 'application', text: '🧰 Application', description: 'Use science in practice' },
                        { id: 'team_research', text: '👥 Research Teams', description: 'Collaborative labs' },
                        { id: 'ethics_science', text: '⚖️ Ethics in Science', description: 'Responsible research' }
                    ];
                default:
                    return [
                        { id: 'stability', text: '🏛️ Stability & Security', description: 'Predictable career with good benefits' },
                        { id: 'flexibility', text: '🌊 Flexibility & Freedom', description: 'Work-life balance and autonomy' },
                        { id: 'high_income', text: '💰 High Earning Potential', description: 'Financial success and wealth' },
                        { id: 'social_impact', text: '🌍 Social Impact', description: 'Making the world a better place' },
                        { id: 'prestige', text: '👑 Recognition & Prestige', description: 'Respected profession and status' }
                    ];
            }
        },

        getCareerOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'software_engineer', text: '👨‍💻 Software Engineer', description: 'Apps, systems, platforms' },
                        { id: 'data_scientist', text: '📊 Data Scientist', description: 'ML, analytics, insights' },
                        { id: 'security_engineer', text: '🛡️ Security Engineer', description: 'Protect systems & data' },
                        { id: 'product_manager', text: '🧭 Product Manager', description: 'Tech + business' },
                        { id: 'research_engineer', text: '🔬 Research Engineer', description: 'R&D and innovation' }
                    ];
                case 'business':
                    return [
                        { id: 'founder', text: '🚀 Startup Founder', description: 'Create and scale companies' },
                        { id: 'consultant', text: '🧠 Strategy Consultant', description: 'Advise executives' },
                        { id: 'investment', text: '💼 Investment Analyst', description: 'Markets & valuation' },
                        { id: 'brand_manager', text: '🏷️ Brand/Marketing Manager', description: 'Grow demand' },
                        { id: 'ops_manager', text: '⚙️ Operations Manager', description: 'Efficiency & delivery' }
                    ];
                case 'people':
                    return [
                        { id: 'nurse', text: '👩‍⚕️ Nurse/Healthcare Pro', description: 'Patient-centered care' },
                        { id: 'teacher', text: '👩‍🏫 Teacher/Educator', description: 'Inspire students' },
                        { id: 'therapist', text: '🧠 Therapist/Counselor', description: 'Mental health support' },
                        { id: 'public_health', text: '🏥 Public Health Pro', description: 'Community health' },
                        { id: 'social_worker', text: '🤲 Social Worker', description: 'Advocacy & support' }
                    ];
                case 'creative':
                    return [
                        { id: 'designer', text: '🎨 Designer (UI/UX/Graphic)', description: 'Visual problem solving' },
                        { id: 'filmmaker', text: '🎬 Filmmaker/Producer', description: 'Stories & media' },
                        { id: 'architect', text: '🏛️ Architect', description: 'Built environments' },
                        { id: 'musician', text: '🎵 Musician/Composer', description: 'Performance & sound' },
                        { id: 'art_director', text: '🖼️ Art Director', description: 'Creative leadership' }
                    ];
                case 'science':
                    return [
                        { id: 'biologist', text: '🧬 Biologist', description: 'Life sciences research' },
                        { id: 'chemist', text: '⚗️ Chemist', description: 'Materials & reactions' },
                        { id: 'physicist', text: '🌌 Physicist', description: 'Energy & universe' },
                        { id: 'environmentalist', text: '🌱 Environmental Scientist', description: 'Sustainability' },
                        { id: 'biomedical', text: '🔬 Biomedical Researcher', description: 'Health & disease' }
                    ];
                default:
                    return [
                        { id: 'entrepreneur', text: '🚀 Start My Own Business', description: 'Be an entrepreneur and innovator' },
                        { id: 'expert', text: '🎓 Become a Leading Expert', description: 'Master a field and be recognized' },
                        { id: 'manager', text: '👔 Lead Teams & Organizations', description: 'Management and leadership roles' },
                        { id: 'researcher', text: '🔬 Advance Human Knowledge', description: 'Research and discovery' },
                        { id: 'artist', text: '🎨 Create Beautiful Things', description: 'Artistic and creative work' }
                    ];
            }
        },

        getProblemSolvingOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'debug_systematic', text: '🔍 Systematic Debugging', description: 'Step-by-step problem isolation' },
                        { id: 'prototype_test', text: '🛠️ Prototype & Test', description: 'Build solutions and iterate' },
                        { id: 'research_docs', text: '📚 Research Documentation', description: 'Study specs and best practices' },
                        { id: 'collaborate_code', text: '👥 Code Collaboration', description: 'Pair programming and reviews' },
                        { id: 'automate_solve', text: '⚡ Automate Solutions', description: 'Script and tool-based approaches' }
                    ];
                case 'business':
                    return [
                        { id: 'data_driven', text: '📊 Data-Driven Analysis', description: 'Use metrics and analytics' },
                        { id: 'stakeholder_input', text: '🤝 Stakeholder Consultation', description: 'Gather diverse perspectives' },
                        { id: 'market_research', text: '🔍 Market Research', description: 'Understand customer needs' },
                        { id: 'pilot_programs', text: '🧪 Pilot Programs', description: 'Test solutions small-scale' },
                        { id: 'strategic_planning', text: '🎯 Strategic Planning', description: 'Long-term systematic approach' }
                    ];
                case 'people':
                    return [
                        { id: 'empathetic_listening', text: '👂 Empathetic Listening', description: 'Understand root concerns' },
                        { id: 'evidence_based', text: '📋 Evidence-Based Practice', description: 'Use proven interventions' },
                        { id: 'collaborative_care', text: '🤝 Collaborative Care', description: 'Team-based solutions' },
                        { id: 'holistic_approach', text: '🌟 Holistic Approach', description: 'Address whole person/system' },
                        { id: 'preventive_focus', text: '🛡️ Preventive Focus', description: 'Address causes, not just symptoms' }
                    ];
                case 'creative':
                    return [
                        { id: 'brainstorm_ideate', text: '💡 Brainstorm & Ideate', description: 'Generate many creative options' },
                        { id: 'visual_thinking', text: '🎨 Visual Problem-Solving', description: 'Sketch, map, and visualize' },
                        { id: 'iterate_refine', text: '🔄 Iterate & Refine', description: 'Continuous improvement cycles' },
                        { id: 'cross_pollinate', text: '🌐 Cross-Pollinate Ideas', description: 'Combine different fields' },
                        { id: 'user_centered', text: '👤 User-Centered Design', description: 'Focus on end-user experience' }
                    ];
                case 'science':
                    return [
                        { id: 'hypothesis_test', text: '🧪 Hypothesis Testing', description: 'Scientific method approach' },
                        { id: 'systematic_observation', text: '🔬 Systematic Observation', description: 'Careful data collection' },
                        { id: 'peer_review', text: '👥 Peer Review Process', description: 'Collaborative validation' },
                        { id: 'literature_review', text: '📚 Literature Review', description: 'Build on existing knowledge' },
                        { id: 'experimental_design', text: '⚗️ Experimental Design', description: 'Controlled testing approaches' }
                    ];
                default:
                    return [
                        { id: 'analytical', text: '🧮 Analytical Approach', description: 'Data-driven, systematic analysis' },
                        { id: 'creative_solve', text: '💡 Creative Problem-Solving', description: 'Innovative, out-of-the-box thinking' },
                        { id: 'collaborative_solve', text: '🤝 Collaborative Solutions', description: 'Working with others to solve problems' },
                        { id: 'practical_solve', text: '🔧 Practical Solutions', description: 'Hands-on, real-world approaches' },
                        { id: 'research_solve', text: '📚 Research-Based Solutions', description: 'Evidence-based, thorough investigation' }
                    ];
            }
        },

        getLifestyleOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'remote_flexible', text: '🏠 Remote & Flexible', description: 'Work from anywhere, flexible hours' },
                        { id: 'startup_energy', text: '⚡ Startup Energy', description: 'Fast-paced, high-growth environment' },
                        { id: 'big_tech_stability', text: '🏢 Big Tech Stability', description: 'Established companies, good benefits' },
                        { id: 'freelance_projects', text: '🎯 Freelance Projects', description: 'Independent contractor work' },
                        { id: 'work_life_balance', text: '⚖️ Strong Work-Life Balance', description: 'Clear boundaries, personal time' }
                    ];
                case 'business':
                    return [
                        { id: 'corporate_ladder', text: '📈 Corporate Advancement', description: 'Clear promotion paths, hierarchy' },
                        { id: 'entrepreneurial', text: '🚀 Entrepreneurial Freedom', description: 'Own business, be your own boss' },
                        { id: 'travel_international', text: '✈️ Travel & International', description: 'Global opportunities, cultural exposure' },
                        { id: 'networking_social', text: '🤝 Networking & Social', description: 'Relationship-building, events' },
                        { id: 'high_earning', text: '💰 High Earning Potential', description: 'Financial success and wealth building' }
                    ];
                case 'people':
                    return [
                        { id: 'meaningful_impact', text: '💖 Meaningful Impact', description: 'Make a real difference in lives' },
                        { id: 'community_based', text: '🏘️ Community-Based Work', description: 'Local, grassroots involvement' },
                        { id: 'institutional', text: '🏥 Institutional Setting', description: 'Hospitals, schools, organizations' },
                        { id: 'advocacy_policy', text: '🗣️ Advocacy & Policy', description: 'Systemic change, policy work' },
                        { id: 'direct_service', text: '🤲 Direct Service', description: 'One-on-one helping relationships' }
                    ];
                case 'creative':
                    return [
                        { id: 'artistic_freedom', text: '🎨 Artistic Freedom', description: 'Creative control, self-expression' },
                        { id: 'collaborative_creative', text: '🎭 Collaborative Creative', description: 'Team-based creative projects' },
                        { id: 'commercial_creative', text: '💼 Commercial Creative', description: 'Client work, business applications' },
                        { id: 'independent_artist', text: '🎪 Independent Artist', description: 'Solo practice, gallery representation' },
                        { id: 'creative_teaching', text: '🎓 Creative Teaching', description: 'Share skills, mentor others' }
                    ];
                case 'science':
                    return [
                        { id: 'research_academic', text: '🎓 Academic Research', description: 'University labs, publish papers' },
                        { id: 'industry_applied', text: '🏭 Industry Application', description: 'Commercial R&D, product development' },
                        { id: 'field_work', text: '🗺️ Field Work', description: 'Outdoor research, data collection' },
                        { id: 'lab_controlled', text: '⚗️ Controlled Lab Environment', description: 'Precise, controlled conditions' },
                        { id: 'science_communication', text: '📢 Science Communication', description: 'Educate public, media outreach' }
                    ];
                default:
                    return [
                        { id: 'stability', text: '🏛️ Stability & Security', description: 'Predictable career with good benefits' },
                        { id: 'flexibility', text: '🌊 Flexibility & Freedom', description: 'Work-life balance and autonomy' },
                        { id: 'high_income', text: '💰 High Earning Potential', description: 'Financial success and wealth' },
                        { id: 'social_impact', text: '🌍 Social Impact', description: 'Making the world a better place' },
                        { id: 'prestige', text: '👑 Recognition & Prestige', description: 'Respected profession and status' }
                    ];
            }
        },

        getStudyApproachOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'bootcamp_intensive', text: '⚡ Intensive Bootcamp', description: '3-6 months, job-ready skills' },
                        { id: 'cs_degree', text: '🎓 Computer Science Degree', description: '4-year comprehensive foundation' },
                        { id: 'online_self_paced', text: '💻 Online Self-Paced', description: 'Flexible, work while learning' },
                        { id: 'apprenticeship', text: '🔧 Tech Apprenticeship', description: 'Learn while working at companies' },
                        { id: 'masters_specialized', text: '🎯 Specialized Masters', description: 'Advanced degree in specific area' }
                    ];
                case 'business':
                    return [
                        { id: 'mba_traditional', text: '🎓 Traditional MBA', description: '2-year comprehensive business education' },
                        { id: 'business_undergrad', text: '📚 Business Undergraduate', description: '4-year business foundation' },
                        { id: 'executive_program', text: '👔 Executive Program', description: 'Part-time for working professionals' },
                        { id: 'entrepreneurship', text: '🚀 Entrepreneurship Focus', description: 'Startup and innovation programs' },
                        { id: 'industry_specific', text: '🎯 Industry-Specific', description: 'Finance, marketing, operations focus' }
                    ];
                case 'people':
                    return [
                        { id: 'clinical_program', text: '🏥 Clinical Program', description: 'Hands-on patient/client experience' },
                        { id: 'research_focused', text: '🔬 Research-Focused', description: 'Evidence-based practice emphasis' },
                        { id: 'community_based', text: '🏘️ Community-Based', description: 'Field work, community partnerships' },
                        { id: 'interdisciplinary', text: '🔗 Interdisciplinary', description: 'Multiple fields integration' },
                        { id: 'accelerated_program', text: '⚡ Accelerated Program', description: 'Faster path to practice' }
                    ];
                case 'creative':
                    return [
                        { id: 'studio_intensive', text: '🎨 Studio-Intensive', description: 'Hands-on creation, portfolio building' },
                        { id: 'liberal_arts', text: '📚 Liberal Arts Foundation', description: 'Broad creative and critical thinking' },
                        { id: 'technical_skills', text: '🛠️ Technical Skills Focus', description: 'Software, tools, craft mastery' },
                        { id: 'theory_practice', text: '⚖️ Theory & Practice Balance', description: 'Conceptual and applied learning' },
                        { id: 'collaborative_projects', text: '🤝 Collaborative Projects', description: 'Team-based creative work' }
                    ];
                case 'science':
                    return [
                        { id: 'research_university', text: '🎓 Research University', description: 'Lab experience, thesis work' },
                        { id: 'applied_program', text: '🔧 Applied Science Program', description: 'Industry-focused, practical skills' },
                        { id: 'field_study', text: '🗺️ Field Study Emphasis', description: 'Outdoor research, data collection' },
                        { id: 'interdisciplinary_sci', text: '🔗 Interdisciplinary Science', description: 'Cross-field integration' },
                        { id: 'pre_professional', text: '🏥 Pre-Professional Track', description: 'Preparation for med/vet/dental school' }
                    ];
                default:
                    return [
                        { id: 'fast_track', text: '⚡ Quick Entry to Career', description: 'Shorter programs, faster results' },
                        { id: 'deep_study', text: '📖 Deep, Comprehensive Study', description: 'Longer programs with depth' },
                        { id: 'practical_skills', text: '🛠️ Practical Skills Focus', description: 'Job-ready skills and training' },
                        { id: 'academic_theory', text: '🎓 Academic & Theoretical', description: 'Research and scholarly approach' }
                    ];
            }
        },

        getTimelineOptions: function(firstInterest) {
            switch(firstInterest) {
                case 'technology':
                    return [
                        { id: 'immediate_job', text: '⚡ Job Ready ASAP', description: '6 months or less to employment' },
                        { id: 'one_year', text: '📅 1-2 Years', description: 'Certificate or associate degree' },
                        { id: 'four_year_plan', text: '🎓 4-Year Degree', description: 'Traditional bachelor\'s program' },
                        { id: 'advanced_degree', text: '🎯 Advanced Degree', description: '6+ years including masters/PhD' },
                        { id: 'career_change', text: '🔄 Career Transition', description: 'Flexible timeline, currently working' }
                    ];
                case 'business':
                    return [
                        { id: 'entrepreneur_now', text: '🚀 Start Business Now', description: 'Learn while building company' },
                        { id: 'mba_track', text: '📈 MBA Track', description: '2-3 years for advanced degree' },
                        { id: 'corporate_climb', text: '🏢 Corporate Ladder', description: '4+ years, traditional path' },
                        { id: 'consulting_prep', text: '🧠 Consulting Preparation', description: '2-4 years, strategic focus' },
                        { id: 'industry_switch', text: '🔄 Industry Switch', description: 'Transition from current field' }
                    ];
                case 'people':
                    return [
                        { id: 'direct_service', text: '🤲 Direct Service Soon', description: '1-2 years to helping roles' },
                        { id: 'licensed_professional', text: '🏥 Licensed Professional', description: '4-6 years including licensure' },
                        { id: 'advanced_practice', text: '🎓 Advanced Practice', description: '6+ years, specialized expertise' },
                        { id: 'policy_leadership', text: '🗣️ Policy & Leadership', description: '5+ years, systemic change focus' },
                        { id: 'second_career', text: '🔄 Second Career', description: 'Transition from other field' }
                    ];
                case 'creative':
                    return [
                        { id: 'portfolio_ready', text: '🎨 Portfolio Ready', description: '1-2 years to professional work' },
                        { id: 'fine_arts_degree', text: '🎭 Fine Arts Degree', description: '4 years comprehensive training' },
                        { id: 'commercial_focus', text: '💼 Commercial Focus', description: '2-3 years, client-ready skills' },
                        { id: 'artistic_mastery', text: '🏆 Artistic Mastery', description: '6+ years, MFA or equivalent' },
                        { id: 'creative_entrepreneur', text: '🚀 Creative Entrepreneur', description: 'Build creative business' }
                    ];
                case 'science':
                    return [
                        { id: 'lab_technician', text: '⚗️ Lab Technician', description: '1-2 years, entry-level positions' },
                        { id: 'research_scientist', text: '🔬 Research Scientist', description: '4-6 years including research experience' },
                        { id: 'phd_researcher', text: '🎓 PhD Researcher', description: '8+ years, independent research' },
                        { id: 'industry_scientist', text: '🏭 Industry Scientist', description: '4-6 years, commercial applications' },
                        { id: 'science_educator', text: '👩‍🏫 Science Educator', description: '4-6 years, teaching focus' }
                    ];
                default:
                    return [
                        { id: 'immediate', text: '⚡ Immediate Entry', description: 'Start working as soon as possible' },
                        { id: 'short_term', text: '📅 1-2 Years', description: 'Quick certification or training' },
                        { id: 'medium_term', text: '🎓 3-4 Years', description: 'Traditional degree program' },
                        { id: 'long_term', text: '🎯 5+ Years', description: 'Advanced degree or specialization' },
                        { id: 'flexible', text: '🔄 Flexible Timeline', description: 'Part-time, work while studying' }
                    ];
            }
        },

        getGenericOptions: function() {
            return [
                { id: 'fast_track', text: '⚡ Quick Entry to Career', description: 'Shorter programs, faster results' },
                { id: 'deep_study', text: '📖 Deep, Comprehensive Study', description: 'Longer programs with depth' },
                { id: 'practical_skills', text: '🛠️ Practical Skills Focus', description: 'Job-ready skills and training' },
                { id: 'academic_theory', text: '🎓 Academic & Theoretical', description: 'Research and scholarly approach' }
            ];
        },

        // Personalization helper
        personalize: function(text) {
            const name = this.userProfile.name || '';
            return text.replaceAll('{name}', name);
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
                this.currentStep++;
                this.updateProgress();
            }, 1000);
        },
        
        updateProgress: function() {
            const totalSteps = 10;
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
                this.addBotMessage(this.personalize("Perfect, {name}! 🎉 Let me analyze everything we've discussed and find the ideal study programs for you..."));
                
                setTimeout(() => {
                    this.addBotMessage(this.personalize("I'm now processing your 9 detailed responses using AI to understand your unique profile and match you with the most suitable academic programs, {name}."));
                    
                    setTimeout(() => {
                        // Call real recommendation endpoint
                        $.ajax({
                            url: sitRecommender.apiUrl + 'chat/recommend',
                            method: 'POST',
                            headers: {
                                'X-WP-Nonce': sitRecommender.nonce
                            },
                            data: {
                                conversation_history: this.conversationHistory,
                                user_profile: this.userProfile,
                                student_name: this.userProfile.name
                            },
                            success: (response) => {
                                this.hideTypingIndicator();
                                if (response.success) {
                                    // Track completion
                                    this.trackUsage('assessment_completed', {
                                        total_steps: this.currentStep,
                                        student_name: this.userProfile.name
                                    });
                                    
                                    // Track recommendations
                                    if (response.recommendations) {
                                        response.recommendations.forEach(rec => {
                                            this.trackUsage('recommendation_generated', {
                                                field: rec.field,
                                                confidence: rec.confidence
                                            });
                                        });
                                    }
                                    
                                    // Show AI analysis explanation first
                                    if (response.analysis_explanation) {
                                        this.addBotMessage(this.personalize(response.analysis_explanation));
                                        setTimeout(() => {
                                            this.showRecommendations(response);
                                        }, 2000);
                                    } else {
                                        this.showRecommendations(response);
                                    }
                                } else {
                                    console.error('Recommendation error:', response);
                                    this.addBotMessage(this.personalize("I apologize, {name}, but I'm having trouble generating your recommendations right now. Please try again later."));
                                }
                            },
                            error: (xhr, status, error) => {
                                console.error('Recommendation AJAX error:', {xhr, status, error});
                                this.hideTypingIndicator();
                                this.addBotMessage(this.personalize("I'm experiencing technical difficulties, {name}. Please refresh the page and try again."));
                            }
                        });
                    }, 2000);
                }, 1500);
            }, 1000);
        },
        
        // Track usage statistics
        trackUsage: function(action, data) {
            if (typeof sitRecommender !== 'undefined' && sitRecommender.trackStats) {
                $.post(sitRecommender.ajaxUrl, {
                    action: 'sit_track_usage',
                    action_type: action,
                    data: JSON.stringify(data),
                    nonce: wp_create_nonce('sit_track_usage')
                }).fail(function() {
                    // Silently fail - don't interrupt user experience
                    console.log('Statistics tracking failed');
                });
            }
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
                        <p>Based on our comprehensive conversation, here are the perfect study programs for you:</p>
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
                            🔄 Start New Assessment
                        </button>
                        <button class="sit-btn sit-btn-primary" onclick="window.open('http://search.studyinturkiye.com/study-areas', '_blank')">
                            🔍 Browse All Programs
                        </button>
                    </div>
                </div>
            `;
            
            $('.sit-recommender-container').html(resultsHtml);
        },
        
        addBotMessage: function(message) {
            const messageHtml = `
                <div class="sit-chat-bubble sit-bot-message">
                    <div class="sit-avatar"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 32px; height: 32px; border-radius: 50%;"></div>
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
                    <div class="sit-avatar"><img src="https://studyinturkiye.com/wp-content/uploads/2025/09/file-preview.png" alt="AI" style="width: 32px; height: 32px; border-radius: 50%;"></div>
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
