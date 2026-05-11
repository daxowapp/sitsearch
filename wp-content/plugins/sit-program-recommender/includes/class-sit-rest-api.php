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
            
            $engine = new SIT_Engine();
            $question_data = $engine->generate_chat_question($student_name, $current_step, $conversation_history);
            
            if (empty($question_data)) {
                 return new WP_Error('generation_failed', 'Failed to generate question', array('status' => 500));
            }
            
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
            
            $engine = new SIT_Engine();
            $recommendations_data = $engine->generate_chat_recommendations($student_name, $conversation_history, $user_profile);
            
            if (empty($recommendations_data) || empty($recommendations_data['recommendations'])) {
                return new WP_Error('generation_failed', 'Failed to generate recommendations', array('status' => 500));
            }
            
            // Map programs utilizing cached engine method
            $programs_by_field = $engine->get_programs_for_fields($recommendations_data['recommendations']);
            $recommendations_data['programs_by_field'] = $this->prepare_programs_by_field($programs_by_field);
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $recommendations_data
            ));
            
        } catch (Exception $e) {
            error_log('SIT Plugin: Exception in get_ai_recommendations: ' . $e->getMessage());
            return new WP_Error('server_error', 'Internal server error', array('status' => 500));
        }
    }
}
