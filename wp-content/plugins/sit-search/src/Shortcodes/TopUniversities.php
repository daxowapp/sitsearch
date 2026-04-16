<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class TopUniversities
{
    public function __invoke()
    {
        // Check cache first
        $cache_key = 'sit_top_universities_data';
        $universities = get_transient($cache_key);
        
        if (false === $universities) {
            $args = array(
                'post_type' => 'sit-university',
                'post_status' => 'publish',
                'posts_per_page' => 20, // Limit results
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'Featured_Univesity',
                        'value' => '1',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'Active_in_Search',
                        'value' => '1',
                        'compare' => '='
                    )
                ),
                'orderby' => 'meta_value_num',
                'meta_key' => 'QS_Rank',
                'order' => 'ASC'
            );

            $query = new \WP_Query($args);
            $posts = $query->get_posts();
            
            // Get all post IDs for batch queries
            $post_ids = wp_list_pluck($posts, 'ID');
            
            // Batch load all meta data and terms at once
            if (!empty($post_ids)) {
                update_meta_cache('post', $post_ids);
                update_object_term_cache($post_ids, 'sit-country');
            }
            
            $universities = array_map(function ($university) {
                // Get country terms
                $country_terms = get_the_terms($university->ID, 'sit-country');
                $country_name = (!empty($country_terms) && !is_wp_error($country_terms)) ? 
                    $country_terms[0]->name : '';
                
                // Get accommodation once
                $accommodation = get_post_meta($university->ID, 'Accommodation', true);
                $accommodation_str = is_array($accommodation) ? 
                    implode(', ', $accommodation) : $accommodation;
                
                // Get year founded once
                $year_founded = get_post_meta($university->ID, 'Year_Founded', true);
                $year = $year_founded ? date('Y', strtotime($year_founded)) : null;
                
                // Get image once
                $uni_image = get_post_meta($university->ID, 'uni_image', true);
                $image_url = !empty($uni_image) ? 
                    esc_url($uni_image) : 'https://placehold.co/714x340?text=University';
                
                return [
                    'title' => $university->post_title,
                    'country' => $country_name,
                    'description' => get_post_meta($university->ID, 'Description', true),
                    'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                    'accommodation' => $accommodation_str,
                    'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                    'year' => $year,
                    'image_url' => $image_url,
                    'guid' => $university->guid,
                ];
            }, $posts);
            
            // Cache for 12 hours
            set_transient($cache_key, $universities, 12 * HOUR_IN_SECONDS);
        }

        ob_start();
        Template::render('shortcodes/top-universities', ['universities' => $universities]);
        return ob_get_clean();
    }
}