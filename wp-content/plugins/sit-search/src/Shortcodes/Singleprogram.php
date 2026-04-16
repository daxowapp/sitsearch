<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class Singleprogram
{
    public function __invoke()
    {
        $current_post_id = get_the_ID();
        $current_uni_id = get_post_meta(get_the_ID(), 'zh_university', true);
        $university = !empty($current_uni_id) ? get_post($current_uni_id) : null;
        $current_post_title = get_the_title($current_post_id);

        // Get active universities first
        $all_universities = get_posts(array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'fields' => 'ids'
        ));
        
        // Use CachedData for active university lookups instead of looping
        $globally_active_ids = \SIT\Search\Services\CachedData::get_active_university_ids();
        $active_university_ids = array_intersect($all_universities, $globally_active_ids);

        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => 5,
            'no_found_rows'  => true,
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

        // Get terms safely — program's own terms
        $pro_country_terms = get_the_terms($current_post_id, 'sit-country');
        $pro_city_terms = get_the_terms($current_post_id, 'sit-city');

        // University terms (only if university exists)
        $uni_country_terms = $university ? get_the_terms($university->ID, 'sit-country') : false;
        $uni_city_terms = $university ? get_the_terms($university->ID, 'sit-city') : false;

        // Resolve country: prefer university terms, fallback to program terms
        $resolved_country = '';
        if (!empty($uni_country_terms) && !is_wp_error($uni_country_terms)) {
            $resolved_country = $uni_country_terms[0]->name;
        } elseif (!empty($pro_country_terms) && !is_wp_error($pro_country_terms)) {
            $resolved_country = $pro_country_terms[0]->name;
        }

        // Resolve city: prefer university terms, fallback to program terms
        $resolved_city = '';
        if (!empty($uni_city_terms) && !is_wp_error($uni_city_terms)) {
            $resolved_city = $uni_city_terms[0]->name;
        } elseif (!empty($pro_city_terms) && !is_wp_error($pro_city_terms)) {
            $resolved_city = $pro_city_terms[0]->name;
        }

        // Resolve university title: prefer post title, fallback to program's 'University' ACF field
        $resolved_uni_title = '';
        if ($university) {
            $resolved_uni_title = $university->post_title;
        } else {
            // Fallback: program may have a 'University' text field from Zoho sync
            $resolved_uni_title = get_post_meta($current_post_id, 'University', true) ?: '';
        }

        // Resolve university link: prefer permalink, fallback to empty
        $resolved_uni_link = $university ? get_permalink($university->ID) : '';
        $resolved_uni_id = $university ? $university->ID : 0;

        $programs = [
            'pro_id' => $current_post_id,
            'title' => $current_post_title,
            'fee' => get_post_meta($current_post_id, 'Official_Tuition', true),
            'discounted_fee' => get_post_meta($current_post_id, 'Discounted_Tuition', true),
            'Advanced_Discount' => get_post_meta($current_post_id, 'Advanced_Discount', true),
            'duration' => get_post_meta($current_post_id, 'Study_Years', true),
            'pro_country' => (!empty($pro_country_terms) && !is_wp_error($pro_country_terms)) ? $pro_country_terms[0]->name : '',
            'country' => $resolved_country,
            'city' => $resolved_city,
            'description' => get_post_meta($current_post_id, 'Description', true),
            'keywords' => get_post_meta($current_post_id, 'Keywords', true),
            'curriculum' => get_post_meta($current_post_id, 'Curriculums', true),
            'uni_description' => $resolved_uni_id ? get_post_meta($resolved_uni_id, 'Description', true) : '',
            'ranking' => $resolved_uni_id ? get_post_meta($resolved_uni_id, 'QS_Rank', true) : '',
            'Tuition_Currency' => get_post_meta($current_post_id, 'Tuition_Currency', true),
            'uni_title' => $resolved_uni_title,
            'uni_link' => $resolved_uni_link,
            'University_brochure' => $resolved_uni_id ? get_post_meta($resolved_uni_id, 'University_brochure', true) : '',
            'image_url' => !empty(get_post_meta($resolved_uni_id ?: $current_post_id, 'uni_image', true))
                ? esc_url(get_post_meta($resolved_uni_id ?: $current_post_id, 'uni_image', true))
                : 'https://placehold.co/714x340?text=University',
            'Year_Founded' => $resolved_uni_id ? get_post_meta($resolved_uni_id, 'Year_Founded', true) : '',
            'program_students' => $resolved_uni_id ? get_post_meta($resolved_uni_id, 'Number_Of_Students', true) : '',
            'total_students' => $resolved_uni_id ? get_post_meta($resolved_uni_id, 'Number_Of_Students', true) : '',
            'ielts' => get_post_meta($current_post_id, 'IELTS', true),
            'pte' => get_post_meta($current_post_id, 'PTE', true),
            'toefl' => get_post_meta($current_post_id, 'TOEFL', true),
        ];
        
        // Initialize universities array
        $universities = [];
        
        if ($meta_values){
            $other_args = array(
                'post_type'      => 'sit-university',
                'post_status'    => 'publish',
                'no_found_rows'  => true,
                'post__in'       => $meta_values,
            );
            $uni_query = new \WP_Query($other_args);
            $universities = $uni_query->get_posts();
            
            // Pre-warm caches
            $uni_post_ids = wp_list_pluck($universities, 'ID');
            if (!empty($uni_post_ids)) {
                update_meta_cache('post', $uni_post_ids);
                update_object_term_cache($uni_post_ids, 'sit-country');
            }

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

        // GEO: Get FAQ data for template rendering
        $faq_data = \SIT\Search\Services\GeoSchema::get_faq_data($current_post_id, 10);
        $faq_count = \SIT\Search\Services\GeoSchema::get_faq_count($current_post_id);
        $faq_categories = \SIT\Search\Services\GeoSchema::get_faq_categories($current_post_id);

        ob_start();
        Template::render('shortcodes/single-program',[
            'program' => $programs,
            'other_uni' => $universities,
            'university' => $university,
            'faq_data' => $faq_data,
            'faq_count' => $faq_count,
            'faq_categories' => $faq_categories,
        ]);
        return ob_get_clean();
    }
}