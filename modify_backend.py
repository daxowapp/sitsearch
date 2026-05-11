import re
import os

engine_path = '/Users/darwish/Dev/sitsearch/wp-content/plugins/sit-program-recommender/includes/class-sit-engine.php'
api_path = '/Users/darwish/Dev/sitsearch/wp-content/plugins/sit-program-recommender/includes/class-sit-rest-api.php'

with open(engine_path, 'r') as f:
    engine_content = f.read()

with open(api_path, 'r') as f:
    api_content = f.read()

# 1. Update call_openai in engine to support response_format 
new_call_openai = """    public function call_openai($prompt, $expect_json_object = true) {
        error_log('SIT Engine: call_openai called with prompt length: ' . strlen($prompt));
        
        if (!$this->is_openai_enabled()) {
            error_log('SIT Engine: OpenAI not enabled, returning false');
            return false;
        }
        
        $api_key = $this->openai_settings['api_key'] ?? '';
        $model = $this->openai_settings['model'] ?? 'gpt-3.5-turbo-1106'; 
        // For JSON formatting, explicitly fallback to a 1106 model if user didn't specify one
        if ($expect_json_object && isset($this->openai_settings['model']) && $this->openai_settings['model'] === 'gpt-3.5-turbo') {
            $model = 'gpt-3.5-turbo-1106';
        }
        
        $max_tokens = intval($this->openai_settings['max_tokens'] ?? 1000);
        $temperature = floatval($this->openai_settings['temperature'] ?? 0.7);
        
        if (empty($api_key)) {
            return false;
        }
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are an educational counselor helping students find their ideal field of study. Always respond with valid JSON format.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => $max_tokens,
            'temperature' => $temperature
        );
        
        if ($expect_json_object) {
            $data['response_format'] = array('type' => 'json_object');
        }
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));"""

engine_content = re.sub(
    r"    public function call_openai\(\$prompt\) \{.*?'timeout' => 30\s*\)\);",
    new_call_openai,
    engine_content,
    flags=re.DOTALL
)


new_chat_methods = """
    /**
     * Generate dynamic chat questions based on context
     */
    public function generate_chat_question($student_name, $current_step, $conversation_history) {
        $prompt = "You are an AI academic advisor helping {$student_name} find the perfect study program. ";
        $prompt .= "This is question " . ($current_step + 1) . " out of 10 total questions. ";
        
        if (!empty($conversation_history)) {
            $prompt .= "Previous answers:\\n";
            foreach ($conversation_history as $entry) {
                $prompt .= "Q{$entry['question_number']}: {$entry['answer_text']}\\n";
            }
        }
        
        $prompt .= "\\nGenerate the next question to better understand {$student_name}'s academic interests, learning style, career goals, or personal preferences. ";
        $prompt .= "Provide 3-4 multiple choice options. ";
        $prompt .= "Format your response precisely as JSON: {\\"question\\": \\"Your question here\\", \\"options\\": [{\\"id\\": \\"option1\\", \\"text\\": \\"Option Text\\", \\"description\\": \\"Brief description\\"}]} ";
        $prompt .= "Make the question personalized and engaging.";
        
        $response = $this->call_openai($prompt, true);
        
        if ($response && isset($response['question']) && isset($response['options'])) {
            return $response;
        }
        
        // Fallback logic
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
     * Generate chat recommendations
     */
    public function generate_chat_recommendations($student_name, $conversation_history, $user_profile) {
        $prompt = "You are an AI academic advisor. Analyze {$student_name}'s complete assessment to recommend study programs.\\n\\n";
        
        $prompt .= "Complete 10-Question Assessment:\\n";
        foreach ($conversation_history as $i => $entry) {
            $prompt .= "Q" . ($i + 1) . ": {$entry['answer_text']}\\n";
        }
        
        $fields_list = implode(', ', $this->study_fields);
        $prompt .= "\\nAvailable fields: {$fields_list}\\n";
        
        $prompt .= "\\nBased on this assessment, provide 3-5 study program recommendations available in Turkey. ";
        $prompt .= "Format as JSON: {\\"recommendations\\": [{\\"field\\": \\"Program Name\\", \\"confidence\\": 85, \\"why_good_fit\\": \\"Explanation\\", \\"reasons\\": [\\"reason1\\", \\"reason2\\"], \\"career_prospects\\": \\"Career info\\"}], \\"analysis_explanation\\": \\"Personal analysis for {$student_name}\\"} ";
        $prompt .= "Focus on real programs and explicitly use the provided field names when possible.";
        
        $response = $this->call_openai($prompt, true);
        
        if ($response && isset($response['recommendations'])) {
            return $response;
        }
        
        // Return fallback
        return $this->get_fallback_recommendations();
    }
"""

engine_content = engine_content.replace('    /**\n     * Check if OpenAI is enabled and configured\n     */', new_chat_methods + '\n    /**\n     * Check if OpenAI is enabled and configured\n     */')


