<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class FacultyEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/faculty', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_faculty'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_faculty($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $faculties = [];

        $terms = get_terms([
            'taxonomy' => 'sit-faculty',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $faculties[] = [
                    'name' => $term->name,
                    'zoho_faculty_id' => get_term_meta($term->term_id, 'zoho_faculty_id', true),
                    'active_on_university' => 1,
                ];
            }
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'faculties fetched successfully',
            'faculties' => $faculties,
        ]);
    }
}
