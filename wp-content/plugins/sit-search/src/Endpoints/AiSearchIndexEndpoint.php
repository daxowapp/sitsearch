<?php

namespace SIT\Search\Endpoints;

class AiSearchIndexEndpoint
{
    public function register_routes()
    {
        register_rest_route('sit-search/v1', '/ai-search-index', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_index'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function get_index(\WP_REST_Request $request)
    {
        $cache_key = 'sit_ai_search_index_cache';
        $index = get_transient($cache_key);
        if ($index !== false) {
            return new \WP_REST_Response($index, 200);
        }

        $posts = get_posts([
            'post_type' => 'sit-program',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'no_found_rows' => true,
        ]);
        
        $post_ids = wp_list_pluck($posts, 'ID');
        if (!empty($post_ids)) {
            update_meta_cache('post', $post_ids);
            update_object_term_cache($post_ids, 'sit-speciality');
            
            $uni_ids = [];
            foreach ($post_ids as $pid) {
                $uid = intval(get_post_meta($pid, 'zh_university', true));
                if ($uid && !in_array($uid, $uni_ids)) {
                    $uni_ids[] = $uid;
                }
            }
            if (!empty($uni_ids)) {
                _prime_post_caches($uni_ids, false, false);
            }
        }

        $index = [];
        foreach ($posts as $post) {
            $uni_id = get_post_meta($post->ID, 'zh_university', true);
            $uni_title = $uni_id ? get_the_title($uni_id) : '';
            
            $specialities = array_map(function($t) { return $t->name; }, get_the_terms($post->ID, 'sit-speciality') ?: []);
            
            $index[] = [
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'uni' => $uni_title,
                'category' => implode(', ', $specialities)
            ];
        }

        set_transient($cache_key, $index, 6 * HOUR_IN_SECONDS);

        return new \WP_REST_Response($index, 200);
    }
}
