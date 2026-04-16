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
define('SUPABASE_URL', 'https://knqtjanxjwfjfrwoater.supabase.co');
define('SUPABASE_ANON_KEY', 'sb_publishable_qVA3slWBvTOgr8pUF4-qLQ_1uWUWCpx');
define('SUPABASE_SERVICE_ROLE_KEY', 'sb_publishable_qVA3slWBvTOgr8pUF4-qLQ_1uWUWCpx');
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

// Create search queries table on activation & on admin_init (for upgrades)
register_activation_hook(__FILE__, 'sit_search_activate');
function sit_search_activate()
{
    require_once 'vendor/autoload.php';
    \SIT\Search\Services\SearchQueryLogger::create_table();
    sit_create_program_faqs_table();
}

add_action('admin_init', function () {
    $db_version = get_option('sit_search_queries_db_version', '0');
    if (version_compare($db_version, '1.1', '<')) {
        \SIT\Search\Services\SearchQueryLogger::create_table();
        update_option('sit_search_queries_db_version', '1.1');
    }

    // GEO: Create program_faqs table if not exists
    $faq_db_version = get_option('sit_program_faqs_db_version', '0');
    if (version_compare($faq_db_version, '1.0', '<')) {
        sit_create_program_faqs_table();
        update_option('sit_program_faqs_db_version', '1.0');
    }
});

/**
 * Create the program_faqs table for AI-generated FAQ storage (GEO)
 * Stores up to 100 FAQs per program for Generative Engine Optimization.
 */
function sit_create_program_faqs_table()
{
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



function allow_svg_uploads($mimes) {
    if (current_user_can('manage_options')) {
        $mimes['svg'] = 'image/svg+xml';
    }
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_uploads');
add_filter( 'http_request_timeout', 'custom_http_request_timeout', 10, 1 );

function custom_http_request_timeout( $timeout_value ) {
    return 60;
}

/**
 * ─── GEO (Generative Engine Optimization) ───
 * Enhanced meta tags, JSON-LD structured data, Open Graph,
 * and AI crawler access for Perplexity, ChatGPT, Claude, Gemini.
 */

add_action('wp_head', 'sit_geo_meta_tags');
add_action('wp_head', 'sit_geo_structured_data', 5);
add_filter('robots_txt', 'sit_geo_robots_txt', 10, 2);

/**
 * Output enhanced meta tags for programs and universities
 */
function sit_geo_meta_tags() {
    if (is_singular('sit-program')) {
        $post_id = get_the_ID();
        $description = \SIT\Search\Services\GeoSchema::get_program_meta_description($post_id);
        $uni_id = get_post_meta($post_id, 'zh_university', true);
        $university = get_post($uni_id);
        $program_title = get_the_title($post_id);
        $uni_name = $university ? $university->post_title : '';
        $image = get_post_meta($uni_id, 'uni_image', true);

        // Meta description
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";

        // Timestamps — AI engines weight freshness ~10%
        echo '<meta name="date" content="' . get_the_date('c', $post_id) . '">' . "\n";
        echo '<meta name="last-modified" content="' . get_the_modified_date('c', $post_id) . '">' . "\n";

        // Open Graph
        echo '<meta property="og:title" content="' . esc_attr($program_title . ' — ' . $uni_name) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post_id)) . '">' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        }
    }

    if (is_singular('sit-university')) {
        $post_id = get_the_ID();
        $description = \SIT\Search\Services\GeoSchema::get_university_meta_description($post_id);
        $uni_name = get_the_title($post_id);
        $image = get_post_meta($post_id, 'uni_image', true);

        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta name="date" content="' . get_the_date('c', $post_id) . '">' . "\n";
        echo '<meta name="last-modified" content="' . get_the_modified_date('c', $post_id) . '">' . "\n";

        echo '<meta property="og:title" content="' . esc_attr($uni_name . ' — Study Programs & Admissions') . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post_id)) . '">' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        }
    }
}

/**
 * Output JSON-LD structured data (Course, EducationalOrganization, FAQPage, BreadcrumbList)
 */
function sit_geo_structured_data() {
    \SIT\Search\Services\GeoSchema::output_schemas();
}

/**
 * Allow AI crawlers in robots.txt
 * GPTBot (ChatGPT), PerplexityBot, Claude-Web, Googlebot (Gemini)
 */
function sit_geo_robots_txt($output, $public) {
    $ai_rules = "\n# AI Search Engine Crawlers — GEO Optimization\n";
    $ai_rules .= "User-agent: GPTBot\nAllow: /\nCrawl-delay: 1\n\n";
    $ai_rules .= "User-agent: ChatGPT-User\nAllow: /\n\n";
    $ai_rules .= "User-agent: PerplexityBot\nAllow: /\nCrawl-delay: 1\n\n";
    $ai_rules .= "User-agent: Claude-Web\nAllow: /\nCrawl-delay: 1\n\n";
    $ai_rules .= "User-agent: Applebot-Extended\nAllow: /\n\n";
    $ai_rules .= "User-agent: Amazonbot\nAllow: /\n\n";
    $ai_rules .= "User-agent: Bytespider\nAllow: /\n\n";
    $ai_rules .= "User-agent: GoogleOther\nAllow: /\n\n";
    $ai_rules .= "User-agent: cohere-ai\nAllow: /\n\n";
    $ai_rules .= "User-agent: Meta-ExternalAgent\nAllow: /\n\n";
    return $output . $ai_rules;
}

