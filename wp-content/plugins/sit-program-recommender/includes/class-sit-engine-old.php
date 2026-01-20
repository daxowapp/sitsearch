<?php
/**
 * SIT AI-Powered Recommendation Engine
 * 
 * Generates dynamic questions using OpenAI and analyzes answers to recommend study fields.
 * Integrates with existing SIT Search plugin for program display.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SIT_Engine {
    
    /**
     * OpenAI settings
     */
    private $openai_settings;
    
    /**
     * Available study fields/departments
     */
    private $study_fields;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->openai_settings = get_option('sit_recommender_openai', array());
        $this->study_fields = $this->get_study_fields();
    }
    
    /**
     * Get available study fields from taxonomies
     */
    private function get_study_fields() {
        $fields = array();
        
        // Get faculties/specialities from your search plugin
        $faculties = get_terms(array(
            'taxonomy' => 'sit-faculty',
            'hide_empty' => false
        ));
        
        $specialities = get_terms(array(
            'taxonomy' => 'sit-speciality',
            'hide_empty' => false
        ));
        
        if (!empty($faculties)) {
            foreach ($faculties as $faculty) {
                $fields[] = $faculty->name;
            }
        }
        
        if (!empty($specialities)) {
            foreach ($specialities as $speciality) {
                $fields[] = $speciality->name;
            }
        }
        
        // Default fields if none found
        if (empty($fields)) {
            $fields = array(
                'Engineering', 'Computer Science', 'Business Administration',
                'Medicine', 'Law', 'Arts and Design', 'Education',
                'Social Sciences', 'Natural Sciences', 'Mathematics'
            );
        }
        
        return array_unique($fields);
    }
    
    /**
     * Convert quiz answers to user vector
     * 
     * @param array $answers Array of question_id => answer_id pairs
     * @return array User vector representing preferences
     */
    public function convert_answers_to_vector($answers) {
        $questions = get_option('sit_recommender_questions', array());
        
        if (empty($questions['questions'])) {
            return array();
        }
        
        // Initialize user vector with zeros
        $vector_size = count($questions['scoring']['vector_dimensions']);
        $user_vector = array_fill(0, $vector_size, 0.0);
        $total_weight = 0.0;
        
        foreach ($questions['questions'] as $question) {
            $question_id = $question['id'];
            
            // Skip if no answer provided for this question
            if (!isset($answers[$question_id])) {
                continue;
            }
            
            $answer_id = $answers[$question_id];
            $question_weight = isset($question['weight']) ? $question['weight'] : 1.0;
            
            // Find the selected option
            $selected_option = null;
            foreach ($question['options'] as $option) {
                if ($option['id'] === $answer_id) {
                    $selected_option = $option;
                    break;
                }
            }
            
            if ($selected_option && isset($selected_option['vector'])) {
                // Add weighted vector to user vector
                for ($i = 0; $i < $vector_size; $i++) {
                    if (isset($selected_option['vector'][$i])) {
                        $user_vector[$i] += $selected_option['vector'][$i] * $question_weight;
                    }
                }
                $total_weight += $question_weight;
            }
        }
        
        // Normalize by total weight
        if ($total_weight > 0) {
            for ($i = 0; $i < $vector_size; $i++) {
                $user_vector[$i] /= $total_weight;
            }
        }
        
        // Apply normalization method
        $normalization = $questions['scoring']['normalization'] ?? 'l2';
        $user_vector = $this->normalize_vector($user_vector, $normalization);
        
        return $user_vector;
    }
    
    /**
     * Score departments based on user vector
     * 
     * @param array $user_vector User preference vector
     * @param array $user_data Additional user data for bonus rules
     * @return array Array of department scores with reasons
     */
    public function score_departments($user_vector, $user_data = array()) {
        $mapping = get_option('sit_recommender_mapping', array());
        
        if (empty($mapping['departments']) || empty($user_vector)) {
            return array();
        }
        
        $scores = array();
        $global_settings = $mapping['global_settings'] ?? array();
        $min_threshold = $global_settings['min_score_threshold'] ?? 0.0;
        
        foreach ($mapping['departments'] as $dept_key => $department) {
            if (!isset($department['vector_weights'])) {
                continue;
            }
            
            // Calculate dot product score
            $base_score = $this->calculate_dot_product($user_vector, $department['vector_weights']);
            
            // Apply bonus rules
            $bonus_score = $this->apply_bonus_rules($department, $user_data);
            $total_score = $base_score + $bonus_score;
            
            // Only include if above threshold
            if ($total_score >= $min_threshold) {
                $scores[$dept_key] = array(
                    'department' => $department,
                    'base_score' => $base_score,
                    'bonus_score' => $bonus_score,
                    'total_score' => $total_score,
                    'reasons' => $this->generate_reasons($department, $user_vector, $bonus_score > 0)
                );
            }
        }
        
        // Sort by total score (descending)
        uasort($scores, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });
        
        // Limit results
        $max_results = $global_settings['max_recommendations'] ?? 10;
        $scores = array_slice($scores, 0, $max_results, true);
        
        // Normalize scores if requested
        $normalization = $global_settings['score_normalization'] ?? 'none';
        if ($normalization !== 'none') {
            $scores = $this->normalize_scores($scores, $normalization);
        }
        
        return $scores;
    }
    
    /**
     * Get program recommendations based on department scores
     * 
     * @param array $department_scores Scored departments
     * @param array $filters Additional filters
     * @return array Program recommendations
     */
    public function get_program_recommendations($department_scores, $filters = array()) {
        if (empty($department_scores)) {
            return array();
        }
        
        $dal = new SIT_DAL();
        $recommendations = array();
        
        foreach ($department_scores as $dept_key => $score_data) {
            $department = $score_data['department'];
            $meta_mappings = $department['meta_mappings'] ?? array();
            
            // Build query filters
            $query_filters = array_merge($filters, $meta_mappings);
            
            // Get programs for this department
            $programs = $dal->get_programs($query_filters);
            
            foreach ($programs as $program) {
                $recommendations[] = array(
                    'program' => $program,
                    'department_key' => $dept_key,
                    'department_name' => $department['name'],
                    'score' => $score_data['total_score'],
                    'base_score' => $score_data['base_score'],
                    'bonus_score' => $score_data['bonus_score'],
                    'reasons' => $score_data['reasons'],
                    'match_strength' => $this->get_match_strength($score_data['total_score'])
                );
            }
        }
        
        // Sort by score
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $recommendations;
    }
    
    /**
     * Rerank recommendations using OpenAI (if enabled)
     * 
     * @param array $recommendations Initial recommendations
     * @param array $user_profile User profile data
     * @return array Reranked recommendations
     */
    public function rerank_with_openai($recommendations, $user_profile = array()) {
        $openai_settings = get_option('sit_recommender_openai', array());
        
        if (empty($openai_settings['enabled']) || empty($openai_settings['api_key'])) {
            return $recommendations;
        }
        
        try {
            // Prepare context for OpenAI
            $context = $this->prepare_openai_context($recommendations, $user_profile);
            
            // Call OpenAI API
            $reranked = $this->call_openai_api($context, $openai_settings);
            
            if ($reranked) {
                return $this->apply_openai_reranking($recommendations, $reranked);
            }
        } catch (Exception $e) {
            error_log('SIT Recommender OpenAI Error: ' . $e->getMessage());
        }
        
        return $recommendations;
    }
    
    /**
     * Calculate dot product between two vectors
     */
    private function calculate_dot_product($vector_a, $vector_b) {
        $dot_product = 0.0;
        $size = min(count($vector_a), count($vector_b));
        
        for ($i = 0; $i < $size; $i++) {
            $dot_product += $vector_a[$i] * $vector_b[$i];
        }
        
        return $dot_product;
    }
    
    /**
     * Apply bonus rules to department score
     */
    private function apply_bonus_rules($department, $user_data) {
        $bonus_score = 0.0;
        
        if (!isset($department['bonus_rules']) || empty($user_data)) {
            return $bonus_score;
        }
        
        foreach ($department['bonus_rules'] as $rule) {
            $condition = $rule['condition'] ?? '';
            $bonus = $rule['bonus'] ?? 0.0;
            
            if ($this->evaluate_bonus_condition($condition, $user_data)) {
                $bonus_score += $bonus;
            }
        }
        
        return $bonus_score;
    }
    
    /**
     * Evaluate bonus condition
     */
    private function evaluate_bonus_condition($condition, $user_data) {
        // Simple condition evaluation - can be extended
        switch ($condition) {
            case 'high_math_score':
                return isset($user_data['math_score']) && $user_data['math_score'] >= 80;
            
            case 'programming_experience':
                return !empty($user_data['programming_experience']);
            
            case 'strong_physics':
                return isset($user_data['physics_score']) && $user_data['physics_score'] >= 75;
            
            case 'hands_on_experience':
                return !empty($user_data['practical_experience']);
            
            case 'leadership_experience':
                return !empty($user_data['leadership_roles']);
            
            case 'communication_skills':
                return isset($user_data['communication_score']) && $user_data['communication_score'] >= 75;
            
            case 'biology_chemistry_background':
                return (isset($user_data['biology_score']) && $user_data['biology_score'] >= 70) ||
                       (isset($user_data['chemistry_score']) && $user_data['chemistry_score'] >= 70);
            
            case 'healthcare_experience':
                return !empty($user_data['healthcare_experience']);
            
            case 'research_interest':
                return !empty($user_data['research_projects']);
            
            case 'community_service':
                return !empty($user_data['volunteer_hours']);
            
            case 'creative_portfolio':
                return !empty($user_data['portfolio_url']);
            
            case 'design_software_skills':
                return !empty($user_data['design_software']);
            
            case 'environmental_awareness':
                return !empty($user_data['environmental_projects']);
            
            case 'sustainability_projects':
                return !empty($user_data['sustainability_experience']);
            
            case 'technical_aptitude':
                return isset($user_data['technical_score']) && $user_data['technical_score'] >= 75;
            
            case 'industry_experience':
                return !empty($user_data['industry_experience']);
            
            default:
                return false;
        }
    }
    
    /**
     * Generate reasons for recommendation
     */
    private function generate_reasons($department, $user_vector, $has_bonus = false) {
        $questions = get_option('sit_recommender_questions', array());
        $mapping = get_option('sit_recommender_mapping', array());
        
        $reasons = array();
        $dimensions = $questions['scoring']['vector_dimensions'] ?? array();
        $templates = $mapping['global_settings']['reason_templates'] ?? array();
        
        // Find top matching dimensions
        $top_dimensions = $this->get_top_dimensions($user_vector, $dimensions, 2);
        
        if (!empty($top_dimensions)) {
            $primary_areas = implode(' and ', array_slice($top_dimensions, 0, 2));
            $reason_template = $templates['high_match'] ?? 'This program aligns with your interests in {primary_areas}.';
            $reasons[] = str_replace('{primary_areas}', $primary_areas, $reason_template);
        }
        
        if ($has_bonus) {
            $bonus_template = $templates['bonus_applied'] ?? 'Additional consideration given for relevant background.';
            $reasons[] = $bonus_template;
        }
        
        return $reasons;
    }
    
    /**
     * Get top dimensions from user vector
     */
    private function get_top_dimensions($vector, $dimensions, $count = 3) {
        if (empty($vector) || empty($dimensions)) {
            return array();
        }
        
        $indexed_vector = array();
        for ($i = 0; $i < count($vector) && $i < count($dimensions); $i++) {
            $indexed_vector[] = array(
                'dimension' => $dimensions[$i],
                'value' => $vector[$i]
            );
        }
        
        // Sort by value descending
        usort($indexed_vector, function($a, $b) {
            return $b['value'] <=> $a['value'];
        });
        
        $top_dimensions = array();
        for ($i = 0; $i < $count && $i < count($indexed_vector); $i++) {
            if ($indexed_vector[$i]['value'] > 0.1) { // Only include significant dimensions
                $top_dimensions[] = $indexed_vector[$i]['dimension'];
            }
        }
        
        return $top_dimensions;
    }
    
    /**
     * Normalize vector using specified method
     */
    private function normalize_vector($vector, $method = 'l2') {
        switch ($method) {
            case 'l2':
                $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $vector)));
                if ($magnitude > 0) {
                    return array_map(function($x) use ($magnitude) { return $x / $magnitude; }, $vector);
                }
                break;
            
            case 'l1':
                $sum = array_sum(array_map('abs', $vector));
                if ($sum > 0) {
                    return array_map(function($x) use ($sum) { return $x / $sum; }, $vector);
                }
                break;
            
            case 'max':
                $max = max(array_map('abs', $vector));
                if ($max > 0) {
                    return array_map(function($x) use ($max) { return $x / $max; }, $vector);
                }
                break;
        }
        
        return $vector;
    }
    
    /**
     * Normalize scores using specified method
     */
    private function normalize_scores($scores, $method = 'min_max') {
        if (empty($scores)) {
            return $scores;
        }
        
        $score_values = array_column($scores, 'total_score');
        
        switch ($method) {
            case 'min_max':
                $min_score = min($score_values);
                $max_score = max($score_values);
                $range = $max_score - $min_score;
                
                if ($range > 0) {
                    foreach ($scores as &$score_data) {
                        $score_data['normalized_score'] = ($score_data['total_score'] - $min_score) / $range;
                    }
                }
                break;
            
            case 'z_score':
                $mean = array_sum($score_values) / count($score_values);
                $variance = array_sum(array_map(function($x) use ($mean) { return pow($x - $mean, 2); }, $score_values)) / count($score_values);
                $std_dev = sqrt($variance);
                
                if ($std_dev > 0) {
                    foreach ($scores as &$score_data) {
                        $score_data['normalized_score'] = ($score_data['total_score'] - $mean) / $std_dev;
                    }
                }
                break;
        }
        
        return $scores;
    }
    
    /**
     * Get match strength label
     */
    private function get_match_strength($score) {
        if ($score >= 0.8) {
            return __('Excellent Match', 'sit-program-recommender');
        } elseif ($score >= 0.6) {
            return __('Good Match', 'sit-program-recommender');
        } elseif ($score >= 0.4) {
            return __('Fair Match', 'sit-program-recommender');
        } else {
            return __('Potential Match', 'sit-program-recommender');
        }
    }
    
    /**
     * Prepare context for OpenAI API
     */
    private function prepare_openai_context($recommendations, $user_profile) {
        $context = array(
            'user_profile' => $user_profile,
            'recommendations' => array()
        );
        
        foreach ($recommendations as $rec) {
            $context['recommendations'][] = array(
                'program_title' => $rec['program']->post_title,
                'department' => $rec['department_name'],
                'score' => $rec['score'],
                'reasons' => $rec['reasons']
            );
        }
        
        return $context;
    }
    
    /**
     * Call OpenAI API for reranking
     */
    private function call_openai_api($context, $settings) {
        $api_key = $settings['api_key'];
        $model = $settings['model'] ?? 'gpt-3.5-turbo';
        $max_tokens = $settings['max_tokens'] ?? 150;
        $temperature = $settings['temperature'] ?? 0.7;
        
        $prompt = $this->build_openai_prompt($context);
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are an expert academic advisor helping students choose the best programs.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => $max_tokens,
                'temperature' => $temperature
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('OpenAI API request failed: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        return null;
    }
    
    /**
     * Build OpenAI prompt
     */
    private function build_openai_prompt($context) {
        $prompt = "Based on the following user profile and program recommendations, please provide insights on the top 3 most suitable programs and explain why they would be the best fit:\n\n";
        
        $prompt .= "User Profile:\n";
        foreach ($context['user_profile'] as $key => $value) {
            $prompt .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
        }
        
        $prompt .= "\nProgram Recommendations:\n";
        foreach ($context['recommendations'] as $i => $rec) {
            $prompt .= ($i + 1) . ". " . $rec['program_title'] . " (" . $rec['department'] . ") - Score: " . round($rec['score'], 2) . "\n";
            $prompt .= "   Reasons: " . implode(', ', $rec['reasons']) . "\n\n";
        }
        
        $prompt .= "Please provide a brief analysis focusing on the top 3 recommendations.";
        
        return $prompt;
    }
    
    /**
     * Apply OpenAI reranking results
     */
    private function apply_openai_reranking($recommendations, $openai_response) {
        // For now, just add the OpenAI insights to the recommendations
        // In a more sophisticated implementation, you could parse the response
        // and actually reorder the recommendations
        
        if (!empty($recommendations)) {
            $recommendations[0]['openai_insights'] = $openai_response;
        }
        
        return $recommendations;
    }
}
