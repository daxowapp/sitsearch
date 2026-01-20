<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class CityEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/city', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_city'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_city($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $cities = [];

        $terms = get_terms([
            'taxonomy' => 'sit-city',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $cities[] = [
                    'name' => $term->name,
                    'zoho_city_id' => get_term_meta($term->term_id, 'zoho_city_id', true),
                    'zoho_parent_id' => get_term_meta($term->term_id, 'zoho_parent_id', true),
                ];
            }
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'cities fetched successfully',
            'cities' => $cities,
        ]);
    }
}
