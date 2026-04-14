<?php

namespace SIT\Search;

use SIT\Search\Actions\RegisterMenu;
use SIT\Search\Actions\SearchProgramAjax;
use SIT\Search\Actions\GenerateEmbeddingsAjax;
use SIT\Search\Modules\Campus;
use SIT\Search\Modules\City;
use SIT\Search\Modules\Country;
use SIT\Search\Modules\Degree;
use SIT\Search\Modules\Language;
use SIT\Search\Modules\Faculty;
use SIT\Search\Modules\Program;
use SIT\Search\Modules\Speciality;
use SIT\Search\Modules\Lead;
use SIT\Search\Modules\University;
use SIT\Search\Services\Webhook;
use SIT\Search\Services\Zoho;
use SIT\Search\Shortcodes\ApplyNow;
use SIT\Search\Shortcodes\SearchProgram;
use SIT\Search\Shortcodes\ProgramArchive;
use SIT\Search\Shortcodes\ProgramSteps;
use SIT\Search\Shortcodes\SearchBar;
use SIT\Search\Shortcodes\SingleUniversity;
use SIT\Search\Shortcodes\TopUniversities;
use SIT\Search\Shortcodes\TrendingStudyAreas;
use SIT\Search\Shortcodes\Universities;
use SIT\Search\Shortcodes\UniversityCountries;
use SIT\Search\Shortcodes\FilterSort;
use SIT\Search\Shortcodes\Breadcrumb;
use SIT\Search\Shortcodes\Singleprogram;
use SIT\Search\Shortcodes\CampusFaculties;
use SIT\Search\Shortcodes\UniversityPrograms;
use SIT\Search\Shortcodes\UniversityGrid;
use SIT\Search\Shortcodes\AISearchAdmin;
use SIT\Search\Shortcodes\AiSearch;
use SIT\Search\Endpoints\ProgramEndpoint;
use SIT\Search\Endpoints\CountryEndpoint;
use SIT\Search\Endpoints\ProgramDataEndpoint;
use SIT\Search\Endpoints\CampusEndpoint;
use SIT\Search\Endpoints\UniversityDataEndpoint;
use SIT\Search\Endpoints\LanguageEndpoint;
use SIT\Search\Endpoints\DegreeEndpoint;
use SIT\Search\Endpoints\FacultyEndpoint;
use SIT\Search\Endpoints\SpecialityEndpoint;
use SIT\Search\Endpoints\CityEndpoint;

class App
{
    protected static $instance;
    /**
     * List of actions and their handlers
     *
     * @var array
     */
    protected array $actions = array(
        RegisterMenu::class,
        SearchProgramAjax::class,
        GenerateEmbeddingsAjax::class,
    );

    /**
     * List of filters and their handlers
     *
     * @var array
     */
    protected array $filters = array();
    /**
     * List of shortcodes and their handlers
     *
     * @var array
     */
    protected array $shortcodes = array(
        'sit_search_bar' => SearchBar::class,
        'sit_top_universities' => TopUniversities::class,
        'trending_study_areas' => TrendingStudyAreas::class,
        'sit_university_countries' => UniversityCountries::class,
        'filter_sort' => FilterSort::class,
        'bread_crump' => Breadcrumb::class,
        'single_program' => Singleprogram::class,
        'single_univesity' => SingleUniversity::class,
        'program_steps' => ProgramSteps::class,
        'apply_now' => ApplyNow::class,
        'universities' => Universities::class,
        'campus_faculties' => CampusFaculties::class,
        'university_program' => UniversityPrograms::class,
        'program_archive' => ProgramArchive::class,
        'search_program' => SearchProgram::class,
        'university_grid' => UniversityGrid::class,
        'ai_search_admin' => AISearchAdmin::class,
        'sit_ai_search' => AiSearch::class,
        'single_university' => SingleUniversity::class,
    );

