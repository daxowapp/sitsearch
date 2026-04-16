<?php

namespace SIT\Search\Actions;

/**
 * GEO Settings Admin Page
 *
 * Admin interface for Generative Engine Optimization settings:
 * - OpenRouter API key configuration
 * - FAQ generation status monitoring
 * - Manual FAQ generation triggers
 *
 * @since 1.3.0
 */
class GeoSettingsAdmin
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('wp_ajax_sit_geo_generate_faqs', [$this, 'ajaxGenerateFaqs']);
        add_action('wp_ajax_sit_geo_get_status', [$this, 'ajaxGetStatus']);
        add_action('wp_ajax_sit_geo_regenerate_program', [$this, 'ajaxRegenerateProgram']);
        add_action('wp_ajax_sit_geo_batch_generate', [$this, 'ajaxBatchGenerate']);
        add_action('wp_ajax_sit_geo_stop_batch', [$this, 'ajaxStopBatch']);
        add_action('wp_ajax_sit_geo_create_table', [$this, 'ajaxCreateTable']);
    }

    public function registerSettings()
    {
        register_setting('sit_geo_options', 'sit_openrouter_api_key');
        register_setting('sit_geo_options', 'sit_geo_faq_model', [
            'default' => 'google/gemini-2.0-flash',
        ]);
    }

    /**
     * Get FAQ generation status statistics
     */
    public static function getStatus(): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'program_faqs';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;

        if (!$table_exists) {
            return [
                'table_exists' => false,
                'total_programs' => 0,
                'programs_with_faqs' => 0,
                'programs_pending' => 0,
                'total_faqs' => 0,
            ];
        }

        $total_programs = wp_count_posts('sit-program')->publish ?? 0;

        $programs_with_faqs = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM {$table}"
        );

        $total_faqs = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table}"
        );

        return [
            'table_exists' => true,
            'total_programs' => (int) $total_programs,
            'programs_with_faqs' => $programs_with_faqs,
            'programs_pending' => max(0, (int) $total_programs - $programs_with_faqs),
            'total_faqs' => $total_faqs,
        ];
    }

    /**
     * AJAX: Get FAQ generation status
     */
    public function ajaxGetStatus()
    {
        check_ajax_referer('sit_geo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        wp_send_json_success(self::getStatus());
    }

    /**
     * AJAX: Generate FAQs for a single program via OpenRouter
     */
    public function ajaxGenerateFaqs()
    {
        check_ajax_referer('sit_geo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        try {
            @set_time_limit(180);

            $post_id = intval($_POST['post_id'] ?? 0);
            if (!$post_id) {
                wp_send_json_error('Missing post_id');
            }

            $result = $this->generateFaqsForProgram($post_id);
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result['message']);
            }
        } catch (\Throwable $e) {
            wp_send_json_error('PHP Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
    }

    /**
     * AJAX: Regenerate FAQs for a program (delete + regenerate)
     */
    public function ajaxRegenerateProgram()
    {
        check_ajax_referer('sit_geo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Missing post_id');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'program_faqs';
        $wpdb->delete($table, ['post_id' => $post_id], ['%d']);

        $result = $this->generateFaqsForProgram($post_id);
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Generate FAQs for a single program using OpenRouter API.
     * Generates 20 FAQs in a single call (5 categories × 4 questions each).
     * 
     * @param int $post_id Program post ID
     */
    private function generateFaqsForProgram(int $post_id): array
    {
        $api_key = get_option('sit_openrouter_api_key', '');
        if (empty($api_key)) {
            return ['success' => false, 'message' => 'OpenRouter API key not configured'];
        }

        $model = get_option('sit_geo_faq_model', 'google/gemini-2.0-flash');

        // Gather program data — with null-safe university resolution
        $program_title = get_the_title($post_id);
        $uni_id = get_post_meta($post_id, 'zh_university', true);
        $university = !empty($uni_id) ? get_post($uni_id) : null;
        $uni_name = $university ? $university->post_title : '';
        
        // Fallback: try program's own 'University' text field from Zoho
        if (empty($uni_name)) {
            $uni_name = get_post_meta($post_id, 'University', true) ?: '';
        }
        
        $resolved_uni_id = $university ? $university->ID : 0;

        $fee = get_post_meta($post_id, 'Official_Tuition', true);
        $discounted_fee = get_post_meta($post_id, 'Discounted_Tuition', true);
        $currency = get_post_meta($post_id, 'Tuition_Currency', true) ?: 'USD';
        $duration = get_post_meta($post_id, 'Study_Years', true);
        $ielts = get_post_meta($post_id, 'IELTS', true);
        $toefl = get_post_meta($post_id, 'TOEFL', true);
        $description = get_post_meta($post_id, 'Description', true);
        $curriculum = get_post_meta($post_id, 'Curriculums', true);
        $ranking = $resolved_uni_id ? get_post_meta($resolved_uni_id, 'QS_Rank', true) : '';
        $students = $resolved_uni_id ? get_post_meta($resolved_uni_id, 'Number_Of_Students', true) : '';
        $year_founded = $resolved_uni_id ? get_post_meta($resolved_uni_id, 'Year_Founded', true) : '';

        $country_terms = get_the_terms($post_id, 'sit-country');
        $city_terms = $university ? get_the_terms($university->ID, 'sit-city') : get_the_terms($post_id, 'sit-city');
        $degree_terms = get_the_terms($post_id, 'sit-degree');
        $language_terms = get_the_terms($post_id, 'sit-language');
        $faculty_terms = get_the_terms($post_id, 'sit-faculty');

        $country = (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '';
        $city = (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '';
        $degree = (!empty($degree_terms) && !is_wp_error($degree_terms)) ? $degree_terms[0]->name : '';
        $language = (!empty($language_terms) && !is_wp_error($language_terms)) ? $language_terms[0]->name : '';
        $faculty = (!empty($faculty_terms) && !is_wp_error($faculty_terms)) ? $faculty_terms[0]->name : '';

        $uni_display = $uni_name ?: 'the university';

        // 5 focused categories × 4 questions = 20 FAQs total
        $categories = [
            ['id' => 'tuition', 'label' => 'Tuition fees, payment plans, scholarships, financial aid'],
            ['id' => 'admission', 'label' => 'Entry requirements, application process, documents, language tests'],
            ['id' => 'academic', 'label' => 'Curriculum, courses, thesis, career prospects after graduation'],
            ['id' => 'campus_life', 'label' => 'Student life, accommodation, facilities, city life'],
            ['id' => 'visa', 'label' => 'Student visa, residence permit, health insurance, travel'],
        ];

        // Build prompt — single call for all 20 FAQs
        $prompt = "Generate exactly 20 FAQs for this program. 5 categories, 4 questions each.\n\n";
        $prompt .= "Program: {$program_title} | University: {$uni_display} | Country: {$country} | City: {$city}\n";
        $prompt .= "Degree: {$degree} | Language: {$language} | Faculty: {$faculty}\n";
        if ($fee) $prompt .= "Tuition: {$currency} {$fee}" . ($discounted_fee ? " (discounted: {$currency} {$discounted_fee})" : '') . "\n";
        if ($duration) $prompt .= "Duration: {$duration} years\n";
        if ($ielts) $prompt .= "IELTS: {$ielts}\n";
        if ($toefl) $prompt .= "TOEFL: {$toefl}\n";
        if ($ranking) $prompt .= "QS Ranking: {$ranking}\n";
        if ($students) $prompt .= "Students: {$students}\n";
        if ($year_founded) $prompt .= "Founded: {$year_founded}\n";
        if ($description) $prompt .= "Description: " . substr(wp_strip_all_tags($description), 0, 300) . "\n";

        $prompt .= "\nCategories (4 questions each):\n";
        foreach ($categories as $cat) {
            $prompt .= "- {$cat['id']}: {$cat['label']}\n";
        }

        $prompt .= "\nCRITICAL Rules:\n";
        $prompt .= "- EVERY question MUST include the university name \"{$uni_display}\" — e.g. \"What is the tuition fee for the {$program_title} program at {$uni_display}?\"\n";
        $prompt .= "- EVERY answer MUST mention \"{$uni_display}\" at least once\n";
        $prompt .= "- Each answer MUST be 2-4 sentences, factual and entity-rich (mention university, city, country)\n";
        $prompt .= "- Include specific data (fees, scores, duration) when available. No hallucination.\n";
        $prompt .= "- Write in citation-worthy style optimized for AI search engine extraction\n";
        $prompt .= 'Response: {"faqs": [{"question":"...","answer":"...","category":"tuition"},...]}'  ;

        // Call OpenRouter API
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url('/'),
                'X-Title' => 'Study in Turkiye GEO',
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert education FAQ writer for international students. Every question and answer MUST include the university name. Respond only with valid JSON.',
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000,
                'response_format' => ['type' => 'json_object'],
            ]),
            'timeout' => 90,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => 'API error: ' . $response->get_error_message()];
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($http_code !== 200) {
            $error_msg = $body['error']['message'] ?? "HTTP {$http_code}";
            return ['success' => false, 'message' => 'OpenRouter: ' . $error_msg];
        }

        $content = $body['choices'][0]['message']['content'] ?? '';
        $faqs = json_decode($content, true);

        if (!$faqs || !isset($faqs['faqs'])) {
            return ['success' => false, 'message' => 'Invalid JSON from LLM'];
        }

        // Store FAQs in database — delete existing first (idempotent)
        global $wpdb;
        $table = $wpdb->prefix . 'program_faqs';
        $wpdb->delete($table, ['post_id' => $post_id], ['%d']);
        
        $order = 1;
        $inserted = 0;

        foreach ($faqs['faqs'] as $faq) {
            if (empty($faq['question']) || empty($faq['answer']) || empty($faq['category'])) {
                continue;
            }

            $wpdb->insert($table, [
                'post_id' => $post_id,
                'faq_order' => $order,
                'question' => sanitize_text_field($faq['question']),
                'answer' => wp_kses_post($faq['answer']),
                'category' => sanitize_text_field($faq['category']),
                'generated_at' => current_time('mysql'),
                'model' => $model,
            ], ['%d', '%d', '%s', '%s', '%s', '%s', '%s']);

            $order++;
            $inserted++;
        }

        return [
            'success' => true,
            'message' => "{$inserted} FAQs generated for {$program_title}",
            'count' => $inserted,
        ];
    }

    /**
     * AJAX: Batch generate FAQs — processes 1 program per request
     * Frontend calls this in a loop until all programs are done.
     */
    public function ajaxBatchGenerate()
    {
        check_ajax_referer('sit_geo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        try {
            // Check if batch was stopped
            if (get_transient('sit_geo_batch_stop')) {
                delete_transient('sit_geo_batch_stop');
                wp_send_json_success([
                    'done' => true,
                    'stopped' => true,
                    'message' => 'Batch generation stopped by user',
                ]);
            }

            @set_time_limit(180);

            global $wpdb;
            $faq_table = $wpdb->prefix . 'program_faqs';
            $posts_table = $wpdb->prefix . 'posts';

            // Check if FAQ table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$faq_table}'") === $faq_table;
            if (!$table_exists) {
                wp_send_json_error('program_faqs table does not exist. Deactivate and reactivate the plugin to create it.');
                return;
            }

            // Find next program that needs FAQs — with parallel worker lock
            // Each worker claims a program via a short-lived transient to avoid duplicates
            $next = null;
            $max_skip = 10; // Try up to 10 programs to find an unlocked one

            for ($skip = 0; $skip < $max_skip; $skip++) {
                $candidate = $wpdb->get_row($wpdb->prepare("
                    SELECT p.ID, COALESCE(f.cnt, 0) as faq_count
                    FROM {$posts_table} p
                    LEFT JOIN (
                        SELECT post_id, COUNT(*) as cnt
                        FROM {$faq_table}
                        GROUP BY post_id
                    ) f ON p.ID = f.post_id
                    WHERE p.post_type = 'sit-program'
                      AND p.post_status = 'publish'
                      AND (f.cnt IS NULL OR f.cnt < 15)
                    ORDER BY p.ID ASC
                    LIMIT 1 OFFSET %d
                ", $skip));

                if (!$candidate) break; // no more programs

                // Try to acquire lock (120s TTL — enough for 2 API calls)
                $lock_key = 'sit_geo_lock_' . $candidate->ID;
                if (get_transient($lock_key)) {
                    continue; // Another worker has this — skip
                }

                set_transient($lock_key, true, 120);
                $next = $candidate;
                break;
            }

            if (!$next) {
                wp_send_json_success([
                    'done' => true,
                    'message' => 'All programs have FAQs!',
                ]);
                return;
            }

            $next_id = (int) $next->ID;

            // Generate all 20 FAQs in a single API call
            $result = $this->generateFaqsForProgram($next_id);

            // Release lock
            delete_transient('sit_geo_lock_' . $next_id);

            $status = self::getStatus();

            wp_send_json_success([
                'done' => false,
                'post_id' => $next_id,
                'program_name' => get_the_title($next_id),
                'program_url' => get_permalink($next_id),
                'program_complete' => $result['success'],
                'success' => $result['success'],
                'message' => $result['message'] ?? '',
                'faqs_generated' => $result['count'] ?? 0,
                'stats' => $status,
            ]);
        } catch (\Throwable $e) {
            wp_send_json_error('PHP Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine());
        }
    }

    /**
     * AJAX: Stop running batch generation
     */
    public function ajaxStopBatch()
    {
        check_ajax_referer('sit_geo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        set_transient('sit_geo_batch_stop', true, 300);
        wp_send_json_success('Batch stop requested');
    }

    /**
     * AJAX: Create the program_faqs database table
     */
    public function ajaxCreateTable()
    {
        check_ajax_referer('sit_geo_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        // Call the global table creation function
        if (function_exists('sit_create_program_faqs_table')) {
            sit_create_program_faqs_table();
        } else {
            // Fallback: create directly
            global $wpdb;
            $table_name = $wpdb->prefix . 'program_faqs';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id BIGINT UNSIGNED NOT NULL,
                faq_order INT UNSIGNED NOT NULL DEFAULT 0,
                question TEXT NOT NULL,
                answer TEXT NOT NULL,
                category VARCHAR(50) NOT NULL DEFAULT 'general',
                generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                model VARCHAR(100) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                KEY idx_post_order (post_id, faq_order),
                KEY idx_post_category (post_id, category)
            ) {$charset_collate};";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        // Verify it was created
        global $wpdb;
        $table = $wpdb->prefix . 'program_faqs';
        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;

        if ($exists) {
            update_option('sit_program_faqs_db_version', '1.0');
            wp_send_json_success([
                'message' => 'Table created successfully!',
                'stats' => self::getStatus(),
            ]);
        } else {
            wp_send_json_error('Failed to create table. Check database permissions.');
        }
    }
}
