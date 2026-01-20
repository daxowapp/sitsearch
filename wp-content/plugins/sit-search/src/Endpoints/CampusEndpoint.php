<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\Functions;

class CampusEndpoint {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('sit-search/v1', '/campus', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle_get_campus'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle_get_campus($request): \WP_Error|\WP_REST_Response|\WP_HTTP_Response
    {
        $campuses = [];

        $query = new \WP_Query([
            'post_type' => 'sit-campus',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $uni_post_id = get_post_meta($post_id, 'zh_university', true);
                $zoho_account_id = get_post_meta($uni_post_id, 'zoho_account_id', true);
                $campus_zoho_id = get_post_meta($post_id, 'zoho_campus_id', true);
                $faculties_id= get_post_meta($post_id, 'sit-faculty', true);
                $faculties_name=[];
                if($faculties_id != 'good'){
                    $faculties_id = explode(',', $faculties_id);
                    foreach ($faculties_id as $faculty_id) {
                        $faculty = get_term($faculty_id, 'sit-faculty');
                        if (!is_wp_error($faculty) && $faculty) {
                            $faculties_name[] = $faculty->name;
                        }
                    }
                }

                $campuses[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'campus_zoho_id' => $campus_zoho_id,
                    'university_id' => $zoho_account_id,
                    'faculties' => $faculties_name,
                ];
            }

            wp_reset_postdata();
        }

        return rest_ensure_response([
            'STATUS' => 200,
            'MESSAGE' => 'campuses fetched successfully',
            'campuses' => $campuses,
        ]);
    }
}