    public function register_endpoints(): void
    {
        add_action('rest_api_init', function () {
            (new Endpoints\ProgramEndpoint)->register_routes();
            (new Endpoints\CountryEndpoint)->register_routes();
            (new Endpoints\ProgramDataEndpoint)->register_routes();
            (new Endpoints\CampusEndpoint)->register_routes();
            (new Endpoints\UniversityDataEndpoint)->register_routes();
            (new Endpoints\LanguageEndpoint)->register_routes();
            (new Endpoints\DegreeEndpoint)->register_routes();
            (new Endpoints\FacultyEndpoint)->register_routes();
            (new Endpoints\SpecialityEndpoint)->register_routes();
            (new Endpoints\CityEndpoint)->register_routes();
            (new Endpoints\SearchEndpoint)->register_routes();
            (new Endpoints\AiSearchEndpoint)->register_routes();
            (new Endpoints\AiSearchIndexEndpoint)->register_routes();
            (new Endpoints\FaqEndpoint)->register_routes();
        });
    }

    public function __construct()
    {
        $this->register_endpoints();

        // Initialize Supabase Sync Endpoint
        new \SIT\Search\Endpoints\SupabaseSyncEndpoint();

        // Initialize Manual Sync Admin page
        new \SIT\Search\Actions\ManualSyncAdmin();

        // Initialize Featured Universities Admin page
        new \SIT\Search\Actions\FeaturedUniversityAdmin();

        // Initialize University Status Admin page
        new \SIT\Search\Actions\UniversityStatusAdmin();

        // Initialize AI Settings Admin page
        new \SIT\Search\Actions\AiSettingsAdmin();

        // Initialize GEO Settings Admin page (Generative Engine Optimization)
        new \SIT\Search\Actions\GeoSettingsAdmin();

        // Initialize Search Queries AJAX handlers
        new \SIT\Search\Actions\SearchQueriesAjax();

        // Initialize Native Meta Boxes
        new \SIT\Search\Actions\UniversityMetaBoxes();

        $this->setup_hooks();
        $this->setup_shortcodes();

        add_action('init', array($this, 'track_recent_searches'));
        
        // Clear active university cache when a university is saved
        add_action('save_post_sit-university', array($this, 'clear_university_cache'));
        add_action('acf/save_post', array($this, 'clear_university_cache_on_acf_save'));

        add_action('wp_enqueue_scripts', array($this, 'setup_assets'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        
        // Add defer attribute to non-critical scripts for better performance
        add_filter('script_loader_tag', array($this, 'add_defer_attribute'), 10, 2);
        
        // Optimize Google Analytics loading
        add_filter('script_loader_tag', array($this, 'optimize_google_analytics'), 10, 2);
        
        // Add DNS prefetch for external resources
        add_action('wp_head', array($this, 'add_resource_hints'), 1);


        add_filter('manage_edit-sit-program_columns', function ($columns) {
            $columns['university'] = __('University', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_country'] = __('Country', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_faculty'] = __('Faculty', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_speciality'] = __('Speciality', SIT_SEARCH_TEXT_DOMAIN);

            $date_column = $columns['date'];
            unset($columns['date']);
            $columns['date'] = $date_column;
            return $columns;
        });

        add_filter('manage_edit-sit-campus_columns', function ($columns) {
            $columns['university'] = __('University', SIT_SEARCH_TEXT_DOMAIN);
            $date_column = $columns['date'];
            unset($columns['date']);
            $columns['date'] = $date_column;
            return $columns;
        });

        add_filter('manage_edit-sit-university_columns', function ($columns) {
            $columns['sit_program'] = __('Programs', SIT_SEARCH_TEXT_DOMAIN);
            $columns['sit_country'] = __('Country', SIT_SEARCH_TEXT_DOMAIN);
            $date_column = $columns['date'];
            unset($columns['date']);
            $columns['date'] = $date_column;
            return $columns;
        });

        add_action('manage_sit-university_posts_custom_column', function ($column, $post_id) {

            if ($column === 'sit_program') {
                $args = array(
                    'post_type' => 'sit-program',
                    'meta_query' => array(
                        array(
                            'key' => 'zh_university',
                            'value' => $post_id,
                            'compare' => '='
                        )
                    )
                );

                $query = new \WP_Query($args);
                $programs = $query->get_posts();

                if ($programs) {
                    $program_titles = array_map(function ($program) {
                        return '<strong><a class="row-title"  href="/wp-admin/post.php?post=' . $program->ID . '&action=edit">' . $program->post_title . '</a></strong>';
                    }, $programs);
                    echo join('<br>', $program_titles);
                } else {
                    echo __('No Programs', SIT_SEARCH_TEXT_DOMAIN);
                }

            }

            if ($column === 'sit_country') {
                $terms = get_the_terms($post_id, 'sit-country');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No Country', SIT_SEARCH_TEXT_DOMAIN);
            }

        }, 10, 2);

        add_action('manage_sit-program_posts_custom_column', function ($column, $post_id) {
            if ($column === 'university') {
                $uni_post_id = get_post_meta($post_id, 'zh_university', true);
                if ($uni_post_id) {
                    $post_title = get_the_title($uni_post_id);
                    echo $post_title ?
                        '<strong><a class="row-title" href="/wp-admin/post.php?post=' . $uni_post_id . '&action=edit" aria-label="' . $post_title . '">' . $post_title . '</a></strong>'
                        : __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                } else {
                    echo __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                }
            }


            if ($column === 'sit_country') {
                $terms = get_the_terms($post_id, 'sit-country');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No Country', SIT_SEARCH_TEXT_DOMAIN);
            }

            if ($column === 'sit_speciality') {
                $terms = get_the_terms($post_id, 'sit-speciality');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No speciality', SIT_SEARCH_TEXT_DOMAIN);
            }


            if ($column === 'sit_faculty') {
                $terms = get_the_terms($post_id, 'sit-faculty');
                echo $terms ? esc_html(join(', ', wp_list_pluck($terms, 'name'))) : __('No faculty', SIT_SEARCH_TEXT_DOMAIN);
            }

        }, 10, 2);

        add_action('manage_sit-campus_posts_custom_column', function ($column, $post_id) {
            if ($column === 'university') {
                $uni_post_id = get_post_meta($post_id, 'zh_university', true);
                if ($uni_post_id) {
                    $post_title = get_the_title($uni_post_id);
                    echo $post_title ?
                        '<strong><a class="row-title" href="/wp-admin/post.php?post=' . $uni_post_id . '&action=edit" aria-label="' . $post_title . '">' . $post_title . '</a></strong>'
                        : __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                } else {
                    echo __('No Data', SIT_SEARCH_TEXT_DOMAIN);
                }
            }

        }, 10, 2);
        add_filter('manage_edit-sit-campus_sortable_columns', function ($sortable_columns) {
            $sortable_columns['university'] = 'university';
            return $sortable_columns;
        });

        add_filter('manage_edit-sit-program_sortable_columns', function ($sortable_columns) {
            $sortable_columns['university'] = 'university';
            $sortable_columns['sit_country'] = 'sit_country';
            return $sortable_columns;
        });

        add_action('pre_get_posts', function ($query) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            $orderby = $query->get('orderby');

            if ($orderby === 'university') {
                $query->set('meta_key', 'zh_university');
                $query->set('orderby', 'meta_value');
            }
        });


        // Initialize GitHub Updater
        if (is_admin() && defined('GITHUB_REPO_OWNER') && defined('GITHUB_REPO_NAME')) {
            // Already initialized in sit-search.php hook
        }
    }

    /**
     * Set up hooks
     */
    public function setup_hooks()
    {
        foreach ($this->actions as $handler) {
            foreach ($handler::$hooks as $action) {
                add_action($action, new $handler, $handler::$priority, $handler::$arguments);
            }
        }

        foreach ($this->filters as $handler) {
            foreach ($handler::$hooks as $filter) {
                add_filter($filter, new $handler, $handler::$priority, $handler::$arguments);
            }
        }
    }

    /**
     * Set up shortcodes
     */
    public function setup_shortcodes()
    {
        foreach ($this->shortcodes as $shortcode => $handler) {
            add_shortcode($shortcode, new $handler);
        }
    }

    /**
     * Get current instance of app
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Track recent searches via cookie for UX.
     * Replaces the old debugging() method.
     */
    public function track_recent_searches()
    {
        if (is_admin()) {
            return;
        }

        $search = [];
        if (isset($_GET['speciality'])) {
            $search['speciality'] = is_array($_GET['speciality']) ? array_map('sanitize_text_field', $_GET['speciality']) : sanitize_text_field($_GET['speciality']);
        }

        if (isset($_GET['country'])) {
            $search['country'] = is_array($_GET['country']) ? array_map('sanitize_text_field', $_GET['country']) : sanitize_text_field($_GET['country']);
        }

        if (isset($_GET['level'])) {
            $search['level'] = is_array($_GET['level']) ? array_map('intval', $_GET['level']) : intval($_GET['level']);
        }

        $recent_search = isset($_COOKIE['recent_search']) ? json_decode(stripslashes($_COOKIE['recent_search']), true) : [];
        if (!is_array($recent_search)) {
            $recent_search = [];
        }

        if (!empty($search) && !in_array($search, $recent_search)) {
            $recent_search[] = $search;
            // Keep only last 10 recent searches
            $recent_search = array_slice($recent_search, -10);
        }

        if (!headers_sent()) {
            setcookie('recent_search', json_encode($recent_search), time() + (86400 * 30), '/');
        }

        // Allow admins to expire Zoho token via GET
        if (isset($_GET['expire_token']) && current_user_can('manage_options')) {
            delete_transient('zoho_access_token');
        }
    }

    public function setup_assets()
    {
        // Use the centralized plugin version constant
        $plugin_version = STI_SEARCH_VERSION;
        
        // --- REGISTER all assets (available for conditional enqueuing) ---
        
        // OwlCarousel2 - For all carousels
        wp_register_style('sit-owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', [], '2.3.4');
        wp_register_style('sit-owl-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', [], '2.3.4');
        wp_register_script('sit-owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', ['jquery'], '2.3.4', false);
        
        // Select2 - For search dropdowns
        wp_register_style('sit-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13');
        wp_register_script('sit-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);

        wp_register_style('sit-search', STI_SEARCH_URL . 'assets/css/sit-search.css', [], $plugin_version);
        wp_register_style('sit-featured', STI_SEARCH_URL . 'assets/css/featured.css', [], $plugin_version);
        wp_register_style('guides-css', STI_SEARCH_URL . 'assets/css/guides.css', [], $plugin_version);
        wp_register_script('sit-search', STI_SEARCH_URL . 'assets/js/main.js', ['jquery', 'sit-owl-carousel', 'sit-select2'], $plugin_version, true);
        wp_register_script('sit-animations', STI_SEARCH_URL . 'assets/js/sit-animations.js', [], $plugin_version, true);
        
        wp_register_script('sit-ai-search', STI_SEARCH_URL . 'assets/js/ai-search.js', ['jquery'], $plugin_version, true);
        
        wp_register_script('guides-js', STI_SEARCH_URL . 'assets/js/guides.js', ['jquery'], $plugin_version, true);

        // --- CONDITIONALLY ENQUEUE only when SIT shortcodes are on the page ---
        $this->maybe_enqueue_assets();
    }

    /**
     * Conditionally enqueue SIT Search assets only on pages that use our shortcodes.
     * This prevents OwlCarousel, Select2, AI search JS, and plugin CSS from
     * loading on non-search pages — improving Core Web Vitals site-wide.
     */
    private function maybe_enqueue_assets()
    {
        if (is_admin()) {
            return;
        }

        $should_enqueue = false;
        
        // All SIT Search shortcodes that require frontend assets
        $sit_shortcodes = [
            'sit_search_bar', 'sit_top_universities', 'trending_study_areas',
            'sit_university_countries', 'filter_sort', 'bread_crump',
            'single_program', 'single_univesity', 'program_steps',
            'apply_now', 'universities', 'campus_faculties',
            'university_program', 'program_archive', 'search_program',
            'university_grid', 'sit_ai_search',
        ];

        // Check singular pages/posts for shortcode presence
        if (is_singular()) {
            global $post;
            if ($post && !empty($post->post_content)) {
                foreach ($sit_shortcodes as $shortcode) {
                    if (has_shortcode($post->post_content, $shortcode)) {
                        $should_enqueue = true;
                        break;
                    }
                }
            }
        }

        // Always load on taxonomy archives, search results, and single program/university pages
        if (is_tax() || is_search() || is_post_type_archive(['sit-program', 'sit-university'])
            || is_singular(['sit-program', 'sit-university', 'sit-campus'])) {
            $should_enqueue = true;
        }
        
        // Fallback: known search/results URLs
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($request_uri, '/results') !== false || strpos($request_uri, '/universities') !== false) {
            $should_enqueue = true;
        }

        if (!$should_enqueue) {
            return;
        }

        // Enqueue everything
        wp_enqueue_style('sit-owl-carousel');
        wp_enqueue_style('sit-owl-theme');
        wp_enqueue_script('sit-owl-carousel');
        wp_enqueue_style('sit-select2');
        wp_enqueue_script('sit-select2');
        wp_enqueue_style('sit-search');
        wp_enqueue_style('sit-featured');
        wp_enqueue_style('guides-css');
        wp_enqueue_script('sit-search');
        wp_enqueue_script('sit-animations');
        wp_enqueue_script('sit-ai-search');
        wp_enqueue_script('guides-js');
        wp_enqueue_script('sit-utm-tracker', STI_SEARCH_URL . 'assets/js/utm-tracker.js', [], STI_SEARCH_VERSION, true);

        wp_localize_script('sit-ai-search', 'sit_ai_search_vars', [
            'api_url' => rest_url('sit-search/v1/'),
            'results_url' => home_url('/results/')
        ]);
        
        wp_localize_script('sit-search', 'upd_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('program_search_nonce')
        ]);
    }


    public function admin_assets()
    {
        $plugin_version = STI_SEARCH_VERSION;
        
        // Enqueue Select2 for Admin
        wp_enqueue_style('sit-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13');
        wp_enqueue_script('sit-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);

        // Admin Styles
        wp_enqueue_style('sit-search-admin', STI_SEARCH_URL . 'assets/css/sit-search-admin.css', [], $plugin_version);
        wp_enqueue_style('sit-admin-redesign', STI_SEARCH_URL . 'assets/css/sit-admin-redesign.css', [], $plugin_version);
        
        // Admin Script (Localize common vars)
        // wp_enqueue_script('sit-admin-js', STI_SEARCH_URL . 'assets/js/admin.js', ['jquery', 'sit-select2'], $plugin_version, true);
    }
    
    /**
     * Add defer attribute to non-critical scripts for better performance
     */
    public function add_defer_attribute($tag, $handle)
    {
        // List of scripts that can be deferred (not critical for initial render)
        // Note: Removed sit-owl-carousel and sit-select2 as they're needed for carousel initialization
        $defer_scripts = [
            'guides-js'
        ];
        
        // Add defer attribute if script is in the list
        if (in_array($handle, $defer_scripts)) {
            return str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Optimize Google Analytics script loading
     * Ensures GA loads asynchronously and doesn't block page rendering
     */
    public function optimize_google_analytics($tag, $handle)
    {
        // Check if this is a Google Analytics or gtag script
        if (strpos($handle, 'google') !== false || strpos($handle, 'gtag') !== false || strpos($tag, 'googletagmanager') !== false || strpos($tag, 'google-analytics') !== false) {
            // Ensure async attribute is present
            if (strpos($tag, 'async') === false) {
                $tag = str_replace(' src', ' async src', $tag);
            }
            
            // Add defer as well for better performance
            if (strpos($tag, 'defer') === false) {
                $tag = str_replace(' src', ' defer src', $tag);
            }
        }
        
        return $tag;
    }
    
    /**
     * Add DNS prefetch and preconnect hints for external resources
     * This speeds up connections to external domains
     */
    public function add_resource_hints()
    {
        ?>
        <!-- DNS Prefetch for faster external resource loading -->
        <link rel="dns-prefetch" href="//www.google-analytics.com">
        <link rel="dns-prefetch" href="//www.googletagmanager.com">
        <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
        <link rel="dns-prefetch" href="//code.jquery.com">
        
        <!-- Preconnect for critical external resources -->
        <link rel="preconnect" href="https://www.google-analytics.com" crossorigin>
        <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
        <?php
    }
    
    // session_start() removed — WordPress does not use PHP sessions.
    // Use WordPress transients, user meta, or cookies instead.
    
    /**
     * Clear active university cache when a university post is saved
     */
    public function clear_university_cache($post_id)
    {
        if (get_post_type($post_id) === 'sit-university') {
            Services\CachedData::clear_university_cache();
        }
    }
    
    /**
     * Clear active university cache when ACF fields are saved
     */
    public function clear_university_cache_on_acf_save($post_id)
    {
        if (get_post_type($post_id) === 'sit-university') {
            Services\CachedData::clear_university_cache();
        }
    }
}