# Fix class-sit-engine.php get_programs_for_fields performance
new_get_programs_for_fields = """
    public function get_programs_for_fields($recommended_fields) {
        $programs = array();
        
        // Cache object
        global $wpdb;
        
        foreach ($recommended_fields as $field_data) {
            $field_name = $field_data['field'];
            
            // Check if cached search mechanism exists
            if (class_exists('\\SIT\\Search\\Services\\CachedData')) {
                // Highly optimized taxonomy search
                $faculty_terms = \\SIT\\Search\\Services\\CachedData::get_taxonomy_terms('sit-faculty');
                $speciality_terms = \\SIT\\Search\\Services\\CachedData::get_taxonomy_terms('sit-speciality');
                $active_ids = \\SIT\\Search\\Services\\CachedData::get_active_university_ids();
                
                $term_ids = array();
                
                if (!is_wp_error($faculty_terms)) {
                    foreach ($faculty_terms as $term) {
                        if (stripos($term->name, $field_name) !== false) {
                            $term_ids[] = $term->term_id;
                        }
                    }
                }
                
                if (!is_wp_error($speciality_terms)) {
                    foreach ($speciality_terms as $term) {
                        if (stripos($term->name, $field_name) !== false) {
                            $term_ids[] = $term->term_id;
                        }
                    }
                }
                
                if (!empty($term_ids)) {
                    // Use optimized direct query with caching
                    $term_ids_in = implode(',', array_map('intval', $term_ids));
                    
                    // We need a specific hash for this specific field lookup
                    $cache_key = 'sit_rec_prog_' . md5($field_name . implode(',', $active_ids));
                    
                    $field_programs = get_transient($cache_key);
                    if ($field_programs === false) {
                        $active_ids_in = !empty($active_ids) ? "AND p.post_parent IN (" . implode(',', $active_ids) . ")" : "";
                        // Fallback simply to any active post if no strict parent relation
                        $active_ids_in = ""; 

                        $sql = $wpdb->prepare("
                            SELECT DISTINCT p.* FROM {$wpdb->posts} p
                            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                            WHERE p.post_type = 'sit-program' AND p.post_status = 'publish'
                            AND tr.term_taxonomy_id IN ($term_ids_in)
                            LIMIT %d
                        ", 10);
                        
                        $field_programs = call_user_func('get_posts', array(
                            'post_type' => 'sit-program',
                            'posts_per_page' => 10,
                            'tax_query' => array(
                                'relation' => 'OR',
                                array(
                                    'taxonomy' => 'sit-faculty',
                                    'field' => 'term_id',
                                    'terms' => $term_ids
                                ),
                                array(
                                    'taxonomy' => 'sit-speciality',
                                    'field' => 'term_id',
                                    'terms' => $term_ids
                                )
                            )
                        ));
                        
                        // Let's use the API from raw query instead of get_posts
                        $posts = $wpdb->get_results($sql);
                        $field_programs = array();
                        foreach($posts as $p) {
                            $field_programs[] = get_post($p->ID);
                        }
                        
                        set_transient($cache_key, $field_programs, 3600);
                    }
                    
                    if (!empty($field_programs)) {
                        $programs[$field_name] = $field_programs;
                    }
                }

            } else {
                // Fallback traditional (but still cached) search logic
                $cache_key = 'sit_rec_fb_' . md5($field_name);
                $field_programs = get_transient($cache_key);
                if ($field_programs === false) {
                    $faculty_terms = get_terms(array(
                        'taxonomy' => 'sit-faculty',
                        'name__like' => $field_name,
                        'hide_empty' => false
                    ));
                    $speciality_terms = get_terms(array(
                        'taxonomy' => 'sit-speciality',
                        'name__like' => $field_name,
                        'hide_empty' => false
                    ));
                    
                    $term_ids = array();
                    if (!empty($faculty_terms)) { foreach ($faculty_terms as $term) { $term_ids[] = $term->term_id; } }
                    if (!empty($speciality_terms)) { foreach ($speciality_terms as $term) { $term_ids[] = $term->term_id; } }
                    
                    if (!empty($term_ids)) {
                        $field_programs = get_posts(array(
                            'post_type' => 'sit-program',
                            'posts_per_page' => 10,
                            'tax_query' => array(
                                'relation' => 'OR',
                                array('taxonomy' => 'sit-faculty', 'field' => 'term_id', 'terms' => $term_ids),
                                array('taxonomy' => 'sit-speciality', 'field' => 'term_id', 'terms' => $term_ids)
                            )
                        ));
                    }
                    if (empty($field_programs)) { $field_programs = []; }
                    set_transient($cache_key, $field_programs, 3600);
                }
                
                if (!empty($field_programs)) {
                    $programs[$field_name] = $field_programs;
                }
            }
        }
        
        return $programs;
    }"""

engine_content = re.sub(
    r"    public function get_programs_for_fields.*?return \$programs;\n    \}",
    new_get_programs_for_fields,
    engine_content,
    flags=re.DOTALL
)

with open(engine_path, 'w') as f:
    f.write(engine_content)

# Fix class-sit-rest-api.php -> replace get_ai_question and get_ai_recommendations
api_ai_replacement = """    /**
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
            
            // Programs mapping
            $programs_by_field = $engine->get_programs_for_fields($recommendations_data['recommendations']);
            $recommendations_data['programs_by_field'] = $programs_by_field;
            
            return rest_ensure_response(array(
                'success' => true,
                'data' => $recommendations_data
            ));
            
        } catch (Exception $e) {
            error_log('SIT Plugin: Exception in get_ai_recommendations: ' . $e->getMessage());
            return new WP_Error('server_error', 'Internal server error', array('status' => 500));
        }
    }"""

# Remove everything after get_ai_question because build_prompt, cache_api etc are no longer needed
api_content = re.sub(
    r"    /\*\*\n     \* Get AI-generated question from OpenAI\n     \*/.*",
    api_ai_replacement + "\n}",
    api_content,
    flags=re.DOTALL
)

with open(api_path, 'w') as f:
    f.write(api_content)
