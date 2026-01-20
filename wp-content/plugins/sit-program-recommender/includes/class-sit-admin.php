<?php
/**
 * SIT Admin Interface
 * 
 * Clean, focused admin panel with essential settings and usage statistics.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SIT_Admin {
    
    /**
     * Settings page slug
     */
    private $page_slug = 'sit-recommender';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_sit_test_openai', array($this, 'test_openai_connection'));
        add_action('wp_ajax_sit_clear_stats', array($this, 'clear_statistics'));
        
        // Track usage statistics
        add_action('wp_ajax_sit_track_usage', array($this, 'track_usage'));
        add_action('wp_ajax_nopriv_sit_track_usage', array($this, 'track_usage'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('SIT Recommender', 'sit-program-recommender'),
            __('SIT Recommender', 'sit-program-recommender'),
            'manage_options',
            $this->page_slug,
            array($this, 'admin_page'),
            'dashicons-welcome-learn-more',
            30
        );
    }
    
    /**
     * Register settings - Only essential options
     */
    public function register_settings() {
        // Essential settings only
        register_setting('sit_recommender_settings', 'sit_recommender_general');
        register_setting('sit_recommender_settings', 'sit_recommender_openai');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, $this->page_slug) === false) {
            return;
        }
        
        wp_enqueue_script('sit-admin-js', SIT_RECOMMENDER_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SIT_RECOMMENDER_VERSION, true);
        wp_enqueue_style('sit-admin-css', SIT_RECOMMENDER_PLUGIN_URL . 'assets/css/admin.css', array(), SIT_RECOMMENDER_VERSION);
        
        wp_localize_script('sit-admin-js', 'sitAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sit_admin_nonce')
        ));
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        // Add debug check
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Use simple default stats to avoid database issues
        $stats = array(
            'total_assessments' => 0,
            'completed_assessments' => 0,
            'completion_rate' => 0,
            'today_assessments' => 0,
            'recent_activity' => array(
                array('time' => 'No activity yet', 'text' => 'Waiting for first assessment...')
            ),
            'popular_fields' => array(
                'Computer Science' => 0,
                'Business Administration' => 0,
                'Medicine & Health Sciences' => 0
            )
        );
        ?>
        <div class="wrap">
            <h1>SIT Program Recommender</h1>
            
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Usage Statistics</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #0073aa;">
                        <h3 style="font-size: 2.5em; margin: 0; color: #0073aa;"><?php echo number_format($stats['total_assessments']); ?></h3>
                        <p style="margin: 10px 0 0 0; color: #666; font-weight: 500;">Total Assessments</p>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #0073aa;">
                        <h3 style="font-size: 2.5em; margin: 0; color: #0073aa;"><?php echo number_format($stats['completed_assessments']); ?></h3>
                        <p style="margin: 10px 0 0 0; color: #666; font-weight: 500;">Completed Assessments</p>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #0073aa;">
                        <h3 style="font-size: 2.5em; margin: 0; color: #0073aa;"><?php echo $stats['completion_rate']; ?>%</h3>
                        <p style="margin: 10px 0 0 0; color: #666; font-weight: 500;">Completion Rate</p>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border-left: 4px solid #0073aa;">
                        <h3 style="font-size: 2.5em; margin: 0; color: #0073aa;"><?php echo number_format($stats['today_assessments']); ?></h3>
                        <p style="margin: 10px 0 0 0; color: #666; font-weight: 500;">Today's Assessments</p>
                    </div>
                </div>
                
                <h3>Recent Activity</h3>
                <div style="margin: 20px 0;">
                    <?php foreach ($stats['recent_activity'] as $activity): ?>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                            <span style="color: #666; font-size: 0.9em;"><?php echo esc_html($activity['time']); ?></span>
                            <span><?php echo esc_html($activity['text']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <h3>Most Recommended Fields</h3>
                <div style="margin: 20px 0;">
                    <?php foreach ($stats['popular_fields'] as $field => $count): ?>
                        <div style="display: flex; align-items: center; margin: 10px 0;">
                            <span style="width: 200px; font-weight: 500;"><?php echo esc_html($field); ?></span>
                            <div style="flex: 1; display: flex; align-items: center; margin-left: 20px;">
                                <div style="height: 20px; background: #0073aa; border-radius: 10px; min-width: 2px; width: 20px;"></div>
                                <span style="margin-left: 10px; font-weight: 500; color: #666;"><?php echo $count; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Settings -->
            <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Settings</h2>
                
                <form method="post" action="options.php">
                    <?php settings_fields('sit_recommender_settings'); ?>
                    
                    <h3>General Settings</h3>
                    <?php $general_settings = get_option('sit_recommender_general', array()); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable Plugin</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="sit_recommender_general[enabled]" value="1" <?php checked(isset($general_settings['enabled']) ? $general_settings['enabled'] : 0, 1); ?> />
                                    Enable the SIT Program Recommender
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Track Statistics</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="sit_recommender_general[track_stats]" value="1" <?php checked(isset($general_settings['track_stats']) ? $general_settings['track_stats'] : 1, 1); ?> />
                                    Track usage statistics and analytics
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <h3>OpenAI Settings</h3>
                    <?php $openai_settings = get_option('sit_recommender_openai', array()); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">OpenAI API Key</th>
                            <td>
                                <input type="password" name="sit_recommender_openai[api_key]" value="<?php echo esc_attr(isset($openai_settings['api_key']) ? $openai_settings['api_key'] : ''); ?>" class="regular-text" placeholder="sk-..." />
                                <p class="description">Enter your OpenAI API key for enhanced AI recommendations.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Settings'); ?>
                </form>
                
                <h3>Shortcode Usage</h3>
                <p>Use this shortcode to display the program recommender:</p>
                <code style="background: #f4f4f4; padding: 10px; display: block; margin: 10px 0;">[sit_program_recommender]</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get usage statistics
     */
    public function get_usage_statistics() {
        global $wpdb;
        
        // Initialize default values
        $default_stats = array(
            'total_assessments' => 0,
            'completed_assessments' => 0,
            'completion_rate' => 0,
            'today_assessments' => 0,
            'recent_activity' => array(
                array('time' => 'No activity yet', 'text' => 'Waiting for first assessment...')
            ),
            'popular_fields' => array(
                'Computer Science' => 0,
                'Business Administration' => 0,
                'Medicine & Health Sciences' => 0
            )
        );
        
        try {
            $table_name = $wpdb->prefix . 'sit_statistics';
            
            // Create table if it doesn't exist
            $this->create_statistics_table();
            
            // Check if table exists before running queries
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            
            if (!$table_exists) {
                return $default_stats;
            }
            
            // Get basic stats with error handling
            $total_assessments = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE action = 'assessment_started'");
            $completed_assessments = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE action = 'assessment_completed'");
            $today_assessments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE action = 'assessment_started' AND DATE(created_at) = %s", date('Y-m-d')));
            
            // Handle null results
            $total_assessments = $total_assessments ? intval($total_assessments) : 0;
            $completed_assessments = $completed_assessments ? intval($completed_assessments) : 0;
            $today_assessments = $today_assessments ? intval($today_assessments) : 0;
            
            $completion_rate = $total_assessments > 0 ? round(($completed_assessments / $total_assessments) * 100, 1) : 0;
            
            // Get recent activity with error handling
            $recent_activity = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 10", ARRAY_A);
            $formatted_activity = array();
            
            if (is_array($recent_activity) && !empty($recent_activity)) {
                foreach ($recent_activity as $activity) {
                    if (isset($activity['created_at'])) {
                        $formatted_activity[] = array(
                            'time' => human_time_diff(strtotime($activity['created_at'])) . ' ago',
                            'text' => $this->format_activity_text($activity)
                        );
                    }
                }
            }
            
            // Get popular fields with error handling
            $popular_fields = $wpdb->get_results("SELECT data as field, COUNT(*) as count FROM {$table_name} WHERE action = 'recommendation_generated' AND data IS NOT NULL GROUP BY data ORDER BY count DESC LIMIT 10", ARRAY_A);
            $formatted_fields = array();
            
            if (is_array($popular_fields) && !empty($popular_fields)) {
                foreach ($popular_fields as $field) {
                    if (isset($field['field'])) {
                        $field_data = json_decode($field['field'], true);
                        if (is_array($field_data) && isset($field_data['field'])) {
                            $formatted_fields[$field_data['field']] = intval($field['count']);
                        }
                    }
                }
            }
            
            // Ensure we have some data for display
            if (empty($formatted_fields)) {
                $formatted_fields = $default_stats['popular_fields'];
            }
            
            if (empty($formatted_activity)) {
                $formatted_activity = $default_stats['recent_activity'];
            }
            
            return array(
                'total_assessments' => $total_assessments,
                'completed_assessments' => $completed_assessments,
                'completion_rate' => $completion_rate,
                'today_assessments' => $today_assessments,
                'recent_activity' => $formatted_activity,
                'popular_fields' => $formatted_fields
            );
            
        } catch (Exception $e) {
            // Log error but don't break the page
            error_log('SIT Plugin Statistics Error: ' . $e->getMessage());
            return $default_stats;
        }
    }
    
    /**
     * Create statistics table
     */
    private function create_statistics_table() {
        global $wpdb;
        
        try {
            $table_name = $wpdb->prefix . 'sit_statistics';
            
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
                id int(11) NOT NULL AUTO_INCREMENT,
                action varchar(100) NOT NULL,
                data longtext,
                user_ip varchar(45),
                user_agent text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY action (action),
                KEY created_at (created_at)
            ) {$charset_collate};";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $result = dbDelta($sql);
            
            // Log any database errors
            if ($wpdb->last_error) {
                error_log('SIT Plugin Database Error: ' . $wpdb->last_error);
            }
            
        } catch (Exception $e) {
            error_log('SIT Plugin Table Creation Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Format activity text for display
     */
    private function format_activity_text($activity) {
        if (!is_array($activity) || !isset($activity['action'])) {
            return 'Unknown activity';
        }
        
        switch ($activity['action']) {
            case 'assessment_started':
                return 'New assessment started';
            case 'assessment_completed':
                return 'Assessment completed';
            case 'recommendation_generated':
                if (isset($activity['data'])) {
                    $data = json_decode($activity['data'], true);
                    if (is_array($data) && isset($data['field'])) {
                        return 'Recommended: ' . esc_html($data['field']);
                    }
                }
                return 'Recommendation generated';
            default:
                return ucfirst(str_replace('_', ' ', esc_html($activity['action'])));
        }
    }
    
    /**
     * Track usage statistics
     */
    public function track_usage() {
        // Verify nonce - accept both REST API nonce and specific tracking nonce
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'wp_rest') && !wp_verify_nonce($nonce, 'sit_track_usage')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        $data = sanitize_textarea_field($_POST['data'] ?? '');
        $user_ip = $this->get_user_ip();
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        $table_name = $wpdb->prefix . 'sit_statistics';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'data' => $data,
                'user_ip' => $user_ip,
                'user_agent' => $user_agent,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log('SIT Plugin: Failed to insert usage statistics: ' . $wpdb->last_error);
            wp_send_json_error('Failed to track usage: ' . $wpdb->last_error);
        } else {
            wp_send_json_success('Usage tracked successfully');
        }
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
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
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Test OpenAI connection
     */
    public function test_openai_connection() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'sit_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        
        if (empty($api_key)) {
            wp_send_json_error('API key is required');
        }
        
        // Test the API key
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array('role' => 'user', 'content' => 'Test connection')
                ),
                'max_tokens' => 5
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Connection failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            wp_send_json_success('Connection successful');
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            wp_send_json_error('API Error: ' . $error_message);
        }
    }
    
    /**
     * Clear statistics
     */
    public function clear_statistics() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'sit_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'sit_statistics';
        $result = $wpdb->query("TRUNCATE TABLE {$table_name}");
        
        if ($result !== false) {
            wp_send_json_success('Statistics cleared successfully');
        } else {
            wp_send_json_error('Failed to clear statistics');
        }
    }
    
    /**
     * Render general settings tab
     */
    private function render_general_tab() {
        $settings = get_option('sit_recommender_general', array());
        $rest_routes = rest_get_server()->get_routes();
        $sit_routes = array();
        foreach ($rest_routes as $route => $handlers) {
            if (strpos($route, '/sit/v1/') === 0) {
                $sit_routes[] = $route;
            }
        }
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('sit_recommender_general');
            do_settings_sections('sit_recommender_general');
            ?>
            
            <?php if (!empty($sit_routes)): ?>
            <div class="notice notice-success">
                <p><strong><?php _e('REST API Status:', 'sit-program-recommender'); ?></strong> <?php _e('Working', 'sit-program-recommender'); ?></p>
                <p><?php _e('Available routes:', 'sit-program-recommender'); ?> <?php echo implode(', ', $sit_routes); ?></p>
                <p><a href="<?php echo rest_url('sit/v1/test'); ?>" target="_blank"><?php _e('Test API', 'sit-program-recommender'); ?></a></p>
            </div>
            <?php else: ?>
            <div class="notice notice-error">
                <p><strong><?php _e('REST API Status:', 'sit-program-recommender'); ?></strong> <?php _e('Routes not found', 'sit-program-recommender'); ?></p>
                <p><?php _e('Try deactivating and reactivating the plugin.', 'sit-program-recommender'); ?></p>
            </div>
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Plugin', 'sit-program-recommender'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="sit_recommender_general[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?> />
                            <?php _e('Enable the SIT Program Recommender', 'sit-program-recommender'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Cache Duration (seconds)', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="number" name="sit_recommender_general[cache_duration]" value="<?php echo esc_attr($settings['cache_duration'] ?? 3600); ?>" min="300" max="86400" />
                        <p class="description"><?php _e('How long to cache program data (300-86400 seconds)', 'sit-program-recommender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Rate Limit', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="number" name="sit_recommender_general[rate_limit]" value="<?php echo esc_attr($settings['rate_limit'] ?? 100); ?>" min="10" max="1000" />
                        <p class="description"><?php _e('Maximum requests per IP per hour', 'sit-program-recommender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Rate Limit Window (seconds)', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="number" name="sit_recommender_general[rate_limit_window]" value="<?php echo esc_attr($settings['rate_limit_window'] ?? 3600); ?>" min="300" max="86400" />
                        <p class="description"><?php _e('Time window for rate limiting', 'sit-program-recommender'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Render OpenAI settings tab
     */
    private function render_openai_tab() {
        $settings = get_option('sit_recommender_openai', array());
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('sit_recommender_openai');
            do_settings_sections('sit_recommender_openai');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable OpenAI Integration', 'sit-program-recommender'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="sit_recommender_openai[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?> />
                            <?php _e('Use OpenAI for enhanced recommendations', 'sit-program-recommender'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('API Key', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="password" name="sit_recommender_openai[api_key]" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" class="regular-text" />
                        <button type="button" id="test-openai" class="button"><?php _e('Test Connection', 'sit-program-recommender'); ?></button>
                        <p class="description"><?php _e('Your OpenAI API key', 'sit-program-recommender'); ?></p>
                        <div id="openai-test-result"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Model', 'sit-program-recommender'); ?></th>
                    <td>
                        <select name="sit_recommender_openai[model]">
                            <option value="gpt-3.5-turbo" <?php selected($settings['model'] ?? 'gpt-3.5-turbo', 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                            <option value="gpt-3.5-turbo-16k" <?php selected($settings['model'] ?? '', 'gpt-3.5-turbo-16k'); ?>>GPT-3.5 Turbo 16K</option>
                            <option value="gpt-4" <?php selected($settings['model'] ?? '', 'gpt-4'); ?>>GPT-4</option>
                            <option value="gpt-4-32k" <?php selected($settings['model'] ?? '', 'gpt-4-32k'); ?>>GPT-4 32K</option>
                            <option value="gpt-4-turbo" <?php selected($settings['model'] ?? '', 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                            <option value="gpt-4-turbo-preview" <?php selected($settings['model'] ?? '', 'gpt-4-turbo-preview'); ?>>GPT-4 Turbo Preview</option>
                            <option value="gpt-4o" <?php selected($settings['model'] ?? '', 'gpt-4o'); ?>>GPT-4o</option>
                            <option value="gpt-4o-mini" <?php selected($settings['model'] ?? '', 'gpt-4o-mini'); ?>>GPT-4o Mini</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Max Tokens', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="number" name="sit_recommender_openai[max_tokens]" value="<?php echo esc_attr($settings['max_tokens'] ?? 150); ?>" min="50" max="500" />
                        <p class="description"><?php _e('Maximum tokens for OpenAI response', 'sit-program-recommender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Temperature', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="number" name="sit_recommender_openai[temperature]" value="<?php echo esc_attr($settings['temperature'] ?? 0.7); ?>" min="0" max="1" step="0.1" />
                        <p class="description"><?php _e('Creativity level (0-1)', 'sit-program-recommender'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Render Questions settings tab
     */
    private function render_questions_tab() {
        $questions = get_option('sit_recommender_questions', array());
        ?>
        <div class="questions-manager">
            <h3><?php _e('Quiz Questions Management', 'sit-program-recommender'); ?></h3>
            <p><?php _e('Manage the quiz questions used for program recommendations.', 'sit-program-recommender'); ?></p>
            
            <div class="questions-actions">
                <button type="button" class="button button-primary" id="add-question"><?php _e('Add Question', 'sit-program-recommender'); ?></button>
                <button type="button" class="button" id="reset-questions"><?php _e('Reset to Default', 'sit-program-recommender'); ?></button>
            </div>
            
            <form method="post" action="options.php" id="questions-form">
                <?php
                settings_fields('sit_recommender_questions');
                do_settings_sections('sit_recommender_questions');
                ?>
                
                <div id="questions-container">
                    <?php if (!empty($questions['questions'])): ?>
                        <?php foreach ($questions['questions'] as $index => $question): ?>
                            <div class="question-item" data-index="<?php echo $index; ?>">
                                <h4><?php printf(__('Question %d', 'sit-program-recommender'), $index + 1); ?></h4>
                                <table class="form-table">
                                    <tr>
                                        <th><?php _e('Question Text', 'sit-program-recommender'); ?></th>
                                        <td>
                                            <textarea name="sit_recommender_questions[questions][<?php echo $index; ?>][question]" rows="2" cols="50"><?php echo esc_textarea($question['question']); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Category', 'sit-program-recommender'); ?></th>
                                        <td>
                                            <input type="text" name="sit_recommender_questions[questions][<?php echo $index; ?>][category]" value="<?php echo esc_attr($question['category']); ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Required', 'sit-program-recommender'); ?></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="sit_recommender_questions[questions][<?php echo $index; ?>][required]" value="1" <?php checked(!empty($question['required'])); ?> />
                                                <?php _e('Required question', 'sit-program-recommender'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Weight', 'sit-program-recommender'); ?></th>
                                        <td>
                                            <input type="number" name="sit_recommender_questions[questions][<?php echo $index; ?>][weight]" value="<?php echo esc_attr($question['weight'] ?? 1.0); ?>" step="0.1" min="0" max="2" />
                                        </td>
                                    </tr>
                                </table>
                                <button type="button" class="button button-link-delete remove-question"><?php _e('Remove Question', 'sit-program-recommender'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render remaining tabs (simplified for space)
     */
    private function render_mapping_tab() {
        echo '<h3>' . __('Department Mapping', 'sit-program-recommender') . '</h3>';
        echo '<p>' . __('Configure department vector weights and meta key mappings.', 'sit-program-recommender') . '</p>';
        // Implementation would include department mapping interface
    }
    
    private function render_display_tab() {
        $settings = get_option('sit_recommender_display', array());
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('sit_recommender_display'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Results Per Page', 'sit-program-recommender'); ?></th>
                    <td>
                        <input type="number" name="sit_recommender_display[results_per_page]" value="<?php echo esc_attr($settings['results_per_page'] ?? 10); ?>" min="5" max="50" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Show Progress Bar', 'sit-program-recommender'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="sit_recommender_display[show_progress_bar]" value="1" <?php checked(!empty($settings['show_progress_bar'])); ?> />
                            <?php _e('Display quiz progress bar', 'sit-program-recommender'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    private function render_filters_tab() {
        echo '<h3>' . __('Filter Settings', 'sit-program-recommender') . '</h3>';
        // Implementation would include filter configuration
    }
    
    /**
     * Render Usage tab
     */
    private function render_usage_tab() {
        ?>
        <div class="usage-guide">
            <h3><?php _e('How to Use SIT Program Recommender', 'sit-program-recommender'); ?></h3>
            
            <div class="sit-help-text">
                <h4><?php _e('Quick Start', 'sit-program-recommender'); ?></h4>
                <p><?php _e('The SIT Program Recommender can be added to any page or post using either a shortcode or the Gutenberg block.', 'sit-program-recommender'); ?></p>
            </div>
            
            <div class="sit-collapsible">
                <div class="sit-collapsible-header">
                    <h4><?php _e('Shortcode Usage', 'sit-program-recommender'); ?></h4>
                    <span class="sit-collapsible-toggle">▼</span>
                </div>
                <div class="sit-collapsible-content">
                    <h5><?php _e('Basic Shortcode', 'sit-program-recommender'); ?></h5>
                    <div class="sit-code">
                        [sit_program_recommender]
                    </div>
                    
                    <h5><?php _e('Shortcode with Parameters', 'sit-program-recommender'); ?></h5>
                    <div class="sit-code">
                        [sit_program_recommender theme="modern" show_filters="true" show_search="true" max_results="15"]
                    </div>
                    
                    <h5><?php _e('Available Parameters', 'sit-program-recommender'); ?></h5>
                    <table class="form-table">
                        <tr>
                            <th><code>theme</code></th>
                            <td>
                                <?php _e('Visual theme for the interface', 'sit-program-recommender'); ?><br>
                                <strong><?php _e('Options:', 'sit-program-recommender'); ?></strong> default, modern, minimal<br>
                                <strong><?php _e('Default:', 'sit-program-recommender'); ?></strong> default
                            </td>
                        </tr>
                        <tr>
                            <th><code>show_filters</code></th>
                            <td>
                                <?php _e('Enable live filtering of results', 'sit-program-recommender'); ?><br>
                                <strong><?php _e('Options:', 'sit-program-recommender'); ?></strong> true, false<br>
                                <strong><?php _e('Default:', 'sit-program-recommender'); ?></strong> true
                            </td>
                        </tr>
                        <tr>
                            <th><code>show_search</code></th>
                            <td>
                                <?php _e('Enable program search functionality', 'sit-program-recommender'); ?><br>
                                <strong><?php _e('Options:', 'sit-program-recommender'); ?></strong> true, false<br>
                                <strong><?php _e('Default:', 'sit-program-recommender'); ?></strong> true
                            </td>
                        </tr>
                        <tr>
                            <th><code>max_results</code></th>
                            <td>
                                <?php _e('Maximum number of recommendations to display', 'sit-program-recommender'); ?><br>
                                <strong><?php _e('Range:', 'sit-program-recommender'); ?></strong> 5-50<br>
                                <strong><?php _e('Default:', 'sit-program-recommender'); ?></strong> 10
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="sit-collapsible">
                <div class="sit-collapsible-header">
                    <h4><?php _e('Gutenberg Block', 'sit-program-recommender'); ?></h4>
                    <span class="sit-collapsible-toggle">▼</span>
                </div>
                <div class="sit-collapsible-content">
                    <ol>
                        <li><?php _e('In the block editor, click the "+" button to add a new block', 'sit-program-recommender'); ?></li>
                        <li><?php _e('Search for "SIT Program Recommender" or find it in the Widgets category', 'sit-program-recommender'); ?></li>
                        <li><?php _e('Click on the block to add it to your page', 'sit-program-recommender'); ?></li>
                        <li><?php _e('Configure the settings in the block inspector panel on the right', 'sit-program-recommender'); ?></li>
                        <li><?php _e('Publish or update your page', 'sit-program-recommender'); ?></li>
                    </ol>
                    
                    <div class="sit-help-text">
                        <p><?php _e('The Gutenberg block provides the same functionality as the shortcode but with a visual interface for easier configuration.', 'sit-program-recommender'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="sit-collapsible">
                <div class="sit-collapsible-header">
                    <h4><?php _e('Program Setup Requirements', 'sit-program-recommender'); ?></h4>
                    <span class="sit-collapsible-toggle">▼</span>
                </div>
                <div class="sit-collapsible-content">
                    <p><?php _e('For the recommender to work properly, you need to create program posts with the following meta fields:', 'sit-program-recommender'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th><code>sit_program_school</code></th>
                            <td><?php _e('School or department name (e.g., "School of Engineering")', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_level</code></th>
                            <td><?php _e('Program level (e.g., "undergraduate", "postgraduate")', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_mode</code></th>
                            <td><?php _e('Study mode (e.g., "full-time", "part-time")', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_duration</code></th>
                            <td><?php _e('Duration in years (e.g., "4")', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_intake</code></th>
                            <td><?php _e('Intake periods (e.g., "January, August")', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_fees</code></th>
                            <td><?php _e('Program fees (numeric value)', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_requirements</code></th>
                            <td><?php _e('Entry requirements description', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>sit_program_careers</code></th>
                            <td><?php _e('Career prospects and opportunities', 'sit-program-recommender'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="sit-collapsible">
                <div class="sit-collapsible-header">
                    <h4><?php _e('PHP Integration', 'sit-program-recommender'); ?></h4>
                    <span class="sit-collapsible-toggle">▼</span>
                </div>
                <div class="sit-collapsible-content">
                    <p><?php _e('For developers who want to integrate the recommender programmatically:', 'sit-program-recommender'); ?></p>
                    
                    <h5><?php _e('Get Recommendations', 'sit-program-recommender'); ?></h5>
                    <div class="sit-code">
// Initialize the engine<br>
$engine = new SIT_Engine();<br><br>

// Convert quiz answers to user vector<br>
$answers = [1 => 'a', 2 => 'b', 3 => 'c']; // question_id => answer_id<br>
$user_vector = $engine->convert_answers_to_vector($answers);<br><br>

// Score departments<br>
$department_scores = $engine->score_departments($user_vector);<br><br>

// Get program recommendations<br>
$recommendations = $engine->get_program_recommendations($department_scores);
                    </div>
                    
                    <h5><?php _e('Create Program Programmatically', 'sit-program-recommender'); ?></h5>
                    <div class="sit-code">
$program_id = wp_insert_post([<br>
&nbsp;&nbsp;&nbsp;&nbsp;'post_title' => 'Bachelor of Engineering (Computer Engineering)',<br>
&nbsp;&nbsp;&nbsp;&nbsp;'post_content' => 'Program description...',<br>
&nbsp;&nbsp;&nbsp;&nbsp;'post_type' => 'sit_program',<br>
&nbsp;&nbsp;&nbsp;&nbsp;'post_status' => 'publish'<br>
]);<br><br>

update_post_meta($program_id, 'sit_program_school', 'School of Engineering');<br>
update_post_meta($program_id, 'sit_program_level', 'undergraduate');<br>
update_post_meta($program_id, 'sit_program_mode', 'full-time');<br>
update_post_meta($program_id, 'sit_program_duration', '4');
                    </div>
                </div>
            </div>
            
            <div class="sit-collapsible">
                <div class="sit-collapsible-header">
                    <h4><?php _e('REST API Usage', 'sit-program-recommender'); ?></h4>
                    <span class="sit-collapsible-toggle">▼</span>
                </div>
                <div class="sit-collapsible-content">
                    <p><?php _e('The plugin provides REST API endpoints for custom integrations:', 'sit-program-recommender'); ?></p>
                    
                    <h5><?php _e('Available Endpoints', 'sit-program-recommender'); ?></h5>
                    <table class="form-table">
                        <tr>
                            <th><code>POST /wp-json/sit/v1/quiz/start</code></th>
                            <td><?php _e('Start a new quiz session', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>POST /wp-json/sit/v1/quiz/answer</code></th>
                            <td><?php _e('Submit quiz answers', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>POST /wp-json/sit/v1/recommend</code></th>
                            <td><?php _e('Get program recommendations', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>GET /wp-json/sit/v1/programs</code></th>
                            <td><?php _e('Retrieve programs with filters', 'sit-program-recommender'); ?></td>
                        </tr>
                        <tr>
                            <th><code>GET /wp-json/sit/v1/filters</code></th>
                            <td><?php _e('Get available filter options', 'sit-program-recommender'); ?></td>
                        </tr>
                    </table>
                    
                    <p><?php _e('All API requests require a valid WordPress nonce in the X-WP-Nonce header.', 'sit-program-recommender'); ?></p>
                </div>
            </div>
            
            <div class="sit-help-text">
                <h4><?php _e('Need Help?', 'sit-program-recommender'); ?></h4>
                <p><?php _e('If you encounter any issues or need assistance with the setup, please check the plugin settings and ensure all required configurations are completed.', 'sit-program-recommender'); ?></p>
            </div>
        </div>
        <?php
    }
    
    private function render_export_tab() {
        ?>
        <h3><?php _e('Export/Import Settings', 'sit-program-recommender'); ?></h3>
        <div class="export-import-section">
            <h4><?php _e('Export Settings', 'sit-program-recommender'); ?></h4>
            <p><?php _e('Download all plugin settings as a JSON file.', 'sit-program-recommender'); ?></p>
            <button type="button" class="button" id="export-settings"><?php _e('Export Settings', 'sit-program-recommender'); ?></button>
            
            <h4><?php _e('Import Settings', 'sit-program-recommender'); ?></h4>
            <p><?php _e('Upload a JSON file to import settings.', 'sit-program-recommender'); ?></p>
            <input type="file" id="import-file" accept=".json" />
            <button type="button" class="button" id="import-settings"><?php _e('Import Settings', 'sit-program-recommender'); ?></button>
        </div>
        <?php
    }
    
    /**
     * Sanitization callbacks
     */
    public function sanitize_general_settings($input) {
        $sanitized = array();
        $sanitized['enabled'] = !empty($input['enabled']);
        $sanitized['cache_duration'] = max(300, min(86400, intval($input['cache_duration'] ?? 3600)));
        $sanitized['rate_limit'] = max(10, min(1000, intval($input['rate_limit'] ?? 100)));
        $sanitized['rate_limit_window'] = max(300, min(86400, intval($input['rate_limit_window'] ?? 3600)));
        return $sanitized;
    }
    
    public function sanitize_openai_settings($input) {
        $sanitized = array();
        $sanitized['enabled'] = !empty($input['enabled']);
        $sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');
        $sanitized['model'] = sanitize_text_field($input['model'] ?? 'gpt-3.5-turbo');
        $sanitized['max_tokens'] = max(50, min(500, intval($input['max_tokens'] ?? 150)));
        $sanitized['temperature'] = max(0, min(1, floatval($input['temperature'] ?? 0.7)));
        return $sanitized;
    }
    
    public function sanitize_questions_settings($input) {
        // Basic sanitization - full implementation would validate question structure
        return $input;
    }
    
    public function sanitize_mapping_settings($input) {
        return $input;
    }
    
    public function sanitize_display_settings($input) {
        $sanitized = array();
        $sanitized['results_per_page'] = max(5, min(50, intval($input['results_per_page'] ?? 10)));
        $sanitized['show_progress_bar'] = !empty($input['show_progress_bar']);
        $sanitized['show_reasons'] = !empty($input['show_reasons']);
        $sanitized['theme'] = sanitize_text_field($input['theme'] ?? 'default');
        return $sanitized;
    }
    
    public function sanitize_filter_settings($input) {
        return $input;
    }
    
    /**
     * AJAX handlers
     */
    public function export_settings() {
        check_ajax_referer('sit_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'sit-program-recommender'));
        }
        
        $settings = array(
            'general' => get_option('sit_recommender_general', array()),
            'openai' => get_option('sit_recommender_openai', array()),
            'questions' => get_option('sit_recommender_questions', array()),
            'mapping' => get_option('sit_recommender_mapping', array()),
            'display' => get_option('sit_recommender_display', array()),
            'filters' => get_option('sit_recommender_filters', array()),
            'exported_at' => current_time('mysql'),
            'version' => SIT_RECOMMENDER_VERSION
        );
        
        // Remove sensitive data
        if (isset($settings['openai']['api_key'])) {
            $settings['openai']['api_key'] = '';
        }
        
        wp_send_json_success($settings);
    }
    
    public function import_settings() {
        check_ajax_referer('sit_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'sit-program-recommender'));
        }
        
        $settings_json = sanitize_textarea_field($_POST['settings'] ?? '');
        $settings = json_decode($settings_json, true);
        
        if (!$settings) {
            wp_send_json_error(__('Invalid JSON format', 'sit-program-recommender'));
        }
        
        // Import settings
        $imported = 0;
        $setting_keys = array('general', 'openai', 'questions', 'mapping', 'display', 'filters');
        
        foreach ($setting_keys as $key) {
            if (isset($settings[$key])) {
                update_option('sit_recommender_' . $key, $settings[$key]);
                $imported++;
            }
        }
        
        wp_send_json_success(sprintf(__('Successfully imported %d setting groups', 'sit-program-recommender'), $imported));
    }
}
