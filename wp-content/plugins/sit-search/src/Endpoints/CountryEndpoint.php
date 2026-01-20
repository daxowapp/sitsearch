<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class CountryEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/country', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_country'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_country($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $countries = [];

        $terms = get_terms([
            'taxonomy' => 'sit-country',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $countries[] = [
                    'name' => $term->name,
                    'zoho_country_id' => get_term_meta($term->term_id, 'zoho_country_id', true),
                    'active_on_university' => get_term_meta($term->term_id, 'active_on_university', true),
                ];
            }
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'Countries fetched successfully',
            'countries' => $countries,
        ]);
    }
}
