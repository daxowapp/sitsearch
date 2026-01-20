<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class ProgramDataEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/program', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_program'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_program($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $programs = [];
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
            'post_type' => 'sit-program',
            'post_status' => 'publish',
            'posts_per_page' => 3000,
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

                $program_zoho_id = get_post_meta($post_id, 'zoho_product_id', true);

                $university_post_id = get_post_meta($post_id, 'zh_university', true);
                $university_zoho_id = get_post_meta($university_post_id, 'zoho_account_id', true);

                $country_terms = wp_get_post_terms($post_id, 'sit-country');
                $country = (!is_wp_error($country_terms) && !empty($country_terms)) ? $country_terms[0]->name : '';

                $city_terms = wp_get_post_terms($post_id, 'sit-city');
                $city = (!is_wp_error($city_terms) && !empty($city_terms)) ? $city_terms[0]->name : '';

                $speciality_terms = wp_get_post_terms($post_id, 'sit-speciality');
                $speciality = (!is_wp_error($speciality_terms) && !empty($speciality_terms)) ? $speciality_terms[0]->name : '';

                $language_terms = wp_get_post_terms($post_id, 'sit-language');
                $language = (!is_wp_error($language_terms) && !empty($language_terms)) ? $language_terms[0]->name : '';

                $degree_terms = wp_get_post_terms($post_id, 'sit-degree');
                $degree = (!is_wp_error($degree_terms) && !empty($degree_terms)) ? $degree_terms[0]->name : '';

                $faculty_terms = wp_get_post_terms($post_id, 'sit-faculty');
                $faculty = (!is_wp_error($faculty_terms) && !empty($faculty_terms)) ? $faculty_terms[0]->name : '';

                $programs[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'program_zoho_id' => $program_zoho_id,
                    'university_zoho_id' => $university_zoho_id,
                    'speciality' => $speciality,
                    'language' => $language,
                    'degree' => $degree,
                    'faculty' => $faculty,
                    'country' => $country,
                    'city' => $city,
                    'meta' => $meta_data,
                ];
            }

            wp_reset_postdata();
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'programs fetched successfully',
            'programs' => $programs,
        ]);
    }
}
