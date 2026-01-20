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
        error_log('SIT Engine: Constructor - OpenAI settings: ' . json_encode($this->openai_settings));
        error_log('SIT Engine: Constructor - Study fields: ' . json_encode($this->study_fields));
        
        // Let's also check what OpenAI options exist in the database
        $all_options = wp_load_alloptions();
        $openai_related = array();
        foreach ($all_options as $key => $value) {
            if (strpos($key, 'sit') !== false || strpos($key, 'openai') !== false) {
                $openai_related[$key] = $value;
            }
        }
        error_log('SIT Engine: All SIT/OpenAI related options: ' . json_encode($openai_related));
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
     * Generate dynamic questions using OpenAI
     * 
     * @param int $num_questions Number of questions to generate
     * @return array Generated questions
     */
    public function generate_questions($num_questions = 10) {
        error_log('SIT: generate_questions called with ' . $num_questions . ' questions');
        error_log('SIT: OpenAI settings: ' . json_encode($this->openai_settings));
        error_log('SIT: OpenAI enabled check: ' . ($this->is_openai_enabled() ? 'true' : 'false'));
        
        if (!$this->is_openai_enabled()) {
            error_log('SIT: Using fallback questions');
            return $this->get_fallback_questions($num_questions);
        }
        
        $fields_list = implode(', ', $this->study_fields);
        
        $prompt = "Generate {$num_questions} assessment questions to help students discover their ideal field of study. 

Available study fields: {$fields_list}

Requirements:
1. Questions should explore interests, skills, career goals, and learning preferences
2. Each question should have 4 multiple choice options (A, B, C, D)
3. Questions should be engaging and help identify personality traits and academic interests
4. Avoid directly asking 'which field do you want to study'
5. Focus on scenarios, preferences, and problem-solving approaches

Format as JSON:
{
  \"questions\": [
    {
      \"id\": 1,
      \"question\": \"Question text here\",
      \"options\": [
        {\"id\": \"a\", \"text\": \"Option A text\"},
        {\"id\": \"b\", \"text\": \"Option B text\"},
        {\"id\": \"c\", \"text\": \"Option C text\"},
        {\"id\": \"d\", \"text\": \"Option D text\"}
      ]
    }
  ]
}";

        $response = $this->call_openai($prompt);
        
        if ($response && isset($response['questions'])) {
            return $response;
        }
        
        return $this->get_fallback_questions($num_questions);
    }
    
    /**
     * Analyze answers and recommend study fields using OpenAI
     * 
     * @param array $answers User's quiz answers
     * @param array $questions The questions that were answered
     * @return array Recommended fields with explanations
     */
    public function analyze_answers_and_recommend($answers, $questions) {
        if (!$this->is_openai_enabled()) {
            return $this->get_fallback_recommendations();
        }
        
        $fields_list = implode(', ', $this->study_fields);
        
        // Format answers for analysis
        $formatted_answers = array();
        foreach ($answers as $question_id => $answer_id) {
            $question = $this->find_question_by_id($questions, $question_id);
            $option = $this->find_option_by_id($question, $answer_id);
            
            if ($question && $option) {
                $formatted_answers[] = "Q: {$question['question']}\nA: {$option['text']}";
            }
        }
        
        $answers_text = implode("\n\n", $formatted_answers);
        
        $prompt = "Based on the following quiz answers, recommend the top 3 most suitable study fields for this student.

Available fields: {$fields_list}

Student's answers:
{$answers_text}

Analyze the answers to understand:
1. The student's interests and passions
2. Their problem-solving approach
3. Career aspirations
4. Learning preferences
5. Skills and strengths

Provide recommendations in JSON format:
{
  \"recommendations\": [
    {
      \"field\": \"Field name\",
      \"confidence\": 95,
      \"reasons\": [
        \"Specific reason based on their answers\",
        \"Another reason showing good fit\",
        \"Third supporting reason\"
      ],
      \"career_prospects\": \"Brief description of career opportunities\",
      \"why_good_fit\": \"Personalized explanation of why this field suits them\"
    }
  ]
}

Rank by confidence (0-100) and provide personalized, specific reasons based on their actual answers.";

        $response = $this->call_openai($prompt);
        
        if ($response && isset($response['recommendations'])) {
            return $response;
        }
        
        return $this->get_fallback_recommendations();
    }
    
    /**
     * Get programs for recommended fields using existing search plugin
     * 
     * @param array $recommended_fields Array of recommended field names
     * @return array Programs matching the fields
     */
    public function get_programs_for_fields($recommended_fields) {
        $programs = array();
        
        foreach ($recommended_fields as $field_data) {
            $field_name = $field_data['field'];
            
            // Search by faculty
            $faculty_terms = get_terms(array(
                'taxonomy' => 'sit-faculty',
                'name__like' => $field_name,
                'hide_empty' => false
            ));
            
            // Search by speciality
            $speciality_terms = get_terms(array(
                'taxonomy' => 'sit-speciality',
                'name__like' => $field_name,
                'hide_empty' => false
            ));
            
            $term_ids = array();
            
            if (!empty($faculty_terms)) {
                foreach ($faculty_terms as $term) {
                    $term_ids[] = $term->term_id;
                }
            }
            
            if (!empty($speciality_terms)) {
                foreach ($speciality_terms as $term) {
                    $term_ids[] = $term->term_id;
                }
            }
            
            if (!empty($term_ids)) {
                $field_programs = get_posts(array(
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
                
                if (!empty($field_programs)) {
                    $programs[$field_name] = $field_programs;
                }
            }
        }
        
        return $programs;
    }
    
    /**
     * Call OpenAI API
     * 
     * @param string $prompt The prompt to send to OpenAI
     * @return array|false Response data or false on failure
     */
    public function call_openai($prompt) {
        error_log('SIT Engine: call_openai called with prompt length: ' . strlen($prompt));
        
        if (!$this->is_openai_enabled()) {
            error_log('SIT Engine: OpenAI not enabled, returning false');
            return false;
        }
        
        error_log('SIT Engine: OpenAI is enabled, proceeding with API call');
        
        $api_key = $this->openai_settings['api_key'] ?? '';
        $model = $this->openai_settings['model'] ?? 'gpt-3.5-turbo';
        $max_tokens = $this->openai_settings['max_tokens'] ?? 1000;
        $temperature = $this->openai_settings['temperature'] ?? 0.7;
        
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
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            error_log('SIT OpenAI API Error: ' . $response->get_error_message());
            return false;
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Log the full response for debugging
        error_log('SIT OpenAI Response Code: ' . $http_code);
        error_log('SIT OpenAI Response Body: ' . $body);
        
        if ($http_code !== 200) {
            error_log('SIT OpenAI API HTTP Error: ' . $http_code . ' - ' . $body);
            return false;
        }
        
        if (isset($data['error'])) {
            error_log('SIT OpenAI API Error: ' . json_encode($data['error']));
            return false;
        }
        
        if (isset($data['choices'][0]['message']['content'])) {
            $content = $data['choices'][0]['message']['content'];
            error_log('SIT OpenAI Content: ' . $content);
            
            $json_data = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json_data;
            } else {
                error_log('SIT OpenAI JSON Parse Error: ' . json_last_error_msg());
                error_log('SIT OpenAI Raw Content: ' . $content);
            }
        } else {
            error_log('SIT OpenAI No content in response: ' . json_encode($data));
        }
        
        return false;
    }
    
    /**
     * Check if OpenAI is enabled and configured
     */
    public function is_openai_enabled() {
        error_log('SIT Engine: Checking OpenAI enabled status');
        error_log('SIT Engine: OpenAI settings: ' . json_encode($this->openai_settings));
        error_log('SIT Engine: Enabled check: ' . (!empty($this->openai_settings['enabled']) ? 'true' : 'false'));
        error_log('SIT Engine: API key check: ' . (!empty($this->openai_settings['api_key']) ? 'present' : 'missing'));
        
        $is_enabled = !empty($this->openai_settings['enabled']) && 
                     !empty($this->openai_settings['api_key']);
        
        error_log('SIT Engine: Final is_openai_enabled result: ' . ($is_enabled ? 'true' : 'false'));
        return $is_enabled;
    }
    
    /**
     * Get fallback questions when OpenAI is not available
     */
    private function get_fallback_questions($num_questions = 10) {
        error_log('SIT: get_fallback_questions called with ' . $num_questions . ' questions');
        $fallback_questions = array(
            array(
                'id' => 1,
                'question' => 'What type of problems do you enjoy solving most?',
                'options' => array(
                    array('id' => 'a', 'text' => 'Technical and logical challenges'),
                    array('id' => 'b', 'text' => 'Creative and artistic problems'),
                    array('id' => 'c', 'text' => 'People-related issues and conflicts'),
                    array('id' => 'd', 'text' => 'Business and strategic challenges')
                )
            ),
            array(
                'id' => 2,
                'question' => 'In your free time, you prefer to:',
                'options' => array(
                    array('id' => 'a', 'text' => 'Build, code, or experiment with technology'),
                    array('id' => 'b', 'text' => 'Create art, write, or design things'),
                    array('id' => 'c', 'text' => 'Volunteer, help others, or socialize'),
                    array('id' => 'd', 'text' => 'Read about business, economics, or leadership')
                )
            ),
            array(
                'id' => 3,
                'question' => 'What motivates you most in your future career?',
                'options' => array(
                    array('id' => 'a', 'text' => 'Innovation and technological advancement'),
                    array('id' => 'b', 'text' => 'Creative expression and artistic impact'),
                    array('id' => 'c', 'text' => 'Helping people and making a social difference'),
                    array('id' => 'd', 'text' => 'Financial success and business growth')
                )
            ),
            array(
                'id' => 4,
                'question' => 'Your ideal work environment would be:',
                'options' => array(
                    array('id' => 'a', 'text' => 'A lab, tech company, or research facility'),
                    array('id' => 'b', 'text' => 'A studio, agency, or creative workspace'),
                    array('id' => 'c', 'text' => 'A hospital, school, or community center'),
                    array('id' => 'd', 'text' => 'A corporate office or business environment')
                )
            ),
            array(
                'id' => 5,
                'question' => 'When working on a group project, you typically:',
                'options' => array(
                    array('id' => 'a', 'text' => 'Focus on the technical implementation'),
                    array('id' => 'b', 'text' => 'Handle the creative and design aspects'),
                    array('id' => 'c', 'text' => 'Coordinate team members and resolve conflicts'),
                    array('id' => 'd', 'text' => 'Manage the budget and timeline')
                )
            )
        );
        
        $result = array('questions' => array_slice($fallback_questions, 0, $num_questions));
        error_log('SIT: Returning ' . count($result['questions']) . ' fallback questions');
        return $result;
    }
    
    /**
     * Get fallback recommendations when OpenAI is not available
     */
    private function get_fallback_recommendations() {
        return array(
            'recommendations' => array(
                array(
                    'field' => 'Computer Science',
                    'confidence' => 85,
                    'reasons' => array(
                        'Strong analytical thinking skills',
                        'Interest in technology and problem-solving',
                        'Logical approach to challenges'
                    ),
                    'career_prospects' => 'Software development, AI research, cybersecurity, data science',
                    'why_good_fit' => 'Your analytical mindset and interest in technology make this field ideal for you.'
                ),
                array(
                    'field' => 'Business Administration',
                    'confidence' => 75,
                    'reasons' => array(
                        'Leadership potential',
                        'Strategic thinking abilities',
                        'Interest in organizational challenges'
                    ),
                    'career_prospects' => 'Management, consulting, entrepreneurship, finance',
                    'why_good_fit' => 'Your leadership skills and business acumen suggest success in this field.'
                ),
                array(
                    'field' => 'Engineering',
                    'confidence' => 70,
                    'reasons' => array(
                        'Problem-solving orientation',
                        'Technical aptitude',
                        'Interest in building and creating'
                    ),
                    'career_prospects' => 'Design, manufacturing, construction, research and development',
                    'why_good_fit' => 'Your technical skills and creative problem-solving align well with engineering.'
                )
            )
        );
    }
    
    /**
     * Helper function to find question by ID
     */
    private function find_question_by_id($questions, $question_id) {
        if (isset($questions['questions'])) {
            foreach ($questions['questions'] as $question) {
                if ($question['id'] == $question_id) {
                    return $question;
                }
            }
        }
        return null;
    }
    
    /**
     * Helper function to find option by ID
     */
    private function find_option_by_id($question, $option_id) {
        if ($question && isset($question['options'])) {
            foreach ($question['options'] as $option) {
                if ($option['id'] == $option_id) {
                    return $option;
                }
            }
        }
        return null;
    }
    
    /**
     * Generate search URL for recommended field
     * 
     * @param string $field_name The field name to search for
     * @return string Search URL for the field
     */
    public function get_search_url_for_field($field_name) {
        // This will integrate with your existing search plugin
        $base_url = home_url('/programs/');
        
        // Try to find matching speciality
        $speciality_term = get_term_by('name', $field_name, 'sit-speciality');
        if ($speciality_term) {
            return add_query_arg('speciality', $speciality_term->slug, $base_url);
        }
        
        // Try to find matching faculty
        $faculty_term = get_term_by('name', $field_name, 'sit-faculty');
        if ($faculty_term) {
            return add_query_arg('faculty', $faculty_term->slug, $base_url);
        }
        
        // Fallback to general search
        return add_query_arg('search', urlencode($field_name), $base_url);
    }
}
