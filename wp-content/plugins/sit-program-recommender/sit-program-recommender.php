<?php
/**
 * Plugin Name: SIT Program Recommender
 * Plugin URI: https://sit.edu.sg
 * Description: An intelligent program recommendation system for Singapore Institute of Technology (SIT) that uses quiz-based assessment to match students with suitable academic programs.
 * Version: 1.0.0
 * Author: SIT Development Team
 * Author URI: https://sit.edu.sg
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sit-program-recommender
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SIT_RECOMMENDER_VERSION', '1.0.0');
define('SIT_RECOMMENDER_PLUGIN_FILE', __FILE__);
define('SIT_RECOMMENDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SIT_RECOMMENDER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SIT_RECOMMENDER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main SIT Program Recommender Class
 */
class SIT_Program_Recommender {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('SIT_Program_Recommender', 'uninstall'));
        
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once SIT_RECOMMENDER_PLUGIN_DIR . 'includes/class-sit-engine.php';
        require_once SIT_RECOMMENDER_PLUGIN_DIR . 'includes/class-sit-dal.php';
        require_once SIT_RECOMMENDER_PLUGIN_DIR . 'includes/class-sit-admin.php';
        require_once SIT_RECOMMENDER_PLUGIN_DIR . 'includes/class-sit-frontend.php';
        require_once SIT_RECOMMENDER_PLUGIN_DIR . 'includes/class-sit-rest-api.php';
        require_once SIT_RECOMMENDER_PLUGIN_DIR . 'includes/class-sit-blocks.php';
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize components
        if (is_admin()) {
            new SIT_Admin();
        }
        
        new SIT_Frontend();
        new SIT_Blocks();
        
        // Initialize REST API on proper hook
        add_action('rest_api_init', array($this, 'init_rest_api'));
        
        // Add debug hook
        add_action('init', array($this, 'debug_routes'));
        
        // Load plugin textdomain
        $this->load_textdomain();
    }
    
    /**
     * Initialize REST API
     */
    public function init_rest_api() {
        new SIT_REST_API();
    }
    
    /**
     * Debug function to check if routes are registered
     */
    public function debug_routes() {
        if (isset($_GET['sit_debug_routes']) && current_user_can('manage_options')) {
            $routes = rest_get_server()->get_routes();
            $sit_routes = array();
            foreach ($routes as $route => $handlers) {
                if (strpos($route, '/sit/v1/') === 0) {
                    $sit_routes[$route] = $handlers;
                }
            }
            
            echo '<pre>';
            echo "SIT Routes Found: " . count($sit_routes) . "\n\n";
            foreach ($sit_routes as $route => $handlers) {
                echo "Route: " . $route . "\n";
                foreach ($handlers as $handler) {
                    echo "  Methods: " . implode(', ', $handler['methods']) . "\n";
                    echo "  Callback: " . (is_array($handler['callback']) ? get_class($handler['callback'][0]) . '::' . $handler['callback'][1] : $handler['callback']) . "\n";
                }
                echo "\n";
            }
            echo '</pre>';
            exit;
        }
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'sit-program-recommender',
            false,
            dirname(SIT_RECOMMENDER_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        $this->create_default_options();
        
        // Load default data
        $this->load_default_data();
        
        // Create database tables if needed
        $this->create_tables();
        
        // Flush rewrite rules to ensure REST API routes work
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove options
        delete_option('sit_recommender_general');
        delete_option('sit_recommender_openai');
        delete_option('sit_recommender_questions');
        delete_option('sit_recommender_mapping');
        delete_option('sit_recommender_display');
        delete_option('sit_recommender_filters');
        
        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sit_recommender_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_sit_recommender_%'");
    }
    
    /**
     * Create default options
     */
    private function create_default_options() {
        // General settings
        if (!get_option('sit_recommender_general')) {
            add_option('sit_recommender_general', array(
                'enabled' => true,
                'cache_duration' => 3600,
                'rate_limit' => 100,
                'rate_limit_window' => 3600
            ));
        }
        
        // OpenAI settings
        if (!get_option('sit_recommender_openai')) {
            add_option('sit_recommender_openai', array(
                'enabled' => false,
                'api_key' => '',
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 150,
                'temperature' => 0.7
            ));
        }
        
        // Display settings
        if (!get_option('sit_recommender_display')) {
            add_option('sit_recommender_display', array(
                'results_per_page' => 10,
                'show_progress_bar' => true,
                'show_reasons' => true,
                'theme' => 'default'
            ));
        }
        
        // Filter settings
        if (!get_option('sit_recommender_filters')) {
            add_option('sit_recommender_filters', array(
                'enable_live_filters' => true,
                'available_filters' => array('school', 'duration', 'mode'),
                'default_sort' => 'score'
            ));
        }
    }
    
    /**
     * Load default data from JSON files
     */
    private function load_default_data() {
        // Load default questions
        $questions_file = SIT_RECOMMENDER_PLUGIN_DIR . 'data/default-questions.json';
        if (file_exists($questions_file) && !get_option('sit_recommender_questions')) {
            $questions_data = file_get_contents($questions_file);
            $questions = json_decode($questions_data, true);
            if ($questions) {
                add_option('sit_recommender_questions', $questions);
            }
        }
        
        // Load default department mapping
        $mapping_file = SIT_RECOMMENDER_PLUGIN_DIR . 'data/default-department-map.json';
        if (file_exists($mapping_file) && !get_option('sit_recommender_mapping')) {
            $mapping_data = file_get_contents($mapping_file);
            $mapping = json_decode($mapping_data, true);
            if ($mapping) {
                add_option('sit_recommender_mapping', $mapping);
            }
        }
    }
    
    /**
     * Create database tables if needed
     */
    private function create_tables() {
        global $wpdb;
        
        // Create statistics table
        $table_name = $wpdb->prefix . 'sit_statistics';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            data text,
            user_ip varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action_idx (action),
            KEY created_at_idx (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Log table creation for debugging
        error_log('SIT Plugin: Statistics table created/updated: ' . $table_name);
    }
}

// Initialize the plugin
function sit_program_recommender() {
    return SIT_Program_Recommender::get_instance();
}

// Start the plugin
sit_program_recommender();
