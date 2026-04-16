<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\FeaturedUniversity;
use SIT\Search\Services\URLHelper;

/**
 * Search Endpoint - Main API for program filtering and search
 * This replaces the frontend FilterSort shortcode logic for Next.js
 */
class SearchEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        // Main search/filter endpoint
        register_rest_route('sit-search/v1', '/search', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_search'],
            'permission_callback' => '__return_true',
        ]);

        // Single program by slug
        register_rest_route('sit-search/v1', '/program/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_get_program_by_slug'],
            'permission_callback' => '__return_true',
        ]);

        // Single university by slug
        register_rest_route('sit-search/v1', '/university/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_get_university_by_slug'],
            'permission_callback' => '__return_true',
        ]);

        // Featured universities
        register_rest_route('sit-search/v1', '/featured-universities', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_get_featured_universities'],
            'permission_callback' => '__return_true',
        ]);

        // Filter options (all dropdowns in one call)
        register_rest_route('sit-search/v1', '/filter-options', [
            'methods'  => 'GET',
            'callback' => [$this, 'handle_get_filter_options'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Main search endpoint - handles all filtering
     */
    public function handle_search($request): \WP_REST_Response
    {
        // Parameter caching key
        $params = $request->get_params();
        ksort($params);
        $cache_version = get_option('sit_search_cache_version', '1');
        $cache_key = 'sit_search_v2_' . md5($cache_version . json_encode($params));

        $cached_response = get_transient($cache_key);
        if ($cached_response !== false) {
            return rest_ensure_response($cached_response);
        }

        // Get all filter parameters with sanitization
        $page = absint($request->get_param('page') ?: 1);
        $per_page = absint($request->get_param('per_page') ?: 21);
        $sort = sanitize_text_field($request->get_param('sort') ?: 'featured');
        $search = sanitize_text_field($request->get_param('search') ?: '');
        
        // Taxonomy filters
        $degree = $request->get_param('level');
        $country = $request->get_param('country');
        $speciality = $request->get_param('speciality');
        $language = $request->get_param('language');
        $city = $request->get_param('city');
        $university = $request->get_param('university');
        
        // Other filters
        $duration = $request->get_param('duration');
        $feeFilter = $request->get_param('feeFilter');
        $isScholarShip = $request->get_param('isScholarShip');

        // Build tax_query
        $tax_query = ['relation' => 'AND'];
        
        // Degree filter
        if (!empty($degree)) {
            $degrees = is_array($degree) ? $degree : [$degree];
            $tax_query[] = [
                'taxonomy' => 'sit-degree',
                'field'    => 'term_id',
                'terms'    => array_map('intval', $degrees),
                'operator' => 'IN',
            ];
        }

        // Country filter
        if (!empty($country)) {
            $tax_query[] = [
                'taxonomy' => 'sit-country',
                'field'    => 'term_id',
                'terms'    => intval($country),
            ];
        }

        // Speciality filter
        if (!empty($speciality)) {
            $tax_query[] = [
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => intval($speciality),
            ];
        }

        // Language filter
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = [];
            foreach ($languages as $lang) {
                if (is_numeric($lang)) {
                    $language_terms[] = intval($lang);
                } else {
                    $term = get_term_by('name', trim($lang), 'sit-language');
                    if ($term) $language_terms[] = $term->term_id;
                }
            }
            if (!empty($language_terms)) {
                $tax_query[] = [
                    'taxonomy' => 'sit-language',
                    'field'    => 'term_id',
                    'terms'    => $language_terms,
                    'operator' => 'IN',
                ];
            }
        }

        // City filter
        if (!empty($city)) {
            $cities = is_array($city) ? $city : [$city];
            $tax_query[] = [
                'taxonomy' => 'sit-city',
                'field'    => 'term_id',
                'terms'    => array_map('intval', $cities),
                'operator' => 'IN',
            ];
        }

        // Build meta_query
        $meta_query = ['relation' => 'AND'];

        // Fee filter
        if (!empty($feeFilter)) {
            $feeRange = explode(' - ', $feeFilter);
            if (count($feeRange) == 2) {
                $meta_query[] = [
                    'key'     => 'official_tuition',
                    'value'   => [intval($feeRange[0]), intval($feeRange[1])],
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ];
            }
        }

        // Duration filter
        if (!empty($duration)) {
            $dur = intval(explode(' ', $duration)[0]);
            if ($dur >= 4) {
                $meta_query[] = [
                    'key'     => 'study_years',
                    'value'   => 4,
                    'type'    => 'NUMERIC',
                    'compare' => '>=',
                ];
            } else {
                $meta_query[] = [
                    'key'     => 'study_years',
                    'value'   => $dur,
                    'type'    => 'NUMERIC',
                    'compare' => '=',
                ];
            }
        }

        // Scholarship filter
        if (!empty($isScholarShip) && $isScholarShip === 'Yes') {
            $meta_query[] = [
                'key'     => 'scholarship_inclusion',
                'value'   => '',
                'compare' => '!=',
            ];
        }

        // Build main query args
        $args = [
            'post_type'      => 'sit-program',
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Get all for sorting, paginate later
            's'              => $search,
        ];

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        // Execute query
        $query = new \WP_Query($args);
        $programs_raw = $query->get_posts();

        // --- OPTIMIZATION: BATCH CACHE PROGRAMS & UNIVERSITIES ---
        $program_ids = wp_list_pluck($programs_raw, 'ID');
        if (!empty($program_ids)) {
            update_meta_cache('post', $program_ids);
            update_object_term_cache($program_ids, ['sit-degree', 'sit-language', 'sit-speciality', 'sit-faculty', 'sit-city']);
        }

        $uni_ids = [];
        foreach ($programs_raw as $program) {
            $u_id = get_post_meta($program->ID, 'zh_university', true);
            if ($u_id) {
                $uni_ids[] = $u_id;
            }
        }
        $unique_uni_ids = array_unique($uni_ids);
        
        if (!empty($unique_uni_ids)) {
            // Bulk cache university post objects, post meta, and terms in minimal queries
            _prime_post_caches($unique_uni_ids, true, true);
            update_object_term_cache($unique_uni_ids, ['sit-country', 'sit-city']);
        }

        $featured_service = new FeaturedUniversity();

        // Map and filter by university if needed
        $mapped_programs = [];
        $seen_ids = [];
        $selected_universities = !empty($university) ? (is_array($university) ? $university : [$university]) : [];

        foreach ($programs_raw as $program) {
            if (in_array($program->ID, $seen_ids)) continue;

            $uni_id = get_post_meta($program->ID, 'zh_university', true);
            $uni = get_post($uni_id); // Now instantly loads from memory cache
            if (!$uni) continue;

            // Filter by university name if specified
            if (!empty($selected_universities) && !in_array($uni->post_title, $selected_universities)) {
                continue;
            }

            $is_featured = $featured_service->isFeatured($uni_id);
            
            // Get effective fee for sorting
            $official_fee = get_post_meta($program->ID, 'official_tuition', true) ?: get_post_meta($program->ID, 'Official_Tuition', true);
            $discounted_fee = get_post_meta($program->ID, 'discounted_tuition', true) ?: get_post_meta($program->ID, 'Discounted_Tuition', true);
            $advanced_discount = get_post_meta($program->ID, 'advanced_discount', true) ?: get_post_meta($program->ID, 'Advanced_Discount', true);
            
            $effective_fee = !empty($discounted_fee) ? $discounted_fee : (!empty($advanced_discount) ? $advanced_discount : $official_fee);

            $mapped_programs[] = [
                'id'                => $program->ID,
                'slug'              => $program->post_name,
                'title'             => $program->post_title,
                'description'       => get_post_meta($program->ID, 'Description', true) ?: '',
                'link'              => get_permalink($program->ID),
                'university_id'     => $uni_id,
                'university_name'   => $uni->post_title,
                'university_slug'   => $uni->post_name,
                'country'           => $this->get_term_name($uni->ID, 'sit-country'),
                'city'              => $this->get_term_name($program->ID, 'sit-city'),
                'language'          => $this->get_term_name($program->ID, 'sit-language'),
                'degree'            => $this->get_term_name($program->ID, 'sit-degree'),
                'fee'               => $official_fee,
                'discounted_fee'    => $discounted_fee,
                'advanced_discount' => $advanced_discount,
                'effective_fee'     => $effective_fee,
                'currency'          => get_post_meta($program->ID, 'tuition_currency', true) ?: get_post_meta($program->ID, 'Tuition_Currency', true) ?: 'USD',
                'duration'          => get_post_meta($program->ID, 'study_years', true) ?: get_post_meta($program->ID, 'Study_Years', true),
                'ranking'           => get_post_meta($uni->ID, 'qs_rank', true) ?: get_post_meta($uni->ID, 'QS_Rank', true),
                'is_featured'       => $is_featured,
                'university_priority' => get_post_meta($uni->ID, 'university_priority', true) ?: 0,
                'featured_priority' => get_post_meta($uni->ID, 'sit_featured_priority', true) ?: 0,
                'views_count'       => get_post_meta($program->ID, 'views_count', true) ?: 0,
                'date'              => $program->post_date,
                'image_url'         => $this->get_university_image($uni->ID),
            ];
            $seen_ids[] = $program->ID;
        }

        // Sort programs
        usort($mapped_programs, function($a, $b) use ($sort) {
            // Featured first
            if ($a['is_featured'] !== $b['is_featured']) {
                return $b['is_featured'] - $a['is_featured'];
            }
            
            // Priority
            $priority_a = max(intval($a['university_priority']), intval($a['featured_priority']));
            $priority_b = max(intval($b['university_priority']), intval($b['featured_priority']));
            if ($priority_a !== $priority_b) {
                return $priority_b - $priority_a;
            }

            // User sort
            switch ($sort) {
                case 'fee_low':
                    return intval($a['effective_fee'] ?: 0) - intval($b['effective_fee'] ?: 0);
                case 'fee_high':
                    return intval($b['effective_fee'] ?: 0) - intval($a['effective_fee'] ?: 0);
                case 'popular':
                    return intval($b['views_count'] ?: 0) - intval($a['views_count'] ?: 0);
                case 'newest':
                    return strtotime($b['date']) - strtotime($a['date']);
                default:
                    return 0;
            }
        });

        // Paginate
        $total = count($mapped_programs);
        $max_pages = ceil($total / $per_page);
        $offset = ($page - 1) * $per_page;
        $programs_slice = array_slice($mapped_programs, $offset, $per_page);

        // Extract available filters from results
        $available_filters = $this->extract_available_filters($mapped_programs);

        $response_data = [
            'status' => 200,
            'data' => [
                'programs' => $programs_slice,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per_page,
                    'total' => $total,
                    'max_pages' => $max_pages,
                ],
                'available_filters' => $available_filters,
            ],
        ];

        set_transient($cache_key, $response_data, 15 * MINUTE_IN_SECONDS);

        return rest_ensure_response($response_data);
    }

    /**
     * Get single program by slug
     */
    public function handle_get_program_by_slug($request): \WP_REST_Response
    {
        $slug = sanitize_title($request->get_param('slug') ?: '');
        
        $args = [
            'post_type' => 'sit-program',
            'name' => $slug,
            'posts_per_page' => 1,
        ];
        
        $query = new \WP_Query($args);
        
        if (!$query->have_posts()) {
            return rest_ensure_response([
                'status' => 404,
                'message' => 'Program not found',
            ]);
        }

        $program = $query->posts[0];
        $uni_id = get_post_meta($program->ID, 'zh_university', true);
        $uni = get_post($uni_id);

        $data = [
            'id' => $program->ID,
            'slug' => $program->post_name,
            'title' => $program->post_title,
            'content' => $program->post_content,
            'description' => get_post_meta($program->ID, 'Description', true),
            'link' => get_permalink($program->ID),
            'university' => $uni ? [
                'id' => $uni->ID,
                'title' => $uni->post_title,
                'slug' => $uni->post_name,
                'link' => get_permalink($uni->ID),
                'country' => $this->get_term_name($uni->ID, 'sit-country'),
                'city' => $this->get_term_name($uni->ID, 'sit-city'),
                'image_url' => $this->get_university_image($uni->ID),
            ] : null,
            'degree' => $this->get_term_name($program->ID, 'sit-degree'),
            'language' => $this->get_term_name($program->ID, 'sit-language'),
            'speciality' => $this->get_term_name($program->ID, 'sit-speciality'),
            'faculty' => $this->get_term_name($program->ID, 'sit-faculty'),
            'city' => $this->get_term_name($program->ID, 'sit-city'),
            'fee' => get_post_meta($program->ID, 'official_tuition', true),
            'discounted_fee' => get_post_meta($program->ID, 'discounted_tuition', true),
            'advanced_discount' => get_post_meta($program->ID, 'advanced_discount', true),
            'currency' => get_post_meta($program->ID, 'tuition_currency', true) ?: 'USD',
            'duration' => get_post_meta($program->ID, 'study_years', true),
            'scholarship_inclusion' => get_post_meta($program->ID, 'scholarship_inclusion', true),
            'meta' => get_post_meta($program->ID),
        ];

        return rest_ensure_response([
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get single university by slug with its programs
     */
    public function handle_get_university_by_slug($request): \WP_REST_Response
    {
        $slug = sanitize_title($request->get_param('slug') ?: '');
        
        $args = [
            'post_type' => 'sit-university',
            'name' => $slug,
            'posts_per_page' => 1,
        ];
        
        $query = new \WP_Query($args);
        
        if (!$query->have_posts()) {
            return rest_ensure_response([
                'status' => 404,
                'message' => 'University not found',
            ]);
        }

        $uni = $query->posts[0];
        $is_featured = (new FeaturedUniversity())->isFeatured($uni->ID);

        // Get programs for this university
        $programs_query = new \WP_Query([
            'post_type' => 'sit-program',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'meta_query' => [
                ['key' => 'zh_university', 'value' => $uni->ID],
            ],
        ]);

        $programs = [];
        
        $program_ids = wp_list_pluck($programs_query->posts, 'ID');
        if (!empty($program_ids)) {
            update_meta_cache('post', $program_ids);
            update_object_term_cache($program_ids, ['sit-degree', 'sit-language']);
        }

        foreach ($programs_query->posts as $program) {
            $programs[] = [
                'id' => $program->ID,
                'slug' => $program->post_name,
                'title' => $program->post_title,
                'link' => get_permalink($program->ID),
                'degree' => $this->get_term_name($program->ID, 'sit-degree'),
                'language' => $this->get_term_name($program->ID, 'sit-language'),
                'fee' => get_post_meta($program->ID, 'official_tuition', true),
                'discounted_fee' => get_post_meta($program->ID, 'discounted_tuition', true),
                'duration' => get_post_meta($program->ID, 'study_years', true),
            ];
        }

        $data = [
            'id' => $uni->ID,
            'slug' => $uni->post_name,
            'title' => $uni->post_title,
            'content' => $uni->post_content,
            'description' => get_post_meta($uni->ID, 'Description', true),
            'link' => get_permalink($uni->ID),
            'country' => $this->get_term_name($uni->ID, 'sit-country'),
            'city' => $this->get_term_name($uni->ID, 'sit-city'),
            'type' => get_post_meta($uni->ID, 'Sector', true),
            'ranking' => get_post_meta($uni->ID, 'qs_rank', true),
            'students' => get_post_meta($uni->ID, 'number_of_students', true),
            'is_featured' => $is_featured,
            'image_url' => $this->get_university_image($uni->ID),
            'logo_url' => get_post_meta($uni->ID, 'uni_logo', true),
            'programs' => $programs,
            'programs_count' => count($programs),
            'meta' => get_post_meta($uni->ID),
        ];

        return rest_ensure_response([
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get featured universities
     */
    public function handle_get_featured_universities($request): \WP_REST_Response
    {
        $featured = (new FeaturedUniversity())->getFeatured();
        
        $universities = [];
        
        $featured_ids = wp_list_pluck($featured, 'ID');
        if (!empty($featured_ids)) {
            update_object_term_cache($featured_ids, ['sit-country', 'sit-city']);
        }

        foreach ($featured as $uni) {
            $universities[] = [
                'id' => $uni->ID,
                'slug' => $uni->post_name,
                'title' => $uni->post_title,
                'link' => get_permalink($uni->ID),
                'country' => $this->get_term_name($uni->ID, 'sit-country'),
                'city' => $this->get_term_name($uni->ID, 'sit-city'),
                'image_url' => $this->get_university_image($uni->ID),
                'priority' => get_post_meta($uni->ID, 'sit_featured_priority', true),
            ];
        }

        return rest_ensure_response([
            'status' => 200,
            'data' => $universities,
        ]);
    }

    /**
     * Get all filter options in one call
     */
    public function handle_get_filter_options($request): \WP_REST_Response
    {
        $options = [
            'degrees' => $this->get_taxonomy_terms('sit-degree'),
            'countries' => $this->get_taxonomy_terms('sit-country'),
            'specialities' => $this->get_taxonomy_terms('sit-speciality'),
            'languages' => $this->get_taxonomy_terms('sit-language'),
            'cities' => $this->get_taxonomy_terms('sit-city'),
        ];

        return rest_ensure_response([
            'status' => 200,
            'data' => $options,
        ]);
    }

    // Helper methods
    private function get_term_name($post_id, $taxonomy): string
    {
        $terms = get_the_terms($post_id, $taxonomy);
        return (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
    }

    private function get_university_image($uni_id): string
    {
        $keys = ['uni_image', 'University_Image', 'uni_logo', 'University_Logo', 'Logo', 'uni_banner', 'Banner'];
        foreach ($keys as $key) {
            $img = get_post_meta($uni_id, $key, true);
            if (!empty($img)) return esc_url($img);
        }
        return URLHelper::placeholder(714, 340, 'University');
    }

    private function get_taxonomy_terms($taxonomy): array
    {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        if (is_wp_error($terms)) return [];
        
        return array_map(function($term) {
            return [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count,
            ];
        }, $terms);
    }

    private function extract_available_filters($programs): array
    {
        $languages = [];
        $universities = [];
        $cities = [];
        $durations = [];

        foreach ($programs as $p) {
            if (!empty($p['language'])) $languages[$p['language']] = true;
            if (!empty($p['university_name'])) $universities[$p['university_name']] = true;
            if (!empty($p['city'])) $cities[$p['city']] = true;
            if (!empty($p['duration'])) {
                $dur = intval($p['duration']);
                if ($dur >= 1 && $dur <= 4) {
                    $durations[$dur . ($dur == 1 ? ' year' : ' years')] = true;
                } elseif ($dur > 4) {
                    $durations['4+ years'] = true;
                }
            }
        }

        return [
            'languages' => array_keys($languages),
            'universities' => array_keys($universities),
            'cities' => array_keys($cities),
            'durations' => array_keys($durations),
        ];
    }
}
