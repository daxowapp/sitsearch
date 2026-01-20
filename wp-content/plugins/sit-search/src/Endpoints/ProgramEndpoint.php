<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class ProgramEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/programs', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_programs'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_programs($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $zoho_university_id = $request->get_param('zoho_university_id');

        if (!$zoho_university_id) {
            return new \WP_Error('missing_param', 'Missing Zoho University ID', ['status' => 400]);
        }

        $post = Functions::getPostByMeta('zoho_account_id', $zoho_university_id, 'sit-university');
        $programs = [];
        $university_data = [];

        if ($post) {
            $post_id = $post->ID;
            $country = '';
            $city = '';
            $uni_title = get_the_title($post_id);
            $uni_content = get_the_content(null, false, $post);
            $uni_permalink = get_permalink($post_id);
            $termcountry = wp_get_post_terms($post_id, 'sit-country');
            $type=get_post_meta($post_id, 'Sector', true);
            if (!is_wp_error($termcountry) && !empty($termcountry)) {
                $country = $termcountry[0]->name;
            }
            $termcity = wp_get_post_terms($post_id, 'sit-city');
            if (!is_wp_error($termcity) && !empty($termcity)) {
                $city = $termcity[0]->name;
            }
            $university_data = [
                'title' => $uni_title,
                'url' => $uni_permalink,
                'zoho_university_id' => $zoho_university_id,
                'country' => $country,
                'city' => $city,
                'uni_description' => get_post_meta($post_id, 'Description', true),
                'image_url'=>!empty(get_post_meta($post_id, 'uni_image', true))  ?
                esc_url(get_post_meta($post_id, 'uni_image', true))
                :'https://placehold.co/714x340?text=University',
                'uni_logo'=>!empty(get_post_meta($post_id, 'uni_logo', true))  ?
                    esc_url(get_post_meta($post_id, 'uni_logo', true))
                    :'https://placehold.co/100x50?text=University',
                'type'=>$type
            ];

            $query_args = [
                'post_type' => 'sit-program',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => 'zh_university',
                        'value' => $post_id,
                        'compare' => '=',
                    ],
                ],
            ];

            $query = new \WP_Query($query_args);

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $level = '';
                $language = '';
                $speciality = '';

                $termslevel = wp_get_post_terms($post_id, 'sit-degree');
                if (!is_wp_error($termslevel) && !empty($termslevel)) {
                    $level = $termslevel[0]->name;
                }

                $termlanguage = wp_get_post_terms($post_id, 'sit-language');
                if (!is_wp_error($termlanguage) && !empty($termlanguage)) {
                    $language = $termlanguage[0]->name;
                }

                $termspeciality = wp_get_post_terms($post_id, 'sit-speciality');
                if (!is_wp_error($termspeciality) && !empty($termspeciality)) {
                    $speciality = $termspeciality[0]->name;
                }

                $programs[] = [
                    'id' => $post_id,
                    'university_title' => $uni_title,
                    'title' => get_the_title(),
                    'url' => get_permalink($post_id),
                    'content' => get_the_content(),
                    'zoho_university_id' => $zoho_university_id,
                    'duration' => get_post_meta($post_id, 'Study_Years', true),
                    'fee' => get_post_meta($post_id, 'Official_Tuition', true),
                    'discounted_fee' => get_post_meta($post_id, 'Discounted_Tuition', true),
                    'Advanced_Discount' => get_post_meta($post_id, 'Advanced_Discount', true),
                    'level' => $level,
                    'language' => $language,
                    'speciality' => $speciality
                ];
            }

            wp_reset_postdata();
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'Programs fetched successfully',
            'University' => $university_data,
            'Programs' => $programs,
        ]);
    }
}