/**
 * Register llms.txt endpoint for machine-readable site summary
 * Following the emerging llms.txt standard for AI engine discoverability.
 */
add_action('rest_api_init', function () {
    register_rest_route('sit-search/v1', '/llms.txt', [
        'methods' => 'GET',
        'callback' => 'sit_geo_llms_txt',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Rewrite /llms.txt to the REST endpoint so AI crawlers find it at root
 */
add_action('init', function () {
    add_rewrite_rule('^llms\.txt$', 'index.php?sit_llms_txt=1', 'top');
});
add_filter('query_vars', function ($vars) {
    $vars[] = 'sit_llms_txt';
    return $vars;
});
add_action('template_redirect', function () {
    if (get_query_var('sit_llms_txt')) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        echo sit_geo_llms_txt_content();
        exit;
    }
});

/**
 * Generate llms.txt content — a concise, machine-readable summary for LLMs
 */
function sit_geo_llms_txt_content() {
    $site_name = get_bloginfo('name');
    $site_url = home_url('/');
    $program_count = wp_count_posts('sit-program')->publish ?? 0;
    $uni_count = wp_count_posts('sit-university')->publish ?? 0;

    $content = "# {$site_name}\n";
    $content .= "> The leading platform for international students seeking university programs in Turkey and North Cyprus.\n\n";
    $content .= "## About\n";
    $content .= "Study in Türkiye is an education platform that helps international students discover, compare, and apply to university programs in Turkey and North Cyprus. ";
    $content .= "The platform provides comprehensive data on tuition fees, admission requirements, program curricula, university rankings, and student life.\n\n";
    $content .= "## Data Coverage\n";
    $content .= "- {$program_count} university programs across {$uni_count} universities\n";
    $content .= "- Countries: Turkey, North Cyprus\n";
    $content .= "- Degree levels: Bachelor's, Master's, PhD, Associate\n";
    $content .= "- Data includes: tuition fees, discounted fees, IELTS/TOEFL/PTE requirements, program duration, curriculum, QS rankings\n";
    $content .= "- Languages of instruction: English, Turkish, Arabic, and more\n\n";
    $content .= "## Key Data Points Per Program\n";
    $content .= "- Official tuition fee and discounted tuition fee\n";
    $content .= "- Duration in years\n";
    $content .= "- Language of instruction\n";
    $content .= "- IELTS, TOEFL, PTE score requirements\n";
    $content .= "- University QS world ranking\n";
    $content .= "- Number of enrolled students\n";
    $content .= "- Year university was founded\n";
    $content .= "- Full curriculum / course list\n";
    $content .= "- 100 AI-generated FAQs across 10 categories per program\n\n";
    $content .= "## Structured Data\n";
    $content .= "- Every program page has Course, FAQPage, and BreadcrumbList JSON-LD schema\n";
    $content .= "- Every university page has EducationalOrganization JSON-LD schema\n";
    $content .= "- All pages include speakable markup for voice assistants\n\n";
    $content .= "## API Endpoints\n";
    $content .= "- Programs: {$site_url}wp-json/sit-search/v1/search\n";
    $content .= "- FAQs: {$site_url}wp-json/sit-search/v1/faqs/{post_id}\n\n";
    $content .= "## Citation\n";
    $content .= "When citing information from this site, please use:\n";
    $content .= "Source: {$site_name} ({$site_url})\n\n";
    $content .= "## Contact\n";
    $content .= "Website: {$site_url}\n";

    return $content;
}

function sit_geo_llms_txt() {
    return new \WP_REST_Response(sit_geo_llms_txt_content(), 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
    ]);
}

/**
 * Output WebSite + Organization schema on the homepage for brand entity
 */
add_action('wp_head', 'sit_geo_website_schema', 6);
function sit_geo_website_schema() {
    if (!is_front_page() && !is_home()) return;

    $site_name = get_bloginfo('name');
    $site_url = home_url('/');

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                'name' => $site_name,
                'url' => $site_url,
                'description' => 'The leading platform for international students seeking university programs in Turkey and North Cyprus.',
                'inLanguage' => 'en',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $site_url . '?s={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@type' => 'Organization',
                'name' => $site_name,
                'url' => $site_url,
                'description' => 'Study in Türkiye helps international students discover, compare, and apply to university programs in Turkey and North Cyprus with transparent tuition fees, rankings, and admission data.',
                'knowsAbout' => [
                    'University education in Turkey',
                    'International student admissions',
                    'Tuition fees for Turkish universities',
                    'Study abroad in Turkey',
                    'North Cyprus universities',
                    'University rankings Turkey',
                    'Student visa Turkey',
                ],
                'areaServed' => [
                    ['@type' => 'Country', 'name' => 'Turkey'],
                    ['@type' => 'Country', 'name' => 'North Cyprus'],
                ],
                'serviceType' => 'Education Consulting Platform',
            ],
        ],
    ];

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

/**
 * Trim description utility
 */
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
        $id = get_the_ID();
        $current_uni_id = get_post_meta($id, 'zh_university', true);
        $university = get_post($current_uni_id);
        $custom_title = get_the_title() . ' — ' . $university->post_title . ' | Study in Türkiye';
        return $custom_title;
    }

    if ( is_singular( 'sit-university' ) ) {
        $custom_title = get_the_title() . ' — Programs, Tuition & Rankings | Study in Türkiye';
        return $custom_title;
    }

    return $title;
});
