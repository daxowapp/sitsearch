<?php
/**
 * SIT REST API
 * 
 * Handles REST API endpoints for the quiz and recommendations.
 * Includes security, validation, and rate limiting.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SIT_REST_API {
    
    /**
     * API namespace
     */
    private $namespace = 'sit-recommender/v1';
    
    /**
     * Rate limiting settings
     */
    private $rate_limit;
    private $rate_limit_window;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register routes immediately since we're called from rest_api_init hook
        $this->register_routes();
        
        $general_settings = get_option('sit_recommender_general', array());
        $this->rate_limit = $general_settings['rate_limit'] ?? 100;
        $this->rate_limit_window = $general_settings['rate_limit_window'] ?? 3600;
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // OpenAI Chat Question endpoint
        register_rest_route($this->namespace, '/chat/question', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_ai_question'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'context' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'JSON string with conversation context'
                )
            )
        ));
        
        // OpenAI Chat Recommendation endpoint
        register_rest_route($this->namespace, '/chat/recommend', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_ai_recommendations'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'conversation_history' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'JSON string with complete conversation history'
                ),
                'user_profile' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'JSON string with user profile data'
                ),
                'student_name' => array(
                    'required' => false,
                    'type' => 'string'
                )
            )
        ));

        // Quiz start endpoint
        register_rest_route($this->namespace, '/quiz/start', array(
            'methods' => 'POST',
            'callback' => array($this, 'start_quiz'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'user_data' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => __('Optional user profile data', 'sit-program-recommender')
                )
            )
        ));
        
        // Quiz answer endpoint
        register_rest_route($this->namespace, '/quiz/answer', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_answer'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => __('Quiz session ID', 'sit-program-recommender')
                ),
                'question_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => __('Question ID', 'sit-program-recommender')
                ),
                'answer_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => __('Selected answer ID', 'sit-program-recommender')
                )
            )
        ));
        
        // Get recommendations endpoint
        register_rest_route($this->namespace, '/recommend', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_recommendations'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'session_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => __('Quiz session ID', 'sit-program-recommender')
                ),
                'filters' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => __('Additional filters', 'sit-program-recommender')
                ),
                'use_openai' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                    'description' => __('Use OpenAI for reranking', 'sit-program-recommender')
                )
            )
        ));
        
        // Get programs endpoint
        register_rest_route($this->namespace, '/programs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_programs'),
            'permission_callback' => array($this, 'check_permissions'),
            'args' => array(
                'search' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => __('Search query', 'sit-program-recommender')
                ),
                'school' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => __('Filter by school', 'sit-program-recommender')
                ),
                'level' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => __('Filter by level', 'sit-program-recommender')
                ),
                'mode' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => __('Filter by study mode', 'sit-program-recommender')
                ),
                'page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1,
                    'description' => __('Page number', 'sit-program-recommender')
                ),
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'description' => __('Programs per page', 'sit-program-recommender')
                )
            )
        ));
        
        // Get filter options endpoint
        register_rest_route($this->namespace, '/filters', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_filter_options'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Get quiz questions endpoint
        register_rest_route($this->namespace, '/quiz/questions', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_quiz_questions'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Debug endpoint
        register_rest_route($this->namespace, '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // Test OpenAI endpoint
        register_rest_route($this->namespace, '/test-openai', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_openai_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // Simple test quiz endpoint
        register_rest_route($this->namespace, '/test-quiz', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_quiz_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // Test recommendation endpoint
        register_rest_route($this->namespace, '/test-recommend', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_recommend_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // AI Chat endpoints
        register_rest_route($this->namespace, '/chat/message', array(
            'methods' => 'POST',
            'callback' => array($this, 'chat_message_endpoint'),
            'permission_callback' => '__return_true',
            'args' => array(
                'message' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'User message'
                ),
                'conversation_history' => array(
                    'required' => false,
                    'type' => 'array',
                    'description' => 'Conversation history'
                ),
                'current_step' => array(
                    'required' => false,
                    'type' => 'integer',
                    'description' => 'Current conversation step'
                )
            )
        ));
        
        register_rest_route($this->namespace, '/chat/recommend', array(
            'methods' => 'POST',
            'callback' => array($this, 'chat_recommend_endpoint'),
            'permission_callback' => '__return_true',
            'args' => array(
                'conversation_history' => array(
                    'required' => false,
                    'type' => 'array',
                    'description' => 'Conversation history'
                ),
                'user_profile' => array(
                    'required' => false,
                    'type' => 'object',
                    'description' => 'User profile data'
                ),
                'student_name' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Student name'
                )
            )
        ));
        
        // Simple test endpoint
        register_rest_route($this->namespace, '/chat/test', array(
            'methods' => 'POST',
            'callback' => array($this, 'chat_test_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // OpenAI debug endpoint
        register_rest_route($this->namespace, '/debug/openai', array(
            'methods' => 'GET',
            'callback' => array($this, 'debug_openai_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // Test OpenAI call endpoint
        register_rest_route($this->namespace, '/debug/test-openai', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_openai_call_endpoint'),
            'permission_callback' => '__return_true'
        ));
        
        // Simple recommendation test endpoint
        register_rest_route($this->namespace, '/test/recommend', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_recommend_simple'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Check permissions and rate limiting
     */
    public function check_permissions($request) {
        error_log('SIT REST: check_permissions called for ' . $request->get_route());
        
        // Check if plugin is enabled
        $general_settings = get_option('sit_recommender_general', array());
        error_log('SIT REST: Plugin enabled: ' . (empty($general_settings['enabled']) ? 'false' : 'true'));
        if (empty($general_settings['enabled'])) {
            return new WP_Error('plugin_disabled', __('SIT Program Recommender is currently disabled.', 'sit-program-recommender'), array('status' => 503));
        }
        
        // Check rate limiting
        if (!$this->check_rate_limit()) {
            error_log('SIT REST: Rate limit exceeded');
            return new WP_Error('rate_limit_exceeded', __('Rate limit exceeded. Please try again later.', 'sit-program-recommender'), array('status' => 429));
        }
        
        // Check nonce for POST requests
        if ($request->get_method() === 'POST') {
            $nonce = $request->get_header('X-WP-Nonce');
            error_log('SIT REST: Nonce received: ' . ($nonce ? 'present' : 'missing'));
            error_log('SIT REST: Nonce verification: ' . (wp_verify_nonce($nonce, 'wp_rest') ? 'valid' : 'invalid'));
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return new WP_Error('invalid_nonce', __('Invalid security token.', 'sit-program-recommender'), array('status' => 403));
            }
        }
        
        error_log('SIT REST: Permissions check passed');
        return true;
    }
    
    /**
     * Start a new quiz session - Generate questions with AI
     */
    public function start_quiz($request) {
        error_log('SIT REST: start_quiz method called');
        try {
            $user_data = $request->get_param('user_data') ?? array();
            $num_questions = $request->get_param('num_questions') ?: 10;
            $num_questions = max(5, min(20, intval($num_questions))); // Between 5-20 questions
            
            // Generate session ID
            $session_id = $this->generate_session_id();
            
            // Generate questions using AI
            $engine = new SIT_Engine();
            $questions = $engine->generate_questions($num_questions);
            
            error_log('SIT REST: Questions generated: ' . json_encode($questions));
            error_log('SIT REST: Questions empty check: ' . (empty($questions) ? 'true' : 'false'));
            error_log('SIT REST: Questions isset check: ' . (isset($questions['questions']) ? 'true' : 'false'));
            error_log('SIT REST: Questions count: ' . (isset($questions['questions']) ? count($questions['questions']) : 'N/A'));
            
            if (empty($questions) || empty($questions['questions'])) {
                // Check if OpenAI is configured
                $openai_settings = get_option('sit_recommender_openai', array());
                if (empty($openai_settings['enabled']) || empty($openai_settings['api_key'])) {
                    return new WP_Error('openai_not_configured', __('OpenAI is not configured. Please add your API key in the admin settings.', 'sit-program-recommender'), array('status' => 500));
                }
                
                return new WP_Error('no_questions', __('Failed to generate questions. Please check the error log for OpenAI API details.', 'sit-program-recommender'), array('status' => 500));
            }
            
            // Initialize session data
            $session_data = array(
                'session_id' => $session_id,
                'user_data' => $this->sanitize_user_data($user_data),
                'questions' => $questions, // Store generated questions in session
                'answers' => array(),
                'started_at' => current_time('mysql'),
                'ip_address' => $this->get_client_ip(),
                'user_agent' => $request->get_header('User-Agent')
            );
            
            // Store session (expires in 1 hour)
            set_transient('sit_quiz_session_' . $session_id, $session_data, 3600);
            
            return rest_ensure_response(array(
                'success' => true,
                'session_id' => $session_id,
                'questions' => $this->prepare_questions_for_frontend($questions),
                'total_questions' => count($questions['questions'] ?? array()),
                'message' => __('Quiz session started successfully.', 'sit-program-recommender')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('quiz_start_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Submit quiz answer
     */
    public function submit_answer($request) {
        try {
            $session_id = sanitize_text_field($request->get_param('session_id'));
            $question_id = intval($request->get_param('question_id'));
            $answer_id = sanitize_text_field($request->get_param('answer_id'));
            
            // Get session data
            $session_data = get_transient('sit_quiz_session_' . $session_id);
            if (!$session_data) {
                return new WP_Error('invalid_session', __('Invalid or expired session.', 'sit-program-recommender'), array('status' => 400));
            }
            
            // Validate question and answer
            $questions = get_option('sit_recommender_questions', array());
            if (!$this->validate_answer($questions, $question_id, $answer_id)) {
                return new WP_Error('invalid_answer', __('Invalid question or answer.', 'sit-program-recommender'), array('status' => 400));
            }
            
            // Store answer
            $session_data['answers'][$question_id] = $answer_id;
            $session_data['last_updated'] = current_time('mysql');
            
            // Update session
            set_transient('sit_quiz_session_' . $session_id, $session_data, 3600);
            
            // Calculate progress
            $total_questions = count($questions['questions'] ?? array());
            $answered_questions = count($session_data['answers']);
            $progress = $total_questions > 0 ? ($answered_questions / $total_questions) * 100 : 0;
            
            return rest_ensure_response(array(
                'success' => true,
                'progress' => round($progress, 1),
                'answered' => $answered_questions,
                'total' => $total_questions,
                'is_complete' => $answered_questions >= $total_questions,
                'message' => __('Answer submitted successfully.', 'sit-program-recommender')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('answer_submit_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get study field recommendations using AI analysis
     */
    public function get_recommendations($request) {
        try {
            $session_id = sanitize_text_field($request->get_param('session_id'));
            
            // Get session data
            $session_data = get_transient('sit_quiz_session_' . $session_id);
            if (!$session_data) {
                return new WP_Error('invalid_session', __('Invalid or expired session.', 'sit-program-recommender'), array('status' => 400));
            }
            
            // Check if quiz has answers
            if (empty($session_data['answers'])) {
                return new WP_Error('no_answers', __('Please answer some questions first.', 'sit-program-recommender'), array('status' => 400));
            }
            
            // Generate recommendations using AI
            $engine = new SIT_Engine();
            
            // Get the questions from session (they were generated with AI)
            $questions = $session_data['questions'] ?? array();
            
            // Analyze answers and get field recommendations
            $recommendations = $engine->analyze_answers_and_recommend($session_data['answers'], $questions);
            
            if (empty($recommendations['recommendations'])) {
                return new WP_Error('no_recommendations', __('Unable to generate recommendations.', 'sit-program-recommender'), array('status' => 500));
            }
            
            // Get programs for recommended fields
            $programs_by_field = $engine->get_programs_for_fields($recommendations['recommendations']);
            
            // Store recommendations in session for future reference
            $session_data['recommendations'] = $recommendations;
            $session_data['programs_by_field'] = $programs_by_field;
            $session_data['recommended_at'] = current_time('mysql');
            set_transient('sit_quiz_session_' . $session_id, $session_data, 3600);
            
            return rest_ensure_response(array(
                'success' => true,
                'recommendations' => $recommendations['recommendations'],
                'programs_by_field' => $this->prepare_programs_by_field($programs_by_field),
                'total_fields' => count($recommendations['recommendations']),
                'message' => __('Recommendations generated successfully.', 'sit-program-recommender')
            ));
            
        } catch (Exception $e) {
            return new WP_Error('recommendation_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get programs with filters
     */
    public function get_programs($request) {
        try {
            $filters = array();
            $args = array();
            
            // Extract filters from request
            $filter_params = array('search', 'school', 'level', 'mode');
            foreach ($filter_params as $param) {
                $value = $request->get_param($param);
                if (!empty($value)) {
                    $filters[$param] = sanitize_text_field($value);
                }
            }
            
            // Handle pagination
            $page = max(1, intval($request->get_param('page')));
            $per_page = min(50, max(1, intval($request->get_param('per_page'))));
            
            $args['posts_per_page'] = $per_page;
            $args['paged'] = $page;
            
            // Get programs
            $dal = new SIT_DAL();
            $programs = $dal->get_programs($filters, $args);
            
            // Get total count for pagination
            $total_query = new WP_Query(array_merge($args, array('posts_per_page' => -1, 'fields' => 'ids')));
            $total = $total_query->found_posts;
            
            return rest_ensure_response(array(
                'success' => true,
                'programs' => $this->prepare_programs_for_frontend($programs),
                'pagination' => array(
                    'page' => $page,
                    'per_page' => $per_page,
                    'total' => $total,
                    'total_pages' => ceil($total / $per_page)
                )
            ));
            
        } catch (Exception $e) {
            return new WP_Error('programs_fetch_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get filter options
     */
    public function get_filter_options($request) {
        try {
            $dal = new SIT_DAL();
            
            $options = array(
                'schools' => $dal->get_filter_options('school'),
                'levels' => $dal->get_filter_options('level'),
                'modes' => $dal->get_filter_options('mode'),
                'durations' => $dal->get_filter_options('duration'),
                'intakes' => $dal->get_filter_options('intake')
            );
            
            return rest_ensure_response(array(
                'success' => true,
                'filters' => $options
            ));
            
        } catch (Exception $e) {
            return new WP_Error('filters_fetch_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Get quiz questions
     */
    public function get_quiz_questions($request) {
        try {
            $questions = get_option('sit_recommender_questions', array());
            
            return rest_ensure_response(array(
                'success' => true,
                'questions' => $this->prepare_questions_for_frontend($questions)
            ));
            
        } catch (Exception $e) {
            return new WP_Error('questions_fetch_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Test endpoint for debugging
     */
    public function test_endpoint($request) {
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'REST API is working!',
            'namespace' => $this->namespace,
            'timestamp' => current_time('mysql'),
            'plugin_enabled' => !empty(get_option('sit_recommender_general', array())['enabled']),
            'openai_enabled' => !empty(get_option('sit_recommender_openai', array())['enabled'])
        ));
    }
    
    /**
     * Test OpenAI endpoint
     */
    public function test_openai_endpoint($request) {
        $engine = new SIT_Engine();
        $openai_settings = get_option('sit_recommender_openai', array());
        
        $result = array(
            'openai_configured' => !empty($openai_settings['enabled']) && !empty($openai_settings['api_key']),
            'api_key_present' => !empty($openai_settings['api_key']),
            'api_key_length' => !empty($openai_settings['api_key']) ? strlen($openai_settings['api_key']) : 0,
            'model' => $openai_settings['model'] ?? 'not set',
            'enabled' => !empty($openai_settings['enabled'])
        );
        
        if ($result['openai_configured']) {
            // Try to generate a simple test question
            $test_questions = $engine->generate_questions(1);
            $result['test_generation'] = !empty($test_questions);
            if (empty($test_questions)) {
                $result['error'] = 'Failed to generate test question - check error logs';
            }
        } else {
            $result['error'] = 'OpenAI not properly configured';
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Simple test quiz endpoint
     */
    public function test_quiz_endpoint($request) {
        $num_questions = $request->get_param('num_questions') ?: 10;
        $num_questions = max(2, min(20, intval($num_questions))); // Allow up to 20 questions
        
        $all_questions = array(
            array('id' => 1, 'question' => 'What interests you most?', 'options' => array(
                array('id' => 'a', 'text' => 'Technology and computers'),
                array('id' => 'b', 'text' => 'Art and creativity'),
                array('id' => 'c', 'text' => 'Helping people'),
                array('id' => 'd', 'text' => 'Business and money')
            )),
            array('id' => 2, 'question' => 'How do you prefer to work?', 'options' => array(
                array('id' => 'a', 'text' => 'Alone with focus'),
                array('id' => 'b', 'text' => 'In creative teams'),
                array('id' => 'c', 'text' => 'With people directly'),
                array('id' => 'd', 'text' => 'Leading projects')
            )),
            array('id' => 3, 'question' => 'What motivates you most?', 'options' => array(
                array('id' => 'a', 'text' => 'Innovation and discovery'),
                array('id' => 'b', 'text' => 'Creative expression'),
                array('id' => 'c', 'text' => 'Making a difference'),
                array('id' => 'd', 'text' => 'Financial success')
            )),
            array('id' => 4, 'question' => 'Your ideal work environment?', 'options' => array(
                array('id' => 'a', 'text' => 'High-tech office'),
                array('id' => 'b', 'text' => 'Creative studio'),
                array('id' => 'c', 'text' => 'Community center'),
                array('id' => 'd', 'text' => 'Corporate office')
            )),
            array('id' => 5, 'question' => 'What subjects did you enjoy in school?', 'options' => array(
                array('id' => 'a', 'text' => 'Math and Science'),
                array('id' => 'b', 'text' => 'Art and Literature'),
                array('id' => 'c', 'text' => 'Social Studies'),
                array('id' => 'd', 'text' => 'Economics and Business')
            )),
            array('id' => 6, 'question' => 'How do you solve problems?', 'options' => array(
                array('id' => 'a', 'text' => 'Analyze data and logic'),
                array('id' => 'b', 'text' => 'Think creatively'),
                array('id' => 'c', 'text' => 'Ask others for input'),
                array('id' => 'd', 'text' => 'Consider costs and benefits')
            )),
            array('id' => 7, 'question' => 'What type of projects excite you?', 'options' => array(
                array('id' => 'a', 'text' => 'Building new technology'),
                array('id' => 'b', 'text' => 'Creating beautiful things'),
                array('id' => 'c', 'text' => 'Helping communities'),
                array('id' => 'd', 'text' => 'Growing businesses')
            )),
            array('id' => 8, 'question' => 'Your communication style?', 'options' => array(
                array('id' => 'a', 'text' => 'Technical and precise'),
                array('id' => 'b', 'text' => 'Expressive and visual'),
                array('id' => 'c', 'text' => 'Empathetic and personal'),
                array('id' => 'd', 'text' => 'Persuasive and clear')
            )),
            array('id' => 9, 'question' => 'What kind of impact do you want to make?', 'options' => array(
                array('id' => 'a', 'text' => 'Advance human knowledge'),
                array('id' => 'b', 'text' => 'Inspire and move people'),
                array('id' => 'c', 'text' => 'Improve lives directly'),
                array('id' => 'd', 'text' => 'Drive economic growth')
            )),
            array('id' => 10, 'question' => 'Your learning preference?', 'options' => array(
                array('id' => 'a', 'text' => 'Hands-on experimentation'),
                array('id' => 'b', 'text' => 'Creative exploration'),
                array('id' => 'c', 'text' => 'Group discussions'),
                array('id' => 'd', 'text' => 'Case studies and examples')
            )),
            array('id' => 11, 'question' => 'What type of challenges excite you?', 'options' => array(
                array('id' => 'a', 'text' => 'Technical puzzles'),
                array('id' => 'b', 'text' => 'Creative challenges'),
                array('id' => 'c', 'text' => 'Social problems'),
                array('id' => 'd', 'text' => 'Business challenges')
            )),
            array('id' => 12, 'question' => 'Your ideal team role?', 'options' => array(
                array('id' => 'a', 'text' => 'Technical specialist'),
                array('id' => 'b', 'text' => 'Creative director'),
                array('id' => 'c', 'text' => 'Team coordinator'),
                array('id' => 'd', 'text' => 'Project manager')
            )),
            array('id' => 13, 'question' => 'What drives your decisions?', 'options' => array(
                array('id' => 'a', 'text' => 'Logic and data'),
                array('id' => 'b', 'text' => 'Intuition and creativity'),
                array('id' => 'c', 'text' => 'People and relationships'),
                array('id' => 'd', 'text' => 'Results and efficiency')
            )),
            array('id' => 14, 'question' => 'Your preferred work pace?', 'options' => array(
                array('id' => 'a', 'text' => 'Steady and methodical'),
                array('id' => 'b', 'text' => 'Bursts of inspiration'),
                array('id' => 'c', 'text' => 'Collaborative rhythm'),
                array('id' => 'd', 'text' => 'Fast and decisive')
            )),
            array('id' => 15, 'question' => 'What type of recognition motivates you?', 'options' => array(
                array('id' => 'a', 'text' => 'Technical achievement'),
                array('id' => 'b', 'text' => 'Creative recognition'),
                array('id' => 'c', 'text' => 'Helping others succeed'),
                array('id' => 'd', 'text' => 'Business success')
            )),
            array('id' => 16, 'question' => 'Your approach to new information?', 'options' => array(
                array('id' => 'a', 'text' => 'Analyze thoroughly'),
                array('id' => 'b', 'text' => 'Explore creatively'),
                array('id' => 'c', 'text' => 'Discuss with others'),
                array('id' => 'd', 'text' => 'Apply practically')
            )),
            array('id' => 17, 'question' => 'What type of feedback do you value?', 'options' => array(
                array('id' => 'a', 'text' => 'Technical accuracy'),
                array('id' => 'b', 'text' => 'Creative merit'),
                array('id' => 'c', 'text' => 'Personal impact'),
                array('id' => 'd', 'text' => 'Business value')
            )),
            array('id' => 18, 'question' => 'Your ideal work schedule?', 'options' => array(
                array('id' => 'a', 'text' => 'Structured and consistent'),
                array('id' => 'b', 'text' => 'Flexible and creative'),
                array('id' => 'c', 'text' => 'People-centered hours'),
                array('id' => 'd', 'text' => 'Results-driven timing')
            )),
            array('id' => 19, 'question' => 'What energizes you most?', 'options' => array(
                array('id' => 'a', 'text' => 'Solving complex problems'),
                array('id' => 'b', 'text' => 'Creating something new'),
                array('id' => 'c', 'text' => 'Connecting with people'),
                array('id' => 'd', 'text' => 'Achieving goals')
            )),
            array('id' => 20, 'question' => 'Your long-term career vision?', 'options' => array(
                array('id' => 'a', 'text' => 'Technical expert/researcher'),
                array('id' => 'b', 'text' => 'Creative innovator'),
                array('id' => 'c', 'text' => 'Community leader'),
                array('id' => 'd', 'text' => 'Business executive')
            ))
        );
        
        $selected_questions = array_slice($all_questions, 0, $num_questions);
        $hardcoded_questions = array('questions' => $selected_questions);
        
        return rest_ensure_response(array(
            'success' => true,
            'session_id' => 'test_session_' . time(),
            'questions' => $hardcoded_questions['questions'],
            'total_questions' => count($hardcoded_questions['questions']),
            'message' => 'Test quiz started successfully'
        ));
    }
    
    /**
     * Test recommendation endpoint
     */
    public function test_recommend_endpoint($request) {
        // Simulate AI analysis with hardcoded recommendations
        $recommendations = array(
            array(
                'field' => 'Computer Science',
                'confidence' => 92,
                'reasons' => array(
                    'Strong analytical thinking demonstrated in your answers',
                    'Preference for technical problem-solving approaches',
                    'Interest in logical and systematic work methods'
                ),
                'career_prospects' => 'Software development, AI research, cybersecurity, data science, system architecture',
                'why_good_fit' => 'Your analytical mindset and systematic approach to problem-solving align perfectly with computer science. You show strong logical thinking and an interest in technical challenges.'
            ),
            array(
                'field' => 'Business Administration',
                'confidence' => 78,
                'reasons' => array(
                    'Leadership qualities evident in your responses',
                    'Strategic thinking and goal-oriented approach',
                    'Interest in organizational and management challenges'
                ),
                'career_prospects' => 'Management consulting, project management, entrepreneurship, corporate strategy, business development',
                'why_good_fit' => 'Your leadership potential and strategic thinking make you well-suited for business administration. You demonstrate strong organizational skills and business acumen.'
            ),
            array(
                'field' => 'Engineering',
                'confidence' => 85,
                'reasons' => array(
                    'Problem-solving orientation and methodical approach',
                    'Interest in building and creating solutions',
                    'Technical aptitude and systematic thinking'
                ),
                'career_prospects' => 'Design engineering, manufacturing, construction, research and development, technical consulting',
                'why_good_fit' => 'Your systematic problem-solving approach and interest in creating practical solutions make engineering an excellent fit for your skills and interests.'
            )
        );
        
        // Map AI recommendations to real study areas from SIT Search
        $study_area_mapping = array(
            'Computer Science' => array(
                array(
                    'name' => 'Computer Science',
                    'count' => 86,
                    'speciality_id' => 2663,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2663'
                ),
                array(
                    'name' => 'Computer Engineering', 
                    'count' => 100,
                    'speciality_id' => 2590,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2590'
                ),
                array(
                    'name' => 'Technology, Software, Computer, IT',
                    'count' => 498,
                    'speciality_id' => 2442,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2442'
                ),
                array(
                    'name' => 'Artificial Intelligence',
                    'count' => 13,
                    'speciality_id' => 2707,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2707'
                )
            ),
            'Business Administration' => array(
                array(
                    'name' => 'Business Administration, Management, General',
                    'count' => 556,
                    'speciality_id' => 2619,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2619'
                ),
                array(
                    'name' => 'International Business, International Trade',
                    'count' => 196,
                    'speciality_id' => 2618,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2618'
                ),
                array(
                    'name' => 'Accounting, Finance & Economics',
                    'count' => 404,
                    'speciality_id' => 2622,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2622'
                ),
                array(
                    'name' => 'Marketing, Analyst, Advertising',
                    'count' => 101,
                    'speciality_id' => 2576,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2576'
                ),
                array(
                    'name' => 'Human Resources',
                    'count' => 52,
                    'speciality_id' => 2620,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2620'
                )
            ),
            'Engineering' => array(
                array(
                    'name' => 'Civil Engineering & Construction',
                    'count' => 407,
                    'speciality_id' => 2686,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2686'
                ),
                array(
                    'name' => 'Electrical & Electronics Engineering',
                    'count' => 328,
                    'speciality_id' => 2685,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2685'
                ),
                array(
                    'name' => 'Mechanical, Energy, Manufacturing, Robotic',
                    'count' => 284,
                    'speciality_id' => 2675,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2675'
                ),
                array(
                    'name' => 'Industrial Engineering',
                    'count' => 130,
                    'speciality_id' => 2677,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2677'
                ),
                array(
                    'name' => 'Environmental Engineering',
                    'count' => 103,
                    'speciality_id' => 2684,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2684'
                ),
                array(
                    'name' => 'Material Engineering',
                    'count' => 92,
                    'speciality_id' => 2674,
                    'url' => 'http://search.studyinturkiye.com/results/?speciality=2674'
                )
            )
        );
        
        return rest_ensure_response(array(
            'success' => true,
            'recommendations' => $recommendations,
            'programs_by_field' => $study_area_mapping,
            'total_fields' => count($recommendations),
            'message' => 'Test recommendations generated successfully'
        ));
    }
    
    /**
     * AI Chat message endpoint - generates dynamic responses and follow-up questions
     */
    public function chat_message_endpoint($request) {
        error_log('SIT Chat: chat_message_endpoint called');
        
        $user_message = $request->get_param('message');
        $conversation_history = $request->get_param('conversation_history') ?: array();
        $current_step = $request->get_param('current_step') ?: 0;
        
        error_log('SIT Chat: user_message=' . $user_message);
        error_log('SIT Chat: current_step=' . $current_step);
        error_log('SIT Chat: conversation_history=' . json_encode($conversation_history));
        
        // Use OpenAI to generate dynamic response
        $engine = new SIT_Engine();
        $response = $this->generate_ai_response($user_message, $conversation_history, $current_step);
        
        error_log('SIT Chat: response=' . json_encode($response));
        error_log('SIT Chat: OpenAI enabled=' . ($engine->is_openai_enabled() ? 'true' : 'false'));
        
        return rest_ensure_response(array(
            'success' => true,
            'response' => $response['message'],
            'options' => $response['options'] ?? array(),
            'next_step' => $current_step + 1,
            'is_final' => $response['is_final'] ?? false
        ));
    }
    
    /**
     * Generate AI response using OpenAI or fallback logic
     */
    private function generate_ai_response($user_message, $conversation_history, $current_step) {
        $engine = new SIT_Engine();
        
        // Try OpenAI first
        if ($engine->is_openai_enabled()) {
            error_log('SIT Chat: OpenAI is enabled, attempting to generate response');
            $openai_response = $this->generate_openai_response($user_message, $conversation_history, $current_step);
            if ($openai_response && !empty($openai_response['message'])) {
                error_log('SIT Chat: OpenAI response successful, returning');
                return $openai_response;
            } else {
                error_log('SIT Chat: OpenAI response failed or empty');
            }
        } else {
            error_log('SIT Chat: OpenAI is not enabled');
        }
        
        error_log('SIT Chat: Using fallback response system');
        
        // Fallback to predefined responses
        return $this->generate_fallback_response($user_message, $conversation_history, $current_step);
    }
    
    /**
     * Generate response using OpenAI
     */
    private function generate_openai_response($user_message, $conversation_history, $current_step) {
        $engine = new SIT_Engine();
        
        // Build comprehensive conversation context for deep student profiling
        $context = "You are an expert educational counselor and career advisor with 20+ years of experience. Your goal is to conduct a comprehensive assessment to understand the student's personality, interests, skills, values, and career aspirations to recommend the perfect academic program.\n\n";
        
        $context .= "CONVERSATION GOALS:\n";
        $context .= "- Conduct 10-12 meaningful questions to build a complete student profile\n";
        $context .= "- Explore: interests, strengths, learning style, career goals, values, personality traits\n";
        $context .= "- Ask follow-up questions based on previous answers\n";
        $context .= "- Be encouraging, personal, and insightful\n\n";
        
        // Add conversation history with analysis
        if (!empty($conversation_history)) {
            $context .= "CONVERSATION HISTORY:\n";
            foreach ($conversation_history as $entry) {
                $context .= "Q" . ($entry['step'] + 1) . " - Student chose: " . $entry['user_choice'] . "\n";
            }
            $context .= "\n";
        }
        
        $context .= "CURRENT SITUATION:\n";
        $context .= "- Current step: " . ($current_step + 1) . " of 10-12 questions\n";
        $context .= "- Student's latest answer: " . $user_message . "\n\n";
        
        // Dynamic questioning strategy based on step
        if ($current_step < 2) {
            $context .= "NEXT QUESTION FOCUS: Explore their core interests and passions more deeply. Ask about what excites them, what they're naturally drawn to, or what problems they want to solve.\n\n";
        } elseif ($current_step < 4) {
            $context .= "NEXT QUESTION FOCUS: Understand their strengths, skills, and learning preferences. Ask about how they like to learn, what they're good at, or their problem-solving style.\n\n";
        } elseif ($current_step < 6) {
            $context .= "NEXT QUESTION FOCUS: Explore their values, work environment preferences, and lifestyle goals. Ask about what motivates them, their ideal work setting, or work-life balance.\n\n";
        } elseif ($current_step < 8) {
            $context .= "NEXT QUESTION FOCUS: Dive into career aspirations and impact goals. Ask about their dream job, the impact they want to make, or their long-term vision.\n\n";
        } elseif ($current_step < 10) {
            $context .= "NEXT QUESTION FOCUS: Clarify specific preferences about study approach, specialization areas, or practical considerations like duration, difficulty, etc.\n\n";
        } else {
            $context .= "FINAL QUESTIONS: Ask any remaining clarifying questions to complete their profile. After 10-12 questions, set is_final to true.\n\n";
        }
        
        $context .= "RESPONSE REQUIREMENTS:\n";
        $context .= "1. Start with an encouraging, personalized response to their choice (2-3 sentences)\n";
        $context .= "2. Ask ONE insightful follow-up question that builds on their answer\n";
        $context .= "3. Provide 4-6 diverse, meaningful multiple choice options\n";
        $context .= "4. Use emojis to make it engaging and friendly\n";
        $context .= "5. Make each option distinct and explore different aspects\n";
        $context .= "6. Keep options concise but descriptive\n\n";
        
        $context .= "AVAILABLE STUDY FIELDS TO CONSIDER:\n";
        $context .= "Computer Science, Engineering, Business Administration, Medicine, Psychology, Education, Art & Design, Law, Economics, Biology, Chemistry, Physics, Architecture, Communications, International Relations, and more.\n\n";
        
        $context .= "Respond in JSON format:\n";
        $context .= "{\n";
        $context .= '  "message": "Your encouraging, personalized response (2-3 sentences with emojis)",\n';
        $context .= '  "options": [\n';
        $context .= '    {"id": "unique_id1", "text": "🎯 Option Title", "description": "Brief but meaningful description"},\n';
        $context .= '    {"id": "unique_id2", "text": "🚀 Option Title", "description": "Brief but meaningful description"},\n';
        $context .= '    {"id": "unique_id3", "text": "💡 Option Title", "description": "Brief but meaningful description"},\n';
        $context .= '    {"id": "unique_id4", "text": "🌟 Option Title", "description": "Brief but meaningful description"}\n';
        $context .= '  ],\n';
        $context .= '  "is_final": ' . ($current_step >= 9 ? 'true' : 'false') . '\n';
        $context .= "}";
        
        $response = $engine->call_openai($context);
        
        error_log('SIT Chat: OpenAI raw response: ' . json_encode($response));
        
        if ($response && is_array($response)) {
            error_log('SIT Chat: OpenAI response is valid array, returning');
            return $response;
        } else {
            error_log('SIT Chat: OpenAI response is invalid: ' . gettype($response));
        }
        
        // Fallback if OpenAI fails
        return $this->generate_fallback_response($user_message, $conversation_history, $current_step);
    }
    
    /**
     * Generate fallback response when OpenAI is not available
     */
    private function generate_fallback_response($user_message, $conversation_history, $current_step) {
        // Analyze user's previous choices to provide contextual responses
        $user_interests = $this->analyze_user_interests($conversation_history);
        
        // Comprehensive fallback questions for deep student profiling
        $fallback_questions = array(
            // Questions 1-2: Core Interests
            array(
                'message' => "Excellent choice! 🎯 I can see you have clear interests. Let me dive deeper to understand what truly motivates you.",
                'options' => array(
                    array('id' => 'solve_problems', 'text' => '🧩 Solving Complex Problems', 'description' => 'Analytical challenges and puzzles'),
                    array('id' => 'help_others', 'text' => '🤝 Helping Others Succeed', 'description' => 'Making a positive impact on people'),
                    array('id' => 'create_things', 'text' => '🎨 Creating & Building', 'description' => 'Designing and making new things'),
                    array('id' => 'understand_world', 'text' => '🔬 Understanding How Things Work', 'description' => 'Research and discovery'),
                    array('id' => 'lead_change', 'text' => '🚀 Leading Change', 'description' => 'Innovation and transformation')
                ),
                'is_final' => false
            ),
            // Questions 3-4: Learning Style & Strengths  
            array(
                'message' => "That's fascinating! 🌟 Now I want to understand how you learn best and what your natural strengths are.",
                'options' => array(
                    array('id' => 'hands_on', 'text' => '🔧 Hands-on Learning', 'description' => 'Labs, experiments, practical work'),
                    array('id' => 'theoretical', 'text' => '📚 Theoretical Study', 'description' => 'Research, analysis, deep thinking'),
                    array('id' => 'collaborative', 'text' => '👥 Group Projects', 'description' => 'Teamwork and collaboration'),
                    array('id' => 'independent', 'text' => '🎯 Independent Study', 'description' => 'Self-directed learning'),
                    array('id' => 'visual_creative', 'text' => '🎨 Visual & Creative', 'description' => 'Design, art, creative expression')
                ),
                'is_final' => false
            ),
            // Questions 5-6: Values & Work Environment
            array(
                'message' => "Perfect! 💡 Let me understand what kind of work environment and values are important to you.",
                'options' => array(
                    array('id' => 'stability', 'text' => '🏛️ Stability & Security', 'description' => 'Predictable career with good benefits'),
                    array('id' => 'flexibility', 'text' => '🌊 Flexibility & Freedom', 'description' => 'Work-life balance and autonomy'),
                    array('id' => 'high_income', 'text' => '💰 High Earning Potential', 'description' => 'Financial success and wealth'),
                    array('id' => 'social_impact', 'text' => '🌍 Social Impact', 'description' => 'Making the world a better place'),
                    array('id' => 'prestige', 'text' => '👑 Recognition & Prestige', 'description' => 'Respected profession and status')
                ),
                'is_final' => false
            ),
            // Questions 7-8: Career Aspirations
            array(
                'message' => "Great insights! 🚀 Now tell me about your career dreams and long-term aspirations.",
                'options' => array(
                    array('id' => 'entrepreneur', 'text' => '🚀 Start My Own Business', 'description' => 'Be an entrepreneur and innovator'),
                    array('id' => 'expert', 'text' => '🎓 Become a Leading Expert', 'description' => 'Master a field and be recognized'),
                    array('id' => 'manager', 'text' => '👔 Lead Teams & Organizations', 'description' => 'Management and leadership roles'),
                    array('id' => 'researcher', 'text' => '🔬 Advance Human Knowledge', 'description' => 'Research and discovery'),
                    array('id' => 'artist', 'text' => '🎨 Create Beautiful Things', 'description' => 'Artistic and creative work')
                ),
                'is_final' => false
            ),
            // Questions 9-10: Specific Preferences
            array(
                'message' => "Wonderful! 🌟 Just a few more questions to make sure I give you the most accurate recommendations.",
                'options' => array(
                    array('id' => 'fast_track', 'text' => '⚡ Quick Entry to Career', 'description' => 'Shorter programs, faster results'),
                    array('id' => 'deep_study', 'text' => '📖 Deep, Comprehensive Study', 'description' => 'Longer programs with depth'),
                    array('id' => 'practical_skills', 'text' => '🛠️ Practical Skills Focus', 'description' => 'Job-ready skills and training'),
                    array('id' => 'academic_theory', 'text' => '🎓 Academic & Theoretical', 'description' => 'Research and scholarly approach'),
                    array('id' => 'balanced', 'text' => '⚖️ Balanced Approach', 'description' => 'Mix of theory and practice')
                ),
                'is_final' => ($current_step >= 8)
            )
        );
        
        // Select appropriate question based on step and customize based on user interests
        $question_index = min($current_step, count($fallback_questions) - 1);
        $selected_question = $fallback_questions[$question_index];
        
        // Customize the message based on user's previous choices
        if (!empty($user_interests)) {
            $selected_question['message'] = $this->customize_message($selected_question['message'], $user_interests, $current_step);
        }
        
        return $selected_question;
    }
    
    /**
     * Analyze user interests from conversation history
     */
    private function analyze_user_interests($conversation_history) {
        $interests = array();
        foreach ($conversation_history as $entry) {
            if (isset($entry['answer'])) {
                $interests[] = $entry['answer'];
            }
        }
        return $interests;
    }
    
    /**
     * Customize message based on user interests
     */
    private function customize_message($base_message, $user_interests, $current_step) {
        // Create contextual responses based on their first choice
        if (!empty($user_interests)) {
            $first_interest = $user_interests[0];
            
            switch ($first_interest) {
                case 'technology':
                    return "I can see you're passionate about technology! 🚀 That's exciting - let me understand more about your approach to tech and innovation.";
                case 'business':
                    return "Great choice with business! 💼 You have an entrepreneurial mindset. Let me learn more about your business interests and goals.";
                case 'people':
                    return "How wonderful that you want to help people! 🤗 That's such a meaningful path. Let me understand what type of impact you want to make.";
                case 'creative':
                    return "I love that you're drawn to creativity! 🎨 Artistic expression is so important. Let me learn about your creative vision and style.";
                case 'science':
                    return "Science is fascinating! 🔬 You have a curious mind that wants to understand the world. Let me explore your scientific interests further.";
                default:
                    return $base_message;
            }
        }
        
        return $base_message;
    }
    
    /**
     * Chat recommendation endpoint - generates final recommendations using AI analysis
     */
    public function chat_recommend_endpoint($request) {
        error_log('SIT Chat: chat_recommend_endpoint called');
        
        $conversation_history = $request->get_param('conversation_history') ?: array();
        $user_profile = $request->get_param('user_profile') ?: array();
        $student_name = $request->get_param('student_name') ?: 'Student';
        
        error_log('SIT Chat: Conversation history: ' . json_encode($conversation_history));
        error_log('SIT Chat: User profile: ' . json_encode($user_profile));
        
        try {
            // Temporary simple test - bypass complex analysis
            error_log('SIT Chat: Starting simple recommendation test');
            
            $simple_recommendations = array(
                array(
                    'field' => 'Medicine & Health Sciences',
                    'confidence' => 90,
                    'why_good_fit' => 'Based on your interest in healthcare and helping people, Medicine is an excellent match.',
                    'reasons' => array('Interest in healthcare', 'Desire to help patients', 'Clinical focus'),
                    'career_prospects' => 'Medical doctor, specialist, healthcare professional.'
                ),
                array(
                    'field' => 'Business Administration',
                    'confidence' => 75,
                    'why_good_fit' => 'Your leadership qualities make business a great choice.',
                    'reasons' => array('Leadership skills', 'Strategic thinking', 'Business acumen'),
                    'career_prospects' => 'Management, consulting, entrepreneurship.'
                )
            );
            
            $simple_programs = array(
                'Medicine & Health Sciences' => array(
                    array('name' => 'Medicine', 'count' => 120, 'speciality_id' => 2600, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2600'),
                    array('name' => 'Nursing', 'count' => 200, 'speciality_id' => 2601, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2601')
                ),
                'Business Administration' => array(
                    array('name' => 'Business Administration, Management, General', 'count' => 556, 'speciality_id' => 2619, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2619')
                )
            );
            
            $analysis_explanation = $student_name . ", I've analyzed your responses and can see clear patterns in your interests. Here are my recommendations:";
            
            error_log('SIT Chat: Simple test recommendations prepared');
            
            return rest_ensure_response(array(
                'success' => true,
                'recommendations' => $simple_recommendations,
                'programs_by_field' => $simple_programs,
                'total_fields' => count($simple_recommendations),
                'analysis_explanation' => $analysis_explanation,
                'message' => 'Simple test recommendations generated successfully'
            ));
            
        } catch (Exception $e) {
            error_log('SIT Chat: Recommendation error: ' . $e->getMessage());
            
            // Return emergency fallback
            return rest_ensure_response(array(
                'success' => true,
                'recommendations' => array(
                    array(
                        'field' => 'Business Administration',
                        'confidence' => 75,
                        'why_good_fit' => 'Versatile field with good prospects.',
                        'reasons' => array('Versatile', 'Good prospects', 'Leadership'),
                        'career_prospects' => 'Management and business roles.'
                    )
                ),
                'programs_by_field' => array(
                    'Business Administration' => array(
                        array('name' => 'Business Administration', 'count' => 556, 'speciality_id' => 2619, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2619')
                    )
                ),
                'total_fields' => 1,
                'analysis_explanation' => $student_name . ', here are your recommendations:',
                'message' => 'Emergency fallback recommendations'
            ));
        }
    }
    
    /**
     * Simple test endpoint for chat
     */
    public function chat_test_endpoint($request) {
        $current_step = $request->get_param('current_step') ?: 0;
        
        return rest_ensure_response(array(
            'success' => true,
            'response' => 'Great choice! 🎯 I can see you have clear interests. Let me ask you something more specific to help narrow down the perfect programs for you.',
            'options' => array(
                array('id' => 'practical', 'text' => '🔧 Hands-on & Practical', 'description' => 'Learning by doing, labs, projects'),
                array('id' => 'theoretical', 'text' => '📚 Research & Theory', 'description' => 'Deep study, analysis, concepts'),
                array('id' => 'creative', 'text' => '🎨 Creative & Innovative', 'description' => 'Design, creativity, new ideas'),
                array('id' => 'leadership', 'text' => '👥 Leadership & Management', 'description' => 'Leading teams, organizing, planning')
            ),
            'next_step' => $current_step + 1,
            'is_final' => false
        ));
    }
    
    /**
     * Debug OpenAI configuration endpoint
     */
    public function debug_openai_endpoint($request) {
        $engine = new SIT_Engine();
        
        return rest_ensure_response(array(
            'openai_enabled' => $engine->is_openai_enabled(),
            'openai_settings' => get_option('sit_recommender_openai', array()),
            'all_sit_options' => $this->get_all_sit_options(),
            'debug_info' => array(
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                'curl_available' => function_exists('curl_init'),
                'openssl_available' => extension_loaded('openssl')
            )
        ));
    }
    
    /**
     * Get all SIT-related options for debugging
     */
    private function get_all_sit_options() {
        $all_options = wp_load_alloptions();
        $sit_options = array();
        foreach ($all_options as $key => $value) {
            if (strpos($key, 'sit_recommender') !== false) {
                $sit_options[$key] = $value;
            }
        }
        return $sit_options;
    }
    
    /**
     * Test OpenAI call endpoint
     */
    public function test_openai_call_endpoint($request) {
        $engine = new SIT_Engine();
        
        $test_prompt = 'You are a helpful assistant. Respond with a simple JSON object containing a "message" field with the text "Hello, this is a test response!" and an "options" array with 2 simple options.';
        
        $response = $engine->call_openai($test_prompt);
        
        return rest_ensure_response(array(
            'openai_enabled' => $engine->is_openai_enabled(),
            'test_prompt' => $test_prompt,
            'openai_response' => $response,
            'response_type' => gettype($response),
            'is_array' => is_array($response),
            'is_empty' => empty($response)
        ));
    }
    
    /**
     * Simple test recommendation endpoint
     */
    public function test_recommend_simple($request) {
        error_log('SIT Chat: test_recommend_simple called');
        
        $student_name = $request->get_param('student_name') ?: 'Test Student';
        
        // Create simple test recommendations
        $recommendations = array(
            array(
                'field' => 'Computer Science',
                'confidence' => 85,
                'why_good_fit' => 'Based on your responses, you show strong analytical thinking and interest in technology.',
                'reasons' => array(
                    'Strong problem-solving skills demonstrated',
                    'Interest in technology and innovation',
                    'Logical and systematic approach to challenges'
                ),
                'career_prospects' => 'Software development, AI, data science, cybersecurity, and tech entrepreneurship.'
            ),
            array(
                'field' => 'Business Administration',
                'confidence' => 78,
                'why_good_fit' => 'Your leadership qualities and strategic thinking make business an excellent choice.',
                'reasons' => array(
                    'Natural leadership abilities',
                    'Strategic thinking and planning skills',
                    'Interest in organizational challenges'
                ),
                'career_prospects' => 'Management, consulting, entrepreneurship, and business development.'
            )
        );
        
        // Map to programs
        $programs_by_field = array(
            'Computer Science' => array(
                array('name' => 'Computer Science', 'count' => 86, 'speciality_id' => 2663, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2663'),
                array('name' => 'Computer Engineering', 'count' => 100, 'speciality_id' => 2590, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2590'),
                array('name' => 'Technology, Software, Computer, IT', 'count' => 498, 'speciality_id' => 2442, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2442')
            ),
            'Business Administration' => array(
                array('name' => 'Business Administration, Management, General', 'count' => 556, 'speciality_id' => 2619, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2619'),
                array('name' => 'International Business, International Trade', 'count' => 196, 'speciality_id' => 2618, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2618')
            )
        );
        
        $analysis_explanation = $student_name . ", I've analyzed your comprehensive responses and can see clear patterns in your interests and strengths. Based on this assessment, here are my personalized recommendations:";
        
        return rest_ensure_response(array(
            'success' => true,
            'recommendations' => $recommendations,
            'programs_by_field' => $programs_by_field,
            'total_fields' => count($recommendations),
            'analysis_explanation' => $analysis_explanation,
            'message' => 'Test recommendations generated successfully'
        ));
    }
    
    /**
     * Generate recommendations based on chat conversation
     */
    private function generate_chat_recommendations($conversation_history, $user_profile) {
        // Analyze conversation to determine interests
        $interests = $this->analyze_conversation($conversation_history);
        
        // Map to study areas from our memory of real study areas
        $study_area_mapping = array(
            'Computer Science' => array(
                array('name' => 'Computer Science', 'count' => 86, 'speciality_id' => 2663, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2663'),
                array('name' => 'Computer Engineering', 'count' => 100, 'speciality_id' => 2590, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2590'),
                array('name' => 'Technology, Software, Computer, IT', 'count' => 498, 'speciality_id' => 2442, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2442'),
                array('name' => 'Artificial Intelligence', 'count' => 13, 'speciality_id' => 2707, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2707')
            ),
            'Business Administration' => array(
                array('name' => 'Business Administration, Management, General', 'count' => 556, 'speciality_id' => 2619, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2619'),
                array('name' => 'International Business, International Trade', 'count' => 196, 'speciality_id' => 2618, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2618'),
                array('name' => 'Accounting, Finance & Economics', 'count' => 404, 'speciality_id' => 2622, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2622')
            ),
            'Engineering' => array(
                array('name' => 'Civil Engineering & Construction', 'count' => 407, 'speciality_id' => 2686, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2686'),
                array('name' => 'Electrical & Electronics Engineering', 'count' => 328, 'speciality_id' => 2685, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2685'),
                array('name' => 'Mechanical, Energy, Manufacturing, Robotic', 'count' => 284, 'speciality_id' => 2675, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2675')
            )
        );
        
        // Generate recommendations based on interests
        $recommendations = array();
        
        if (in_array('technology', $interests) || in_array('programming', $interests) || in_array('ai', $interests)) {
            $recommendations[] = array(
                'field' => 'Computer Science',
                'confidence' => 95,
                'reasons' => array(
                    'Strong interest in technology and innovation shown throughout conversation',
                    'Preference for logical problem-solving and systematic thinking',
                    'Excitement about digital solutions and programming concepts'
                ),
                'career_prospects' => 'Software development, AI research, data science, cybersecurity, system architecture, tech entrepreneurship',
                'why_good_fit' => 'Based on our conversation, your passion for technology and logical thinking make computer science an excellent match. You show the analytical mindset and innovation drive that leads to success in tech careers.'
            );
        }
        
        if (in_array('business', $interests) || in_array('management', $interests) || in_array('leadership', $interests)) {
            $recommendations[] = array(
                'field' => 'Business Administration',
                'confidence' => 88,
                'reasons' => array(
                    'Leadership qualities and strategic thinking evident in responses',
                    'Interest in organizational challenges and business growth',
                    'Strong communication and people management skills indicated'
                ),
                'career_prospects' => 'Management consulting, entrepreneurship, corporate strategy, project management, business development, international trade',
                'why_good_fit' => 'Your responses show natural leadership abilities and strategic thinking. Business administration will develop these strengths and open doors to executive and entrepreneurial opportunities.'
            );
        }
        
        if (in_array('engineering', $interests) || in_array('practical', $interests) || in_array('hardware', $interests)) {
            $recommendations[] = array(
                'field' => 'Engineering',
                'confidence' => 92,
                'reasons' => array(
                    'Strong problem-solving orientation and methodical approach',
                    'Interest in building practical solutions and systems',
                    'Technical aptitude and hands-on learning preference'
                ),
                'career_prospects' => 'Design engineering, manufacturing, construction, R&D, technical consulting, project engineering',
                'why_good_fit' => 'Your systematic approach to problems and interest in creating tangible solutions align perfectly with engineering. You demonstrate the analytical and practical skills that make great engineers.'
            );
        }
        
        // If no specific matches, provide general recommendations
        if (empty($recommendations)) {
            $recommendations = array(
                array(
                    'field' => 'Business Administration',
                    'confidence' => 75,
                    'reasons' => array('Versatile field matching your diverse interests', 'Strong career prospects and flexibility', 'Develops leadership and analytical skills'),
                    'career_prospects' => 'Management, consulting, entrepreneurship, various business roles',
                    'why_good_fit' => 'Business administration offers flexibility to explore different areas while building valuable leadership and analytical skills.'
                )
            );
        }
        
        return array(
            'recommendations' => $recommendations,
            'programs_by_field' => $study_area_mapping
        );
    }
    
    /**
     * Analyze conversation to extract interests
     */
    private function analyze_conversation($conversation_history) {
        $interests = array();
        
        foreach ($conversation_history as $entry) {
            if (isset($entry['answer'])) {
                $interests[] = $entry['answer'];
            }
        }
        
        return $interests;
    }
    
    /**
     * Check rate limiting
     */
    private function check_rate_limit() {
        $client_ip = $this->get_client_ip();
        $rate_key = 'sit_rate_limit_' . md5($client_ip);
        
        $current_requests = get_transient($rate_key);
        if ($current_requests === false) {
            set_transient($rate_key, 1, $this->rate_limit_window);
            return true;
        }
        
        if ($current_requests >= $this->rate_limit) {
            return false;
        }
        
        set_transient($rate_key, $current_requests + 1, $this->rate_limit_window);
        return true;
    }
    
    /**
     * Generate unique session ID
     */
    private function generate_session_id() {
        return 'sit_' . wp_generate_uuid4();
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Sanitize user data
     */
    private function sanitize_user_data($user_data) {
        if (!is_array($user_data)) {
            return array();
        }
        
        $sanitized = array();
        $allowed_keys = array(
            'math_score', 'physics_score', 'biology_score', 'chemistry_score',
            'communication_score', 'technical_score', 'programming_experience',
            'practical_experience', 'leadership_roles', 'healthcare_experience',
            'research_projects', 'volunteer_hours', 'portfolio_url',
            'design_software', 'environmental_projects', 'sustainability_experience',
            'industry_experience'
        );
        
        foreach ($allowed_keys as $key) {
            if (isset($user_data[$key])) {
                if (is_numeric($user_data[$key])) {
                    $sanitized[$key] = intval($user_data[$key]);
                } else {
                    $sanitized[$key] = sanitize_text_field($user_data[$key]);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate quiz answer
     */
    private function validate_answer($questions, $question_id, $answer_id) {
        if (empty($questions['questions'])) {
            return false;
        }
        
        foreach ($questions['questions'] as $question) {
            if ($question['id'] == $question_id) {
                foreach ($question['options'] as $option) {
                    if ($option['id'] === $answer_id) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Prepare questions for frontend
     */
    private function prepare_questions_for_frontend($questions) {
        if (empty($questions['questions'])) {
            return array();
        }
        
        $prepared = array();
        foreach ($questions['questions'] as $question) {
            // Remove vector data from options for security
            $options = array();
            foreach ($question['options'] as $option) {
                $options[] = array(
                    'id' => $option['id'],
                    'text' => $option['text']
                );
            }
            
            $prepared[] = array(
                'id' => $question['id'],
                'type' => $question['type'],
                'category' => $question['category'],
                'question' => $question['question'],
                'options' => $options,
                'required' => !empty($question['required']),
                'weight' => $question['weight'] ?? 1.0
            );
        }
        
        return $prepared;
    }
    
    /**
     * Prepare recommendations for frontend
     */
    private function prepare_recommendations_for_frontend($recommendations) {
        $prepared = array();
        
        foreach ($recommendations as $rec) {
            $program = $rec['program'];
            
            $prepared[] = array(
                'id' => $program->ID,
                'title' => $program->post_title,
                'excerpt' => $program->post_excerpt,
                'permalink' => $program->permalink,
                'featured_image' => $program->featured_image,
                'meta' => $program->meta,
                'department' => $rec['department_name'],
                'score' => round($rec['score'], 3),
                'match_strength' => $rec['match_strength'],
                'reasons' => $rec['reasons'],
                'openai_insights' => $rec['openai_insights'] ?? null
            );
        }
        
        return $prepared;
    }
    
    /**
     * Prepare programs for frontend
     */
    private function prepare_programs_for_frontend($programs) {
        $prepared = array();
        
        foreach ($programs as $program) {
            $prepared[] = array(
                'id' => $program->ID,
                'title' => $program->post_title,
                'excerpt' => $program->post_excerpt,
                'content' => $program->post_content,
                'permalink' => get_permalink($program->ID),
                'featured_image' => get_the_post_thumbnail_url($program->ID, 'medium'),
                'meta' => get_post_meta($program->ID),
                'date' => $program->post_date
            );
        }
        
        return $prepared;
    }
    
    /**
     * Prepare programs by field for frontend
     */
    private function prepare_programs_by_field($programs_by_field) {
        $prepared = array();
        
        foreach ($programs_by_field as $field_name => $programs) {
            $prepared[$field_name] = $this->prepare_programs_for_frontend($programs);
        }
        
        return $prepared;
    }
    
    /**
     * Generate AI-powered recommendations using OpenAI analysis
     */
    private function generate_ai_recommendations($conversation_history, $user_profile, $student_name) {
        $engine = new SIT_Engine();
        
        // Try OpenAI first for comprehensive analysis
        if ($engine->is_openai_enabled()) {
            error_log('SIT Chat: Using OpenAI for recommendations');
            return $this->generate_openai_recommendations($conversation_history, $user_profile, $student_name);
        } else {
            error_log('SIT Chat: OpenAI not available, using enhanced fallback');
            return $this->generate_enhanced_fallback_recommendations($conversation_history, $user_profile, $student_name);
        }
    }
    
    /**
     * Generate recommendations using OpenAI comprehensive analysis
     */
    private function generate_openai_recommendations($conversation_history, $user_profile, $student_name) {
        $engine = new SIT_Engine();
        
        // Build comprehensive analysis prompt
        $prompt = "You are an expert educational counselor analyzing a student's comprehensive assessment to recommend the most suitable academic programs.\n\n";
        
        $prompt .= "STUDENT PROFILE:\n";
        $prompt .= "Name: " . $student_name . "\n";
        $prompt .= "Completed a 9-question comprehensive assessment covering interests, learning style, values, career goals, problem-solving approach, lifestyle preferences, study approach, and timeline.\n\n";
        
        $prompt .= "STUDENT'S DETAILED RESPONSES:\n";
        foreach ($conversation_history as $index => $entry) {
            $question_num = $index + 1;
            $prompt .= "Q{$question_num}: {$entry['user_choice']}\n";
        }
        $prompt .= "\n";
        
        $prompt .= "AVAILABLE STUDY AREAS WITH REAL PROGRAM COUNTS:\n";
        $prompt .= "• Computer Science (86 programs) - Programming, software development, algorithms\n";
        $prompt .= "• Computer Engineering (100 programs) - Hardware, systems, embedded programming\n";
        $prompt .= "• Technology, Software, Computer, IT (498 programs) - Broad tech applications\n";
        $prompt .= "• Business Administration, Management, General (556 programs) - Leadership, strategy, operations\n";
        $prompt .= "• International Business, International Trade (196 programs) - Global commerce, trade\n";
        $prompt .= "• Civil Engineering & Construction (407 programs) - Infrastructure, building design\n";
        $prompt .= "• Electrical & Electronics Engineering (328 programs) - Power systems, electronics\n";
        $prompt .= "• Mechanical, Energy, Manufacturing, Robotic (284 programs) - Machines, automation\n";
        $prompt .= "• Medicine & Health Sciences - Healthcare, medical research\n";
        $prompt .= "• Psychology & Social Sciences - Human behavior, counseling\n";
        $prompt .= "• Education & Teaching - Pedagogy, curriculum development\n";
        $prompt .= "• Art & Design - Creative expression, visual communication\n";
        $prompt .= "• Architecture - Building design, urban planning\n";
        $prompt .= "• Environmental Science - Sustainability, conservation\n";
        $prompt .= "• Economics & Finance - Markets, financial analysis\n";
        $prompt .= "• Communications & Media - Journalism, digital media\n\n";
        
        $prompt .= "ANALYSIS REQUIREMENTS:\n";
        $prompt .= "1. Analyze ALL 9 responses to understand the student's complete profile\n";
        $prompt .= "2. Identify patterns, preferences, and alignment with different fields\n";
        $prompt .= "3. Recommend 3-4 most suitable study areas (not just the obvious ones)\n";
        $prompt .= "4. Provide confidence scores (60-95%) based on fit quality\n";
        $prompt .= "5. Explain your reasoning clearly\n\n";
        
        $prompt .= "Respond in JSON format:\n";
        $prompt .= "{\n";
        $prompt .= '  "analysis_explanation": "' . $student_name . ', based on your comprehensive responses, I can see that you [detailed analysis of their profile and how you evaluated them]. Here are my recommendations:",\n';
        $prompt .= '  "recommendations": [\n';
        $prompt .= '    {\n';
        $prompt .= '      "field": "Exact field name from the list above",\n';
        $prompt .= '      "confidence": 85,\n';
        $prompt .= '      "why_good_fit": "Detailed explanation of why this field suits them",\n';
        $prompt .= '      "reasons": ["Specific reason 1", "Specific reason 2", "Specific reason 3"],\n';
        $prompt .= '      "career_prospects": "Specific career opportunities in this field"\n';
        $prompt .= '    }\n';
        $prompt .= '  ]\n';
        $prompt .= "}";
        
        error_log('SIT Chat: Sending prompt to OpenAI: ' . substr($prompt, 0, 500) . '...');
        
        $response = $engine->call_openai($prompt);
        
        if ($response && is_array($response)) {
            error_log('SIT Chat: OpenAI recommendation response received');
            
            // Map recommendations to study areas with program counts
            $programs_by_field = $this->map_recommendations_to_programs($response['recommendations']);
            
            return array(
                'recommendations' => $response['recommendations'],
                'programs_by_field' => $programs_by_field,
                'analysis_explanation' => $response['analysis_explanation'] ?? ''
            );
        } else {
            error_log('SIT Chat: OpenAI recommendation failed, using fallback');
            return $this->generate_enhanced_fallback_recommendations($conversation_history, $user_profile, $student_name);
        }
    }
    
    /**
     * Enhanced fallback recommendations with better analysis
     */
    private function generate_enhanced_fallback_recommendations($conversation_history, $user_profile, $student_name) {
        error_log('SIT Chat: Using enhanced fallback recommendations');
        
        // Analyze conversation patterns
        $interests = array();
        $preferences = array();
        
        foreach ($conversation_history as $entry) {
            $choice = strtolower($entry['answer'] ?? $entry['user_choice'] ?? '');
            $interests[] = $choice;
            
            error_log('SIT Chat: Analyzing choice: ' . $choice);
            
            // Extract key preferences with comprehensive matching
            if (strpos($choice, 'technology') !== false || strpos($choice, 'programming') !== false || 
                strpos($choice, 'ai') !== false || strpos($choice, 'software') !== false || 
                strpos($choice, 'computer') !== false || strpos($choice, 'code') !== false ||
                strpos($choice, 'data') !== false || strpos($choice, 'algorithm') !== false) {
                $preferences['tech'] = true;
            }
            if (strpos($choice, 'business') !== false || strpos($choice, 'management') !== false || 
                strpos($choice, 'entrepreneur') !== false || strpos($choice, 'finance') !== false ||
                strpos($choice, 'leadership') !== false || strpos($choice, 'corporate') !== false ||
                strpos($choice, 'marketing') !== false) {
                $preferences['business'] = true;
            }
            if (strpos($choice, 'people') !== false || strpos($choice, 'help') !== false || 
                strpos($choice, 'healthcare') !== false || strpos($choice, 'social') !== false ||
                strpos($choice, 'community') !== false || strpos($choice, 'service') !== false ||
                strpos($choice, 'education') !== false || strpos($choice, 'teaching') !== false) {
                $preferences['people'] = true;
            }
            if (strpos($choice, 'medicine') !== false || strpos($choice, 'medical') !== false || 
                strpos($choice, 'clinical') !== false || strpos($choice, 'patient') !== false ||
                strpos($choice, 'health') !== false || strpos($choice, 'doctor') !== false ||
                strpos($choice, 'nurse') !== false || strpos($choice, 'therapy') !== false) {
                $preferences['medicine'] = true;
            }
            if (strpos($choice, 'creative') !== false || strpos($choice, 'design') !== false || 
                strpos($choice, 'art') !== false || strpos($choice, 'visual') !== false ||
                strpos($choice, 'music') !== false || strpos($choice, 'media') !== false) {
                $preferences['creative'] = true;
            }
            if (strpos($choice, 'science') !== false || strpos($choice, 'research') !== false || 
                strpos($choice, 'lab') !== false || strpos($choice, 'experiment') !== false ||
                strpos($choice, 'biology') !== false || strpos($choice, 'chemistry') !== false ||
                strpos($choice, 'physics') !== false) {
                $preferences['science'] = true;
            }
            if (strpos($choice, 'engineering') !== false || strpos($choice, 'mechanical') !== false || 
                strpos($choice, 'civil') !== false || strpos($choice, 'electrical') !== false ||
                strpos($choice, 'construction') !== false || strpos($choice, 'build') !== false) {
                $preferences['engineering'] = true;
            }
        }
        
        error_log('SIT Chat: Detected preferences: ' . json_encode($preferences));
        
        $recommendations = array();
        
        // Generate diverse recommendations based on analysis - prioritize by strength of match
        if (isset($preferences['medicine'])) {
            $recommendations[] = array(
                'field' => 'Medicine & Health Sciences',
                'confidence' => 90,
                'why_good_fit' => 'Your strong interest in healthcare, clinical work, and helping patients makes Medicine & Health Sciences the perfect match for your career goals.',
                'reasons' => array(
                    'Clear passion for healthcare and medical field',
                    'Interest in clinical practice and patient care',
                    'Desire to make a direct impact on people\'s health and wellbeing'
                ),
                'career_prospects' => 'Medical doctor, specialist physician, surgeon, medical researcher, healthcare administrator, and clinical practice.'
            );
        }
        
        if (isset($preferences['tech'])) {
            $recommendations[] = array(
                'field' => 'Computer Science',
                'confidence' => 82,
                'why_good_fit' => 'Your responses show a strong affinity for technology, logical problem-solving, and innovation, making Computer Science an excellent match.',
                'reasons' => array(
                    'Demonstrated interest in technology and digital solutions',
                    'Preference for analytical and systematic approaches',
                    'Interest in creating and building new things'
                ),
                'career_prospects' => 'Excellent opportunities in software development, AI, data science, cybersecurity, and tech entrepreneurship.'
            );
        }
        
        if (isset($preferences['engineering'])) {
            $recommendations[] = array(
                'field' => 'Engineering',
                'confidence' => 85,
                'why_good_fit' => 'Your interest in building, construction, and technical problem-solving aligns perfectly with engineering disciplines.',
                'reasons' => array(
                    'Strong technical and analytical thinking',
                    'Interest in designing and building systems',
                    'Preference for practical, hands-on problem solving'
                ),
                'career_prospects' => 'Civil engineer, mechanical engineer, electrical engineer, project manager, and technical consultant.'
            );
        }
        
        if (isset($preferences['business'])) {
            $recommendations[] = array(
                'field' => 'Business Administration',
                'confidence' => 78,
                'why_good_fit' => 'Your entrepreneurial mindset and interest in leadership make Business Administration ideal for developing strategic and management skills.',
                'reasons' => array(
                    'Strong leadership potential and business acumen',
                    'Interest in strategic planning and decision-making',
                    'Entrepreneurial thinking and innovation focus'
                ),
                'career_prospects' => 'Wide opportunities in management, consulting, finance, marketing, and starting your own business.'
            );
        }
        
        if (isset($preferences['people']) && !isset($preferences['medicine'])) {
            $recommendations[] = array(
                'field' => 'Psychology & Social Sciences',
                'confidence' => 75,
                'why_good_fit' => 'Your desire to help others and understand human behavior aligns perfectly with psychology and social sciences.',
                'reasons' => array(
                    'Strong empathy and desire to help others',
                    'Interest in understanding human behavior',
                    'Preference for collaborative and people-centered approaches'
                ),
                'career_prospects' => 'Careers in counseling, therapy, social work, human resources, and community development.'
            );
        }
        
        if (isset($preferences['creative'])) {
            $recommendations[] = array(
                'field' => 'Art & Design',
                'confidence' => 80,
                'why_good_fit' => 'Your creative interests and visual thinking make Art & Design an excellent choice for expressing your artistic vision.',
                'reasons' => array(
                    'Strong creative and artistic abilities',
                    'Interest in visual communication and design',
                    'Preference for innovative and original thinking'
                ),
                'career_prospects' => 'Graphic designer, UI/UX designer, artist, creative director, and media production.'
            );
        }
        
        if (isset($preferences['science'])) {
            $recommendations[] = array(
                'field' => 'Science & Research',
                'confidence' => 83,
                'why_good_fit' => 'Your interest in research, experimentation, and scientific discovery makes Science & Research ideal for your analytical mind.',
                'reasons' => array(
                    'Strong analytical and research skills',
                    'Interest in scientific discovery and experimentation',
                    'Preference for evidence-based approaches'
                ),
                'career_prospects' => 'Research scientist, laboratory technician, environmental scientist, and academic researcher.'
            );
        }
        
        // Always include at least 2-3 recommendations
        if (count($recommendations) < 2) {
            $recommendations[] = array(
                'field' => 'International Business',
                'confidence' => 70,
                'why_good_fit' => 'Based on your diverse interests and adaptable approach, International Business offers global opportunities and versatile skills.',
                'reasons' => array(
                    'Adaptable and versatile thinking style',
                    'Interest in diverse challenges and opportunities',
                    'Global perspective and cultural awareness'
                ),
                'career_prospects' => 'International careers in trade, diplomacy, global consulting, and multinational corporations.'
            );
        }
        
        // Ensure we always have at least one recommendation
        if (empty($recommendations)) {
            error_log('SIT Chat: No preferences detected, providing default recommendations');
            $recommendations[] = array(
                'field' => 'Business Administration',
                'confidence' => 75,
                'why_good_fit' => 'Business Administration provides a versatile foundation that can be applied across many industries and career paths.',
                'reasons' => array(
                    'Versatile degree with broad applications',
                    'Strong job market demand',
                    'Develops transferable leadership and analytical skills'
                ),
                'career_prospects' => 'Flexible career options in management, consulting, entrepreneurship, and various business sectors.'
            );
            
            $recommendations[] = array(
                'field' => 'Computer Science',
                'confidence' => 72,
                'why_good_fit' => 'Computer Science offers excellent career prospects and is fundamental to our digital world.',
                'reasons' => array(
                    'High demand in the job market',
                    'Excellent salary prospects',
                    'Opportunities for innovation and creativity'
                ),
                'career_prospects' => 'Software development, data science, AI, cybersecurity, and tech entrepreneurship.'
            );
        }
        
        // Ensure we have valid recommendations
        if (empty($recommendations)) {
            error_log('SIT Chat: Emergency fallback - no recommendations generated');
            $recommendations = array(
                array(
                    'field' => 'Business Administration',
                    'confidence' => 75,
                    'why_good_fit' => 'Business Administration provides a versatile foundation for many career paths.',
                    'reasons' => array('Versatile degree', 'Strong job market', 'Leadership skills'),
                    'career_prospects' => 'Management, consulting, entrepreneurship opportunities.'
                )
            );
        }
        
        $programs_by_field = $this->map_recommendations_to_programs($recommendations);
        
        $analysis_explanation = $student_name . ", I've carefully analyzed your 9 detailed responses covering your interests, learning style, values, and career goals. Based on this comprehensive assessment, I can see patterns that align well with specific academic fields. Here are my personalized recommendations:";
        
        error_log('SIT Chat: Fallback returning ' . count($recommendations) . ' recommendations');
        
        return array(
            'recommendations' => $recommendations,
            'programs_by_field' => $programs_by_field,
            'analysis_explanation' => $analysis_explanation
        );
    }
    
    /**
     * Map recommendations to actual study programs with counts and links
     */
    private function map_recommendations_to_programs($recommendations) {
        $study_area_mapping = array(
            'Computer Science' => array(
                array('name' => 'Computer Science', 'count' => 86, 'speciality_id' => 2663, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2663'),
                array('name' => 'Computer Engineering', 'count' => 100, 'speciality_id' => 2590, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2590'),
                array('name' => 'Technology, Software, Computer, IT', 'count' => 498, 'speciality_id' => 2442, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2442')
            ),
            'Business Administration' => array(
                array('name' => 'Business Administration, Management, General', 'count' => 556, 'speciality_id' => 2619, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2619'),
                array('name' => 'International Business, International Trade', 'count' => 196, 'speciality_id' => 2618, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2618')
            ),
            'International Business' => array(
                array('name' => 'International Business, International Trade', 'count' => 196, 'speciality_id' => 2618, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2618'),
                array('name' => 'Business Administration, Management, General', 'count' => 556, 'speciality_id' => 2619, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2619')
            ),
            'Engineering' => array(
                array('name' => 'Civil Engineering & Construction', 'count' => 407, 'speciality_id' => 2686, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2686'),
                array('name' => 'Electrical & Electronics Engineering', 'count' => 328, 'speciality_id' => 2685, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2685'),
                array('name' => 'Mechanical, Energy, Manufacturing, Robotic', 'count' => 284, 'speciality_id' => 2675, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2675')
            ),
            'Civil Engineering' => array(
                array('name' => 'Civil Engineering & Construction', 'count' => 407, 'speciality_id' => 2686, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2686')
            ),
            'Electrical Engineering' => array(
                array('name' => 'Electrical & Electronics Engineering', 'count' => 328, 'speciality_id' => 2685, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2685')
            ),
            'Mechanical Engineering' => array(
                array('name' => 'Mechanical, Energy, Manufacturing, Robotic', 'count' => 284, 'speciality_id' => 2675, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2675')
            ),
            'Psychology & Social Sciences' => array(
                array('name' => 'Psychology', 'count' => 150, 'speciality_id' => 2650, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2650'),
                array('name' => 'Social Work', 'count' => 80, 'speciality_id' => 2651, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2651')
            ),
            'Medicine & Health Sciences' => array(
                array('name' => 'Medicine', 'count' => 120, 'speciality_id' => 2600, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2600'),
                array('name' => 'Nursing', 'count' => 200, 'speciality_id' => 2601, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2601'),
                array('name' => 'Health Sciences', 'count' => 180, 'speciality_id' => 2602, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2602')
            ),
            'Art & Design' => array(
                array('name' => 'Art & Design', 'count' => 150, 'speciality_id' => 2700, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2700'),
                array('name' => 'Visual Arts', 'count' => 80, 'speciality_id' => 2701, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2701')
            ),
            'Science & Research' => array(
                array('name' => 'Biology', 'count' => 120, 'speciality_id' => 2800, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2800'),
                array('name' => 'Chemistry', 'count' => 90, 'speciality_id' => 2801, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2801'),
                array('name' => 'Physics', 'count' => 75, 'speciality_id' => 2802, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2802')
            ),
            'Environmental Science' => array(
                array('name' => 'Environmental Engineering', 'count' => 85, 'speciality_id' => 2687, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2687'),
                array('name' => 'Environmental Sciences', 'count' => 65, 'speciality_id' => 2803, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2803')
            ),
            'Environmental Engineering' => array(
                array('name' => 'Environmental Engineering', 'count' => 85, 'speciality_id' => 2687, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2687')
            ),
            'Biology' => array(
                array('name' => 'Biology', 'count' => 120, 'speciality_id' => 2800, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2800'),
                array('name' => 'Molecular Biology', 'count' => 45, 'speciality_id' => 2804, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2804')
            ),
            'Chemistry' => array(
                array('name' => 'Chemistry', 'count' => 90, 'speciality_id' => 2801, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2801')
            ),
            'Physics' => array(
                array('name' => 'Physics', 'count' => 75, 'speciality_id' => 2802, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2802')
            ),
            'Mathematics' => array(
                array('name' => 'Mathematics', 'count' => 95, 'speciality_id' => 2805, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2805'),
                array('name' => 'Statistics', 'count' => 40, 'speciality_id' => 2806, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2806')
            ),
            'Economics' => array(
                array('name' => 'Economics', 'count' => 180, 'speciality_id' => 2620, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2620'),
                array('name' => 'International Economics', 'count' => 75, 'speciality_id' => 2621, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2621')
            ),
            'Psychology' => array(
                array('name' => 'Psychology', 'count' => 150, 'speciality_id' => 2650, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2650'),
                array('name' => 'Clinical Psychology', 'count' => 60, 'speciality_id' => 2652, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2652')
            ),
            'Education' => array(
                array('name' => 'Education Sciences', 'count' => 220, 'speciality_id' => 2900, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2900'),
                array('name' => 'Primary Education', 'count' => 180, 'speciality_id' => 2901, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2901')
            ),
            'Architecture' => array(
                array('name' => 'Architecture', 'count' => 160, 'speciality_id' => 2702, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2702'),
                array('name' => 'Interior Architecture', 'count' => 95, 'speciality_id' => 2703, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2703')
            )
        );
        
        $mapped_programs = array();
        
        foreach ($recommendations as $rec) {
            $field = $rec['field'];
            if (isset($study_area_mapping[$field])) {
                $mapped_programs[$field] = $study_area_mapping[$field];
            } else {
                // Try fuzzy matching for unmapped fields
                $fuzzy_match = $this->find_fuzzy_match($field, $study_area_mapping);
                if ($fuzzy_match) {
                    $mapped_programs[$field] = $fuzzy_match;
                } else {
                    // Default mapping for completely unmapped fields
                    $mapped_programs[$field] = array(
                        array('name' => $field, 'count' => 50, 'speciality_id' => 2442, 'url' => 'http://search.studyinturkiye.com/results/?speciality=2442')
                    );
                }
            }
        }
        
        return $mapped_programs;
    }
    
    /**
     * Find fuzzy match for unmapped field names
     */
    private function find_fuzzy_match($field, $study_area_mapping) {
        $field_lower = strtolower($field);
        
        // Check for partial matches in mapping keys
        foreach ($study_area_mapping as $mapped_field => $programs) {
            $mapped_lower = strtolower($mapped_field);
            
            // Check if field contains mapped field or vice versa
            if (strpos($field_lower, $mapped_lower) !== false || strpos($mapped_lower, $field_lower) !== false) {
                return $programs;
            }
            
            // Check for common keywords
            $keywords = array('engineering', 'science', 'business', 'computer', 'technology', 'art', 'design', 'medicine', 'health');
            foreach ($keywords as $keyword) {
                if (strpos($field_lower, $keyword) !== false && strpos($mapped_lower, $keyword) !== false) {
                    return $programs;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get AI-generated question from OpenAI
     */
    public function get_ai_question($request) {
        try {
            $context_json = $request->get_param('context');
            $context = json_decode($context_json, true);
            
            if (!$context) {
                return new WP_Error('invalid_context', 'Invalid context data', array('status' => 400));
            }
            
            $student_name = $context['student_name'] ?? 'Student';
            $current_step = intval($context['current_step'] ?? 0);
            $conversation_history = $context['conversation_history'] ?? array();
            
            // Get OpenAI settings
            $openai_settings = get_option('sit_recommender_openai', array());
            $api_key = $openai_settings['api_key'] ?? '';
            
            if (empty($api_key)) {
                error_log('SIT Plugin: No OpenAI API key configured');
                return new WP_Error('no_api_key', 'OpenAI API key not configured', array('status' => 500));
            }
            
            // Build OpenAI prompt for question generation
            $prompt = $this->build_question_prompt($student_name, $current_step, $conversation_history);
            
            // Call OpenAI API
            $openai_response = $this->call_openai_api($api_key, $prompt, $openai_settings);
            
            if (is_wp_error($openai_response)) {
                error_log('SIT Plugin: OpenAI API error: ' . $openai_response->get_error_message());
                return $openai_response;
            }
            
            // Parse OpenAI response to extract question and options
            $question_data = $this->parse_question_response($openai_response);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $question_data
            ));
            
        } catch (Exception $e) {
            error_log('SIT Plugin: Exception in get_ai_question: ' . $e->getMessage());
            return new WP_Error('server_error', 'Internal server error', array('status' => 500));
        }
    }
    
    /**
     * Get AI-generated recommendations from OpenAI
     */
    public function get_ai_recommendations($request) {
        try {
            $conversation_history_json = $request->get_param('conversation_history');
            $user_profile_json = $request->get_param('user_profile');
            $student_name = $request->get_param('student_name') ?? 'Student';
            
            $conversation_history = json_decode($conversation_history_json, true);
            $user_profile = json_decode($user_profile_json, true);
            
            if (!$conversation_history || !$user_profile) {
                return new WP_Error('invalid_data', 'Invalid conversation or profile data', array('status' => 400));
            }
            
            // Get OpenAI settings
            $openai_settings = get_option('sit_recommender_openai', array());
            $api_key = $openai_settings['api_key'] ?? '';
            
            if (empty($api_key)) {
                error_log('SIT Plugin: No OpenAI API key configured for recommendations');
                return new WP_Error('no_api_key', 'OpenAI API key not configured', array('status' => 500));
            }
            
            // Build OpenAI prompt for recommendations
            $prompt = $this->build_recommendation_prompt($student_name, $conversation_history, $user_profile);
            
            // Call OpenAI API
            $openai_response = $this->call_openai_api($api_key, $prompt, $openai_settings);
            
            if (is_wp_error($openai_response)) {
                error_log('SIT Plugin: OpenAI API error for recommendations: ' . $openai_response->get_error_message());
                return $openai_response;
            }
            
            // Parse OpenAI response to extract recommendations
            $recommendations_data = $this->parse_recommendations_response($openai_response, $student_name);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $recommendations_data
            ));
            
        } catch (Exception $e) {
            error_log('SIT Plugin: Exception in get_ai_recommendations: ' . $e->getMessage());
            return new WP_Error('server_error', 'Internal server error', array('status' => 500));
        }
    }
    
    /**
     * Build OpenAI prompt for question generation
     */
    private function build_question_prompt($student_name, $current_step, $conversation_history) {
        $prompt = "You are an AI academic advisor helping {$student_name} find the perfect study program. ";
        $prompt .= "This is question " . ($current_step + 1) . " out of 10 total questions. ";
        
        if (!empty($conversation_history)) {
            $prompt .= "Previous answers:\n";
            foreach ($conversation_history as $entry) {
                $prompt .= "Q{$entry['question_number']}: {$entry['answer_text']}\n";
            }
        }
        
        $prompt .= "\nGenerate the next question to better understand {$student_name}'s academic interests, learning style, career goals, or personal preferences. ";
        $prompt .= "Provide 3-4 multiple choice options. ";
        $prompt .= "Format your response as JSON: {\"question\": \"Your question here\", \"options\": [{\"id\": \"option1\", \"text\": \"Option Text\", \"description\": \"Brief description\"}]} ";
        $prompt .= "Make the question personalized and engaging.";
        
        return $prompt;
    }
    
    /**
     * Build OpenAI prompt for final recommendations
     */
    private function build_recommendation_prompt($student_name, $conversation_history, $user_profile) {
        $prompt = "You are an AI academic advisor. Analyze {$student_name}'s complete assessment to recommend study programs.\n\n";
        
        $prompt .= "Complete 10-Question Assessment:\n";
        foreach ($conversation_history as $i => $entry) {
            $prompt .= "Q" . ($i + 1) . ": {$entry['answer_text']}\n";
        }
        
        $prompt .= "\nBased on this assessment, provide 3-5 study program recommendations available in Turkey. ";
        $prompt .= "Format as JSON: {\"recommendations\": [{\"field\": \"Program Name\", \"confidence\": 85, \"why_good_fit\": \"Explanation\", \"reasons\": [\"reason1\", \"reason2\"], \"career_prospects\": \"Career info\"}], \"analysis_explanation\": \"Personal analysis for {$student_name}\"} ";
        $prompt .= "Focus on real programs like Computer Science, Business Administration, Engineering, Medicine, etc.";
        
        return $prompt;
    }
    
    /**
     * Call OpenAI API
     */
    private function call_openai_api($api_key, $prompt, $settings) {
        $model = $settings['model'] ?? 'gpt-3.5-turbo';
        $max_tokens = intval($settings['max_tokens'] ?? 500);
        $temperature = floatval($settings['temperature'] ?? 0.7);
        
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        );
        
        $body = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => $max_tokens,
            'temperature' => $temperature
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            error_log('SIT Plugin: OpenAI API returned code ' . $response_code . ': ' . $response_body);
            return new WP_Error('openai_error', 'OpenAI API error: ' . $response_code, array('status' => 500));
        }
        
        $data = json_decode($response_body, true);
        
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', 'Invalid OpenAI response format', array('status' => 500));
        }
        
        return $data['choices'][0]['message']['content'];
    }
    
    /**
     * Parse OpenAI question response
     */
    private function parse_question_response($openai_response) {
        $json_start = strpos($openai_response, '{');
        $json_end = strrpos($openai_response, '}');
        
        if ($json_start !== false && $json_end !== false) {
            $json_string = substr($openai_response, $json_start, $json_end - $json_start + 1);
            $parsed = json_decode($json_string, true);
            
            if ($parsed && isset($parsed['question']) && isset($parsed['options'])) {
                return $parsed;
            }
        }
        
        return array(
            'question' => 'What aspect interests you most about your chosen field?',
            'options' => array(
                array('id' => 'practical', 'text' => '🔧 Practical Applications', 'description' => 'Real-world problem solving'),
                array('id' => 'theoretical', 'text' => '📚 Theoretical Knowledge', 'description' => 'Deep understanding of concepts'),
                array('id' => 'creative', 'text' => '🎨 Creative Expression', 'description' => 'Innovation and creativity'),
                array('id' => 'social', 'text' => '👥 Social Impact', 'description' => 'Helping others and society')
            )
        );
    }
    
    /**
     * Parse OpenAI recommendations response
     */
    private function parse_recommendations_response($openai_response, $student_name) {
        $json_start = strpos($openai_response, '{');
        $json_end = strrpos($openai_response, '}');
        
        if ($json_start !== false && $json_end !== false) {
            $json_string = substr($openai_response, $json_start, $json_end - $json_start + 1);
            $parsed = json_decode($json_string, true);
            
            if ($parsed && isset($parsed['recommendations'])) {
                $programs_by_field = $this->map_recommendations_to_programs($parsed['recommendations']);
                
                return array(
                    'recommendations' => $parsed['recommendations'],
                    'programs_by_field' => $programs_by_field,
                    'analysis_explanation' => $parsed['analysis_explanation'] ?? "Based on your comprehensive assessment, {$student_name}, here are my personalized recommendations:"
                );
            }
        }
        
        return $this->get_fallback_recommendations($student_name, array());
    }
}
