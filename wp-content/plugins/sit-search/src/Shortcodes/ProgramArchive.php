<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;
use SIT\Search\Services\CachedData;

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
        $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
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

        $feeFilter = isset($_GET['feeFilter']) ? sanitize_text_field($_GET['feeFilter']) : '';
        $duration = isset($_GET['duration']) ? sanitize_text_field($_GET['duration']) : '';
        $isScholarShip = isset($_GET['isScholarShip']) ? sanitize_text_field($_GET['isScholarShip']) : '';
        $language = isset($_GET['language']) ? sanitize_text_field($_GET['language']) : '';
        $modeOfStudy = isset($_GET['modeOfStudy']) ? sanitize_text_field($_GET['modeOfStudy']) : '';
        $degreeType = isset($_GET['degreeType']) ? sanitize_text_field($_GET['degreeType']) : '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        // Apply AI Smart Filters
        $ai_terms = [];
        $university = '';
        if (!empty($search)) {
            $ai_result = \SIT\Search\Services\AiSearchHelper::expand_search($search);
            $ai_terms = $ai_result['terms'];
            $ai_filters = $ai_result['filters'] ?? [];
            
            if (empty($degree) && !empty($ai_filters['degree'])) {
                $term = get_term_by('name', $ai_filters['degree'], 'sit-degree');
                if ($term) $degree = $term->term_id;
            }
            if (empty($language) && !empty($ai_filters['language'])) {
                $language = $ai_filters['language']; // handled naturally below
            }
            if (empty($city) && !empty($ai_filters['city']) && !is_tax('sit-city')) {
                // In ProgramArchive, city is tricky if we are on a city archive
                $term = get_term_by('name', $ai_filters['city'], 'sit-city');
                if ($term) {
                    // Check if a city is not already hardcoded by the queried object
                    // In ProgramArchive we don't have $_GET['city'] directly unless it's a tag/cat
                    // Note: Here $tax_query takes care of current taxonomy
                }
            }
            if (empty($country) && !empty($ai_filters['country']) && !is_tax('sit-country')) {
                $term = get_term_by('name', $ai_filters['country'], 'sit-country');
                if ($term) $country = $term->term_id;
            }
            
            $university = $_GET['university'] ?? '';
            if (empty($university) && !empty($ai_filters['university'])) {
                $university = is_array($ai_filters['university']) ? $ai_filters['university'] : [$ai_filters['university']];
            }
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

//        if (!empty($country)) {
//            $tax_query[] = array(
//                'taxonomy' => 'sit-country',
//                'field'    => 'term_id',
//                'terms'    => $country,
//            );
//        }

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
        $uni_args = array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'fields' => 'ids'
        );

        if (!empty($country)) {
            $uni_args['tax_query'] = array(
                array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $country,
                )
            );
        }


        $all_universities = get_posts($uni_args);

        // Use CachedData for active university lookups instead of looping
        $globally_active_ids = CachedData::get_active_university_ids();
        $active_university_ids = array_intersect($all_universities, $globally_active_ids);
        
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
        $university = $university ?: ($_GET['university'] ?? '');
        if (!empty($university)) {
            $universities = is_array($university) ? $university : [$university];
            $university_ids = array();
            
            foreach ($universities as $uni_name) {
                $university_posts = get_posts(array(
                    'post_type' => 'sit-university',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'no_found_rows' => true,
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

        $search_terms = $ai_terms;
        $search_program_ids = [];

        if (!empty($search_terms)) {
            global $wpdb;
            $term_sql = [];
            foreach ($search_terms as $term) {
                $term_sql[] = $wpdb->prepare("meta_value LIKE %s", '%' . $wpdb->esc_like(trim($term)) . '%');
            }
            $or_clause = implode(' OR ', $term_sql);

            // 1. Find universities that match the AI terms
            $uni_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'Account_Name' AND ($or_clause)");
            
            $uni_condition = "";
            if (!empty($uni_ids)) {
                $escaped_uni_ids = implode(',', array_map('intval', $uni_ids));
                $uni_condition = " OR (meta_key = 'zh_university' AND meta_value IN ($escaped_uni_ids))";
            }

            // 1.5 Find programs matching taxonomy terms (speciality/degree)
            $tax_term_sql = [];
            foreach ($search_terms as $term) {
                $tax_term_sql[] = $wpdb->prepare("t.name LIKE %s", '%' . $wpdb->esc_like(trim($term)) . '%');
            }
            $tax_or_clause = implode(' OR ', $tax_term_sql);

            $tax_program_ids = $wpdb->get_col("
                SELECT tr.object_id FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tt.taxonomy IN ('sit-speciality', 'sit-degree') AND ($tax_or_clause)
            ");

            $tax_condition = "";
            if (!empty($tax_program_ids)) {
                $escaped_tax_prog_ids = implode(',', array_map('intval', $tax_program_ids));
                $tax_condition = " OR p.post_id IN ($escaped_tax_prog_ids)";
            }

            // 2. Find all programs that either match the terms directly OR belong to matching universities OR match taxonomy
            $matched_programs_sql = "
                SELECT DISTINCT p.post_id FROM {$wpdb->postmeta} p 
                INNER JOIN {$wpdb->posts} posts ON posts.ID = p.post_id 
                WHERE posts.post_type = 'sit-program' AND posts.post_status = 'publish'
                AND (
                    (meta_key = 'Product_Name' AND ($or_clause))
                    $uni_condition
                    $tax_condition
                )
            ";
            $search_program_ids = $wpdb->get_col($matched_programs_sql);
            
            if (empty($search_program_ids)) {
                // If AI terms found nothing, force zero results
                $search_program_ids = [-1];
            }
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
            'posts_per_page' => 500, // Limit PDF export to 500 programs to prevent memory issues
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
            'fields'         => 'ids', // Only get IDs for PDF, not full objects
        );

        if (!empty($search_program_ids)) {
            $args['post__in'] = $search_program_ids;
            $pdf_arg['post__in'] = $search_program_ids;
        }

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



        
        // Create a separate query to get program IDs for filter options (not full objects)
        $filter_args = $args;
        $filter_args['posts_per_page'] = 1000; // Limit to 1000 for filters to prevent memory issues
        $filter_args['fields'] = 'ids'; // Only get IDs, not full post objects
        $filter_args['no_found_rows'] = true;
        unset($filter_args['paged']); // Remove pagination
        $filter_query = new \WP_Query($filter_args);

        // Pre-warm meta and term caches for all posts in this page
        $page_post_ids = wp_list_pluck($query->posts, 'ID');
        if (!empty($page_post_ids)) {
            update_meta_cache('post', $page_post_ids);
            update_object_term_cache($page_post_ids, 'sit-program');
            
            // Also pre-warm university meta and post objects
            $uni_ids_for_page = [];
            foreach ($page_post_ids as $pid) {
                // Cast to int for _prime_post_caches
                $uid = intval(get_post_meta($pid, 'zh_university', true));
                if ($uid && !in_array($uid, $uni_ids_for_page)) {
                    $uni_ids_for_page[] = $uid;
                }
            }
            if (!empty($uni_ids_for_page)) {
                _prime_post_caches($uni_ids_for_page, true, true);
                update_object_term_cache($uni_ids_for_page, 'sit-university');
            }
        }

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
                'Tuition_Currency' => get_post_meta($post->ID, 'Tuition_Currency', true) ?: 'USD',
                'image_url' => !empty(get_post_meta($oth_uniid, 'uni_image', true)) ?
                    esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
            ];
        }, $query->posts);

        // Extract unique languages from filter results (now IDs only)
        $all_program_ids = $filter_query->posts; // These are now just IDs
        $unique_languages = [];
        $all_universities_for_filter = [];
        $all_durations_for_filter = [];

        // Pre-warm caches for the filter extraction loop
        if (!empty($all_program_ids)) {
            update_meta_cache('post', $all_program_ids);
            update_object_term_cache($all_program_ids, 'sit-program');
        }
        
        foreach ($all_program_ids as $program_id) {
            // Extract languages
            $language_terms = get_the_terms($program_id, 'sit-language');
            if ($language_terms && !is_wp_error($language_terms)) {
                foreach ($language_terms as $term) {
                    if (!isset($unique_languages[$term->term_id])) {
                        $unique_languages[$term->term_id] = $term;
                    }
                }
            }
            
            // Extract universities
            $uni_id = get_post_meta($program_id, 'zh_university', true);
            if ($uni_id) {
                $uni_title = get_the_title($uni_id);
                if ($uni_title && !in_array($uni_title, $all_universities_for_filter)) {
                    $all_universities_for_filter[] = $uni_title;
                }
            }
            
            // Extract durations
            $dur_val = get_post_meta($program_id, 'Study_Years', true);
            if ($dur_val && !in_array($dur_val, $all_durations_for_filter)) {
                $all_durations_for_filter[] = $dur_val;
            }
        }
        
        // Sort the arrays
        sort($all_universities_for_filter);
        sort($all_durations_for_filter);

        // Get all degrees for the degree filter
        $all_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);

        $pdf_program = [];
        if (isset($_GET['download']) && $_GET['download'] == 1 && count($pdf_query->posts) > 0) {
            $pdf_program_ids = $pdf_query->posts;
            
            // Pre-warm caches for the PDF export to avoid 500 * N+1 queries
            update_meta_cache('post', $pdf_program_ids);
            update_object_term_cache($pdf_program_ids, 'sit-program');
            
            $pdf_uni_ids = [];
            foreach ($pdf_program_ids as $pid) {
                $uid = intval(get_post_meta($pid, 'zh_university', true));
                if ($uid && !in_array($uid, $pdf_uni_ids)) {
                    $pdf_uni_ids[] = $uid;
                }
            }
            if (!empty($pdf_uni_ids)) {
                _prime_post_caches($pdf_uni_ids, true, true);
                update_object_term_cache($pdf_uni_ids, 'sit-university');
            }

            $pdf_program = array_map(function ($post_id) {
                // $post_id is now just an integer ID, not a post object
                $post = get_post($post_id);
                
                $oth_uniid = get_post_meta($post_id, 'zh_university', true);
                $unii_title = get_the_title($oth_uniid);
                $country_terms = get_the_terms($post_id, 'sit-country');
                $country_name = !empty($country_terms) && !is_wp_error($country_terms)
                    ? $country_terms[0]->name
                    : '';

                return [
                    'uni_id'        => $post_id,
                    'title' => $post ? $post->post_title : '',
                    'link' => $post ? $post->guid : '',
                    'uni_title' => $unii_title,
                    'country' => $country_name,
                    'description' => !empty(get_post_meta($post_id, 'Description', true)) ?
                        get_post_meta($post_id, 'Description', true)
                        : 'Empty',
                    'ranking' => get_post_meta($oth_uniid, 'QS_Rank', true),
                    'duration' => get_post_meta($post_id, 'Study_Years', true),
                    'students' => get_post_meta($oth_uniid, 'Number_Of_Students', true),
                    'fee' => get_post_meta($post_id, 'Official_Tuition', true),
                    'discounted_fee' => get_post_meta($post_id, 'Discounted_Tuition', true),
                    'Advanced_Discount' => get_post_meta($post_id, 'Advanced_Discount', true),
                    'Tuition_Currency' => get_post_meta($post_id, 'Tuition_Currency', true) ?: 'USD',
                    'image_url' => !empty(get_post_meta($oth_uniid, 'uni_image', true)) ?
                        esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                        : 'https://placehold.co/714x340?text=University',
                ];
            }, $pdf_query->posts);
        }


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