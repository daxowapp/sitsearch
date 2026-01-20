<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class LanguageEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/language', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_language'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_language($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $languages = [];

        $terms = get_terms([
            'taxonomy' => 'sit-language',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $languages[] = [
                    'name' => $term->name,
                    'zoho_language_id' => get_term_meta($term->term_id, 'zoho_language_id', true),
                    'active_on_university' => 1,
                ];
            }
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'languages fetched successfully',
            'languages' => $languages,
        ]);
    }
}
