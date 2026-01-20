<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class UniversityDataEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/university', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_university'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_university($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $universities = [];
        $allowed_countries = array();
        $tax_query = array('relation' => 'AND');
        $turkey_term = get_term_by('name', 'Turkey', 'sit-country');
        $north_cyprus_term = get_term_by('name', 'North Cyprus', 'sit-country');
        
        if ($turkey_term) {
            $allowed_countries[] = $turkey_term->term_id;
        }
        if ($north_cyprus_term) {
            $allowed_countries[] = $north_cyprus_term->term_id;
        }
        $tax_query[] = array(
            'taxonomy' => 'sit-country',
            'field'    => 'term_id',
            'terms'    => $allowed_countries,
            'operator' => 'IN',
        );
        $query = new \WP_Query([
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => $tax_query,
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $meta = get_post_meta($post_id);
                $meta_data = [];

                foreach ($meta as $key => $value) {
                    $meta_data[$key] = is_array($value) && count($value) === 1 ? $value[0] : $value;
                }

                $country_terms = wp_get_post_terms($post_id, 'sit-country');
                $country = (!is_wp_error($country_terms) && !empty($country_terms)) ? $country_terms[0]->name : '';

                $city_terms = wp_get_post_terms($post_id, 'sit-city');
                $city = (!is_wp_error($city_terms) && !empty($city_terms)) ? $city_terms[0]->name : '';

                $universities[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'slug' => get_post_field('post_name', $post_id),
                    'permalink' => get_permalink($post_id),
                    'status' => get_post_status($post_id),
                    'country' => $country,
                    'city' => $city,
                    'meta' => $meta_data,
                ];
            }

            wp_reset_postdata();
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'Universities fetched successfully',
            'universities' => $universities,
        ]);
    }
}
