<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class SpecialityEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/speciality', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_speciality'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_speciality($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $specialities = [];

        $terms = get_terms([
            'taxonomy' => 'sit-speciality',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $specialities[] = [
                    'name' => $term->name,
                    'zoho_speciality_id' => get_term_meta($term->term_id, 'zoho_speciality_id', true),
                    'active_in_search' => get_term_meta($term->term_id, 'active_in_search', true),
                ];
            }
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'specialities fetched successfully',
            'specialities' => $specialities,
        ]);
    }
}
