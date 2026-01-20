<?php

/**
 * Plugin Name: Sit Search
 * Description: A custom search plugin for Sit Search.
 * Version: 1.0
 * Author: Daxow
 * Author URI: https://daxow.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: study-in-turkiye-search
 * Domain Path: /languages
 * Requires at least: 5.2
 * Requires PHP: 7.2
 */


if (!defined('ABSPATH')) {
    exit;
}


define('STI_SEARCH_VERSION', '1.0.0');
define('STI_SEARCH_DIR', plugin_dir_path(__FILE__));
define('STI_SEARCH_URL', plugin_dir_url(__FILE__));
define('SIT_SEARCH_TEXT_DOMAIN', 'study-in-turkiye-search');
define('SIT_SEARCH_ASSETS', STI_SEARCH_URL . 'assets/');
define('STRIPE_PUBLIC_KEY', defined('SIT_STRIPE_PUBLIC_KEY') ? SIT_STRIPE_PUBLIC_KEY : '');
define('STRIPE_SECRET_KEY', defined('SIT_STRIPE_SECRET_KEY') ? SIT_STRIPE_SECRET_KEY : '');

// Supabase Configuration
define('SUPABASE_URL', defined('SIT_SUPABASE_URL') ? SIT_SUPABASE_URL : '');
define('SUPABASE_ANON_KEY', defined('SIT_SUPABASE_ANON_KEY') ? SIT_SUPABASE_ANON_KEY : '');
define('SUPABASE_SERVICE_ROLE_KEY', defined('SIT_SUPABASE_SERVICE_ROLE_KEY') ? SIT_SUPABASE_SERVICE_ROLE_KEY : '');
define('SUPABASE_BUCKET', defined('SIT_SUPABASE_BUCKET') ? SIT_SUPABASE_BUCKET : 'uploads');

/* GitHub Updater Configuration */
define('GITHUB_REPO_OWNER', 'daxowapp');
define('GITHUB_REPO_NAME', 'sitsearch');
define('GITHUB_ACCESS_TOKEN', ''); // Optional: Required for private repos

require 'vendor/autoload.php';


add_action('plugins_loaded', 'sit_search');

function sit_search()
{
    // Initialize GitHub Updater
    if (is_admin()) {
        $updater = new \SIT\Search\Services\GitHubUpdater(
            __FILE__,
            GITHUB_REPO_OWNER,
            GITHUB_REPO_NAME,
            GITHUB_ACCESS_TOKEN
        );
        $updater->init();
    }

    return \SIT\Search\App::get_instance();
}

// Plugin activation hook for database table creation - temporarily disabled
// register_activation_hook(__FILE__, 'sit_search_activate');

/*
function sit_search_activate()
{
    // Create embeddings table on activation
    require_once 'vendor/autoload.php';
    
    // Create the table directly without instantiating the service
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'sit_program_embeddings';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        program_id bigint(20) NOT NULL,
        embedding longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY program_id (program_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
*/
function allow_svg_uploads($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');
add_filter( 'http_request_timeout', 'custom_http_request_timeout', 10, 1 );

function custom_http_request_timeout( $timeout_value ) {
    return 60;
}

add_action('wp_head', 'custom_add_meta_description_for_cpt');

function custom_add_meta_description_for_cpt() {
    if (is_singular('sit-program')) {
        global $post;

        $current_uni_id=get_post_meta($post->ID, 'zh_university', true);
        $university = get_post($current_uni_id);
        $current_post_title = get_the_title($post->ID);
        $desc=get_post_meta($post->ID, 'Description', true);
        $keywords=get_post_meta($post->ID, 'Keywords', true);


        $description = $current_post_title.' '.$university->post_title.' '.$desc;
        $description = trim_description($description);
        echo '<meta name="description" content="' . $description . '">' . "\n";
        echo '<meta name="keywords" content="' . $keywords . '">' . "\n";
    }
}

function trim_description($text, $min = 150, $max = 160) {
    $text = strip_tags($text);
    $text = trim($text);

    if (strlen($text) >= $min && strlen($text) <= $max) {
        return $text;
    }

    if (strlen($text) > $max) {
        $trimmed = substr($text, 0, $max);
        $lastSpace = strrpos($trimmed, ' ');
        return substr($trimmed, 0, $lastSpace) . '...';
    }

    return $text;
}

add_filter( 'rank_math/frontend/title', function( $title ) {
    if ( is_singular( 'sit-program' ) ) {
        $id=get_the_ID();
        $current_uni_id=get_post_meta($id, 'zh_university', true);
        $university = get_post($current_uni_id);
        $custom_title = get_the_title() .' '. $university->post_title . ' - SIT Search';
        return $custom_title;
    }

    return $title;
});
