<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class ProgramArchive
{
    public function __invoke()
    {
        $queried_object = get_queried_object();
        $term = get_queried_object();
        $country_zoho_id = get_term_meta($term->term_id, 'zoho_country_id', true);
        $cities_arg = array(
            'taxonomy'   => 'sit-city',
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key'     => 'zoho_parent_id',
                    'value'   => $country_zoho_id,
                    'compare' => '='
                )
            )
        );

        $cities = get_terms($cities_arg);

        $archive_title=$term->name;
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = $_GET['sort'] ?? '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = (!empty($_GET['country']) && $_GET['country'] != 0) ? intval($_GET['country']) : '';
        $speciality = (!empty($_GET['speciality']) && $_GET['speciality'] != 0) ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

        $feeFilter = $_GET['feeFilter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';
        $modeOfStudy = $_GET['modeOfStudy'] ?? '';
        $degreeType = $_GET['degreeType'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');

        if (is_tax()) {
            $term = get_queried_object();
            if (!empty($term)) {
                $tax_query[] = array(
                    'taxonomy' => $term->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                );
            }
        }

        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_name) {
                // Find language term by name
                $lang_term = get_term_by('name', trim($lang_name), 'sit-language');
                if ($lang_term) {
                    $language_terms[] = $lang_term->term_id;
                }
            }
            if (!empty($language_terms)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-language',
                    'field'    => 'term_id',
                    'terms'    => $language_terms,
                    'operator' => 'IN',
                );
            }
        }

        if (!empty($country)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-country',
                'field'    => 'term_id',
                'terms'    => $country,
            );
        }

        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
        }

        if (!empty($modeOfStudy)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-mode-of-study',
                'field'    => 'term_id',
                'terms'    => $modeOfStudy,
            );
        }

        if (!empty($degreeType)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-degree-type',
                'field'    => 'term_id',
                'terms'    => $degreeType,
            );
        }

        $meta_query = array('relation' => 'AND');

        $meta_query[] = array(
            'key'     => 'zh_university',
            'compare' => 'EXISTS',
        );

        // Filter programs by university's Active_in_Search status
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
        
        if (!empty($active_university_ids)) {
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            );
        } else {
            // No active universities found, return no results
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => array(-1),
                'compare' => 'IN',
            );
        }

        if (!empty($feeFilter)) {
            $feeRange = explode('-', $feeFilter);
            if (count($feeRange) == 2) {
                $meta_query[] = array(
                    'key'     => 'Official_Tuition',
                    'value'   => array(intval($feeRange[0]), intval($feeRange[1])),
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        }

        if (!empty($duration) && is_numeric($duration)) {
            $meta_query[] = array(
                'key'     => 'Study_Years',
                'value'   => intval($duration),
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }

        if (!empty($isScholarShip)) {
            $meta_query[] = array(
                'key'     => 'isScholarShip',
                'value'   => $isScholarShip,
                'compare' => '=',
            );
        }

        // Add university filter (supports multiple selections)
        $university = $_GET['university'] ?? '';
        if (!empty($university)) {
            $universities = is_array($university) ? $university : [$university];
            $university_ids = array();
            
            foreach ($universities as $uni_name) {
                $university_posts = get_posts(array(
                    'post_type' => 'sit-university',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'title' => $uni_name,
                    'fields' => 'ids'
                ));
                
                if (!empty($university_posts)) {
                    $university_ids = array_merge($university_ids, $university_posts);
                }
            }
            
            if (!empty($university_ids)) {
                // Filter university IDs to only include those with Active_in_Search = 1
                $active_university_ids_filtered = array();
                foreach ($university_ids as $uni_id) {
                    $active_in_search = get_field('Active_in_Search', $uni_id);
                    if ($active_in_search == '1' || $active_in_search === true) {
                        $active_university_ids_filtered[] = $uni_id;
                    }
                }
                
                if (!empty($active_university_ids_filtered)) {
                    $meta_query[] = array(
                        'key'     => 'zh_university',
                        'value'   => $active_university_ids_filtered,
                        'compare' => 'IN',
                    );
                } else {
                    // No active universities found, return no results
                    $meta_query[] = array(
                        'key'     => 'zh_university',
                        'value'   => array(-1), // Non-existent ID to return no results
                        'compare' => 'IN',
                    );
                }
            }
        }

        $university_ids = array();

        if (!empty($search)) {
            $university_query = new \WP_Query(array(
                'post_type'      => 'sit-university',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query'     => array(
                    array(
                        'key'     => 'Account_Name',
                        'value'   => $search,
                        'compare' => 'LIKE',
                    ),
                ),
                'fields'         => 'ids',
            ));

            if ($university_query->have_posts()) {
                $university_ids = $university_query->posts;
            }
        }

        if (!empty($search)) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'zh_university',
                    'value'   => $university_ids,
                    'compare' => 'IN',
                ),
                array(
                    'key'     => 'Product_Name',
                    'value'   => $search,
                    'compare' => 'LIKE',
                ),
            );
        }

        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => 21,
            'post_status'    => 'publish',
            'paged'          => $paged,
            'meta_query'     => $meta_query,
        );

        $pdf_arg=array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        );

        if (!empty($degree) || !empty($country) || !empty($speciality) || !empty($modeOfStudy) || !empty($degreeType) || is_tax()) {
            $args['tax_query'] = $tax_query;
            $pdf_arg['tax_query'] = $tax_query;
        }

        switch ($sort) {
            case 'fee_low':
                $args['meta_key'] = 'Official_Tuition';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                $pdf_arg['meta_key'] = 'Official_Tuition';
                $pdf_arg['orderby']  = 'meta_value_num';
                $pdf_arg['order']    = 'ASC';
                break;

            case 'fee_high':
                $args['meta_key'] = 'Official_Tuition';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $pdf_arg['meta_key'] = 'Official_Tuition';
                $pdf_arg['orderby']  = 'meta_value_num';
                $pdf_arg['order']    = 'DESC';
                break;

            case 'popular':
                $args['meta_key'] = 'views_count';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $pdf_arg['meta_key'] = 'views_count';
                $pdf_arg['orderby']  = 'meta_value_num';
                $pdf_arg['order']    = 'DESC';
                break;

            case 'newest':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                $pdf_arg['orderby'] = 'date';
                $pdf_arg['order']   = 'DESC';
                break;
        }

        $query = new \WP_Query($args);

        $pdf_query = new \WP_Query($pdf_arg);
        
        // Create a separate query to get ALL programs for filter options (not paginated)
        $filter_args = $args;
        $filter_args['posts_per_page'] = -1; // Get all posts
        unset($filter_args['paged']); // Remove pagination
        $filter_query = new \WP_Query($filter_args);

        $programs = array_map(function ($post) {

            $oth_uniid = get_post_meta($post->ID, 'zh_university', true);
            $unii_title = get_the_title($oth_uniid);
            $country_terms = get_the_terms($post->ID, 'sit-country');
            $country_name = !empty($country_terms) && !is_wp_error($country_terms)
                ? $country_terms[0]->name
                : '';
            
            $language_terms = get_the_terms($post->ID, 'sit-language');
            $language_name = '';
            if ($language_terms && !is_wp_error($language_terms)) {
                $language_name = $language_terms[0]->name;
            }

            return [
                'uni_id'        => $post->ID,
                'title' => $post->post_title,
                'link' => $post->guid,
                'uni_title' => $unii_title,
                'country' => $country_name,
                'language' => $language_name,
                'description' => !empty(get_post_meta($post->ID, 'Description', true)) ?
                    get_post_meta($post->ID, 'Description', true)
                    : 'Empty',
                'ranking' => get_post_meta($oth_uniid, 'QS_Rank', true),
                'duration' => get_post_meta($post->ID, 'Study_Years', true),
                'students' => get_post_meta($oth_uniid, 'Number_Of_Students', true),
                'fee' => get_post_meta($post->ID, 'Official_Tuition', true),
                'discounted_fee' => get_post_meta($post->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($post->ID, 'Advanced_Discount', true),
                'image_url' => !empty(get_post_meta($oth_uniid, 'uni_image', true)) ?
                    esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
            ];
        }, $query->posts);

        // Extract unique languages from ALL results (not just current page)
        $all_program_posts = $filter_query->posts;
        $unique_languages = [];
        $all_universities_for_filter = [];
        $all_durations_for_filter = [];
        
        foreach ($all_program_posts as $program_post) {
            // Extract languages
            $language_terms = get_the_terms($program_post->ID, 'sit-language');
            if ($language_terms && !is_wp_error($language_terms)) {
                foreach ($language_terms as $term) {
                    if (!isset($unique_languages[$term->term_id])) {
                        $unique_languages[$term->term_id] = $term;
                    }
                }
            }
            
            // Extract universities
            $uni_id = get_post_meta($program_post->ID, 'zh_university', true);
            if ($uni_id) {
                $uni_title = get_the_title($uni_id);
                if ($uni_title && !in_array($uni_title, $all_universities_for_filter)) {
                    $all_universities_for_filter[] = $uni_title;
                }
            }
            
            // Extract durations
            $duration = get_post_meta($program_post->ID, 'Study_Years', true);
            if ($duration && !in_array($duration, $all_durations_for_filter)) {
                $all_durations_for_filter[] = $duration;
            }
        }
        
        // Sort the arrays
        sort($all_universities_for_filter);
        sort($all_durations_for_filter);

        // Get all degrees for the degree filter
        $all_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);

        $pdf_program = array_map(function ($post) {

            $oth_uniid = get_post_meta($post->ID, 'zh_university', true);
            $unii_title = get_the_title($oth_uniid);
            $country_terms = get_the_terms($post->ID, 'sit-country');
            $country_name = !empty($country_terms) && !is_wp_error($country_terms)
                ? $country_terms[0]->name
                : '';

            return [
                'uni_id'        => $post->ID,
                'title' => $post->post_title,
                'link' => $post->guid,
                'uni_title' => $unii_title,
                'country' => $country_name,
                'description' => !empty(get_post_meta($post->ID, 'Description', true)) ?
                    get_post_meta($post->ID, 'Description', true)
                    : 'Empty',
                'ranking' => get_post_meta($oth_uniid, 'QS_Rank', true),
                'duration' => get_post_meta($post->ID, 'Study_Years', true),
                'students' => get_post_meta($oth_uniid, 'Number_Of_Students', true),
                'fee' => get_post_meta($post->ID, 'Official_Tuition', true),
                'discounted_fee' => get_post_meta($post->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($post->ID, 'Advanced_Discount', true),
                'image_url' => !empty(get_post_meta($oth_uniid, 'uni_image', true)) ?
                    esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
            ];
        }, $pdf_query->posts);


        $disstr='';
        if($term->name != ''){
            $disstr="This document provides a comprehensive list of degrees from ".$term->name." leading universities. Each program includes details about duration, tuition fees, language requirements, application deadlines, and more.";
        }

        Template::render('shortcodes/program-archive', [
            'pdf_program' => $pdf_program,
            'programs' => $programs,
            'archiveitle' => $archive_title,
            'query' => $query,
            'cities' => $cities,
            'disstr' => $disstr,
            'available_languages' => $unique_languages, // Pass unique languages from ALL results
            'all_degrees' => $all_degrees, // Pass all degrees to the template
            'all_universities_for_filter' => $all_universities_for_filter, // Pass all universities from ALL results
            'all_durations_for_filter' => $all_durations_for_filter, // Pass all durations from ALL results
        ]);
    }
}