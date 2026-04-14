<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class SingleUniversity
{
    public function __invoke()
    {
        $current_post_id = get_the_ID();
        $current_uni_id = get_post_meta(get_the_ID(), 'zh_university', true);
        $university = get_post($current_uni_id);
        $current_post_title = get_the_title($current_post_id);
        
        // Check if the university is active in search
        $active_in_search = get_field('Active_in_Search', $current_post_id);
        if ($active_in_search != '1' && $active_in_search !== true) {
            return '<div class="university-inactive">This university is not currently active in search.</div>';
        }

        $country_terms = get_the_terms($current_post_id, 'sit-country');
        $city_terms = get_the_terms($current_post_id, 'sit-city');
        
        $programs=[
            'unic_id'=>$current_post_id,
            'title'=>$current_post_title ,
            'pro_country' => (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '',
            'city' => (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '',
            'description' => get_post_meta($current_post_id, 'Description', true),
            'uni_description' => get_post_meta($current_post_id, 'Description', true),
            'ranking' => get_post_meta($current_post_id, 'QS_Rank', true),
            'Tuition_Currency' => get_post_meta($current_post_id, 'Tuition_Currency', true),
            'total_students' => get_post_meta($current_post_id, 'Number_Of_Students', true),
            'University_brochure' => get_post_meta($current_post_id, 'University_brochure', true),
            'image_url'=>!empty(get_post_meta($current_post_id, 'uni_image', true))  ?
                esc_url(get_post_meta($current_post_id, 'uni_image', true))
                :'https://placehold.co/714x340?text=University',
            'Year_Founded'=>get_post_meta($current_post_id, 'Year_Founded', true),
            'uni_logo'=>!empty(get_post_meta($current_post_id, 'uni_logo', true))  ?
                esc_url(get_post_meta($current_post_id, 'uni_logo', true))
                : '',
        ];

        $other_args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1, // Get all posts
            'no_found_rows'  => true,
            'post_status'    => 'publish',
            'meta_key'       => 'zh_university',
            'meta_value'     => $current_post_id,
        );
        $uni_query = new \WP_Query($other_args);
        $universities = $uni_query->get_posts();

        // Pre-warm caches to prevent N+1 queries during mapping
        $uni_post_ids = wp_list_pluck($universities, 'ID');
        if (!empty($uni_post_ids)) {
            update_meta_cache('post', $uni_post_ids);
            update_object_term_cache($uni_post_ids, ['sit-degree', 'sit-language', 'sit-country']);

            // since all programs belong to $current_post_id (which is a university),
            // $oth_uniid in the loop will just be $current_post_id. We can prime its terms just in case.
            update_object_term_cache([$current_post_id], 'sit-city');
        }

        $universities = array_map(function ($university) use ($current_post_id) {
            $oth_uniid = get_post_meta($university->ID, 'zh_university', true) ?: $current_post_id;
            $degree_terms = get_the_terms($university->ID, 'sit-degree');
            $language_terms = get_the_terms($university->ID, 'sit-language');
            $country_terms = get_the_terms($university->ID, 'sit-country');
            $city_terms = get_the_terms($oth_uniid, 'sit-city');
            
            return [
                'uni_id' => $university->ID,
                'title' => $university->post_title,
                'guid' => $university->guid,
                'fee' => get_post_meta($university->ID, 'Official_Tuition', true),
                'discounted_fee' => get_post_meta($university->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($university->ID, 'Advanced_Discount', true),
                'Tuition_Currency' => get_post_meta($university->ID, 'Tuition_Currency', true),
                'level' => (!empty($degree_terms) && !is_wp_error($degree_terms)) ? $degree_terms[0]->name : '',
                'language' => (!empty($language_terms) && !is_wp_error($language_terms)) ? $language_terms[0]->name : '',
                'country' => (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '',
                'city' => (!empty($city_terms) && !is_wp_error($city_terms)) ? $city_terms[0]->name : '', // Fixed variable name
                'description' => get_post_meta($university->ID, 'Description', true),
                'duration' => get_post_meta($university->ID, 'Study_Years', true), // ADDED MISSING DURATION
                'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                'accommodation' => is_array(get_post_meta($university->ID, 'Accommodation', true)) ?
                    implode(', ', get_post_meta($university->ID, 'Accommodation', true)) :
                    get_post_meta($university->ID, 'Accommodation', true),
                'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                'year' => get_post_meta($university->ID, 'Year_Founded', true) ?
                    date('Y', strtotime(get_post_meta($university->ID, 'Year_Founded', true))) :
                    null,
                'image_url'=>!empty(get_post_meta($oth_uniid, 'uni_image', true))  ?
                    esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                    :'https://placehold.co/714x340?text=University',
                'uni_logo'=>!empty(get_post_meta($oth_uniid, 'uni_logo', true))  ?
                    esc_url(get_post_meta($oth_uniid, 'uni_logo', true))
                    :'https://placehold.co/100x50?text=University',
            ];
        }, $universities);

        $campus_args = array(
            'post_type'      => 'sit-campus',
            'posts_per_page' => -1, // Get all posts
            'no_found_rows'  => true,
            'post_status'    => 'publish',
            'meta_key'       => 'zh_university',
            'meta_value'     => $current_post_id,
        );
        $campus_query = new \WP_Query($campus_args);
        $campuses = $campus_query->get_posts();

        // Pre-warm caches for campuses
        $campus_post_ids = wp_list_pluck($campuses, 'ID');
        if (!empty($campus_post_ids)) {
            update_meta_cache('post', $campus_post_ids);
        }

        $campuses = array_map(function ($campus) use ($current_post_id) {
            $uniid = get_post_meta($campus->ID, 'zh_university', true) ?: $current_post_id;
            $country_terms = get_the_terms($uniid, 'sit-country');
            return [
                'cam_id' => $campus->ID,
                'title' => $campus->post_title,
                'guid' => $campus->guid,
                'map' => get_post_meta($campus->ID, 'Map_Cordination', true),
                'country' => (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '',
                'description' => get_post_meta($uniid, 'Description', true),
                'ranking' => get_post_meta($uniid, 'QS_Rank', true),
                'accommodation' => is_array(get_post_meta($uniid, 'Accommodation', true)) ?
                    implode(', ', get_post_meta($uniid, 'Accommodation', true)) :
                    get_post_meta($uniid, 'Accommodation', true),
                'students' => get_post_meta($uniid, 'Number_Of_Students', true),
                'year' => get_post_meta($uniid, 'Year_Founded', true) ?
                    date('Y', strtotime(get_post_meta($uniid, 'Year_Founded', true))) :
                    null,
                'image_url'=>!empty(get_post_meta($uniid, 'uni_image', true))  ?
                    esc_url(get_post_meta($uniid, 'uni_image', true))
                    :'https://placehold.co/714x340?text=University',
            ];
        }, $campuses);

        ob_start();
        Template::render('shortcodes/single-university',['campuses'=>$campuses,'program'=>$programs,'other_uni' => $universities,'university'=>$university]);
        return ob_get_clean();
    }
}