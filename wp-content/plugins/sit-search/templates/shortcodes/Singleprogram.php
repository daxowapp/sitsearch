<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class Singleprogram
{
    public function __invoke()
    {
        $current_post_id = get_the_ID();
        $current_uni_id=get_post_meta(get_the_ID(), 'zh_university', true);
        $university = get_post($current_uni_id);
        $current_post_title = get_the_title($current_post_id);

        // Get active universities first
        $active_university_ids = array();
        $all_universities = get_posts(array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        foreach ($all_universities as $uni_id) {
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search == '1' || $active_in_search === true) {
                $active_university_ids[] = $uni_id;
            }
        }

        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            's'              => $current_post_title,
            'post__not_in'   => array($current_post_id),
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'zh_university',
                    'value'   => $active_university_ids,
                    'compare' => 'IN',
                )
            )
        );

        $query = new \WP_Query($args);

        $meta_values = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $meta_value = get_post_meta(get_the_ID(), 'zh_university', true);
                if ($meta_value) {
                    $meta_values[] = $meta_value;
                }
            }
        }
        // Get terms safely
        $pro_country_terms = get_the_terms($current_post_id, 'sit-country');
        $uni_country_terms = get_the_terms($current_uni_id, 'sit-country');
        $uni_city_terms = get_the_terms($current_uni_id, 'sit-city');
        
        $programs=[
            'pro_id'=>$current_post_id,
            'title'=>$current_post_title ,
            'fee' => get_post_meta($current_post_id, 'Official_Tuition', true),
            'discounted_fee' => get_post_meta($current_post_id, 'Discounted_Tuition', true),
            'Advanced_Discount' => get_post_meta($current_post_id, 'Advanced_Discount', true),
            'duration' => get_post_meta($current_post_id, 'Study_Years', true),
            'pro_country' => (!empty($pro_country_terms) && !is_wp_error($pro_country_terms)) ? $pro_country_terms[0]->name : '',
            'country' => (!empty($uni_country_terms) && !is_wp_error($uni_country_terms)) ? $uni_country_terms[0]->name : '',
            'city' => (!empty($uni_city_terms) && !is_wp_error($uni_city_terms)) ? $uni_city_terms[0]->name : '',
            'description' => get_post_meta($current_post_id, 'Description', true),
            'keywords' => get_post_meta($current_post_id, 'Keywords', true),
            'curriculum' => get_post_meta($current_post_id, 'Curriculums', true),
            'uni_description' => get_post_meta($current_uni_id, 'Description', true),
            'ranking' => get_post_meta($current_uni_id, 'QS_Rank', true),
            'Tuition_Currency' => get_post_meta($current_post_id, 'Tuition_Currency', true),
            'uni_title' => $university->post_title,
            'uni_link' => $university->guid,
            'University_brochure' => get_post_meta($university->ID, 'University_brochure', true),
            'image_url'=>!empty(get_post_meta($current_uni_id, 'uni_image', true))  ?
                esc_url(get_post_meta($current_uni_id, 'uni_image', true))
                :'https://placehold.co/714x340?text=University',
            'Year_Founded'=>get_post_meta($current_uni_id, 'Year_Founded', true),
            'program_students' => get_post_meta($current_uni_id, 'Number_Of_Students', true),
            'total_students' => get_post_meta($current_uni_id, 'Number_Of_Students', true),
            'ielts' => get_post_meta($current_post_id, 'IELTS', true),
            'pte' => get_post_meta($current_post_id, 'PTE', true),
            'toefl' => get_post_meta($current_post_id, 'TOEFL', true),
        ];
        if ($meta_values){
            $other_args = array(
                'post_type'      => 'sit-university',
                'post_status'    => 'publish',
                'post__in'       => $meta_values,
            );
            $uni_query = new \WP_Query($other_args);
            $universities = $uni_query->get_posts();
            $universities = array_map(function ($university) {
                $country_terms = get_the_terms($university->ID, 'sit-country');
                return [
                    'uni_id' => $university->ID,
                    'title' => $university->post_title,
                    'guid' => $university->guid,
                    'country' => (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '',
                    'description' => get_post_meta($university->ID, 'Description', true),
                    'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
                    'accommodation' => is_array(get_post_meta($university->ID, 'Accommodation', true)) ?
                        implode(', ', get_post_meta($university->ID, 'Accommodation', true)) :
                        get_post_meta($university->ID, 'Accommodation', true),
                    'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
                    'year' => get_post_meta($university->ID, 'Year_Founded', true) ?
                        date('Y', strtotime(get_post_meta($university->ID, 'Year_Founded', true))) :
                        null,
                    'image_url'=>!empty(get_post_meta($university->ID, 'uni_image', true))  ?
                        esc_url(get_post_meta($university->ID, 'uni_image', true))
                        :'https://placehold.co/714x340?text=University',
                ];
            }, $universities);
        }
        ob_start();
        Template::render('shortcodes/single-program',['program'=>$programs,'other_uni' => $universities,'university'=>$university]);
        return ob_get_clean();
    }
}