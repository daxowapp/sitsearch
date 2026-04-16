<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class ProgramCity
{
    public function __invoke()
    {
        $queried_object = get_queried_object();
        $term = get_queried_object();
        $country_zoho_id = get_term_meta($term->term_id, 'zoho_country_id', true);

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
        $all_universities = get_posts(array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        // Use CachedData for active university lookups instead of looping
        $globally_active_ids = \SIT\Search\Services\CachedData::get_active_university_ids();
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
                $active_university_ids_filtered = array_intersect($university_ids, $globally_active_ids);
                
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
                'no_found_rows'  => true,
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

        if (!empty($degree) || !empty($country) || !empty($speciality) || !empty($modeOfStudy) || !empty($degreeType) || is_tax()) {
            $args['tax_query'] = $tax_query;
        }

        switch ($sort) {
            case 'fee_low':
                $args['meta_key'] = 'Official_Tuition';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;

            case 'fee_high':
                $args['meta_key'] = 'Official_Tuition';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;

            case 'popular':
                $args['meta_key'] = 'views_count';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;

            case 'newest':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
        }
//        echo '<pre>';
//        print_r($args);
//        echo '</pre>';
        $query = new \WP_Query($args);

        // Pre-warm meta and term caches for all posts in this page
        $page_post_ids = wp_list_pluck($query->posts, 'ID');
        if (!empty($page_post_ids)) {
            update_meta_cache('post', $page_post_ids);
            update_object_term_cache($page_post_ids, 'sit-program');
            
            // Also pre-warm university meta and post objects
            $uni_ids_for_page = [];
            foreach ($page_post_ids as $pid) {
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
                'image_url' => !empty(get_post_meta($oth_uniid, 'uni_image', true)) ?
                    esc_url(get_post_meta($oth_uniid, 'uni_image', true))
                    : 'https://placehold.co/714x340?text=University',
            ];
        }, $query->posts);

        // Extract unique languages from the results
        $all_program_posts = $query->posts;
        $unique_languages = [];
        foreach ($all_program_posts as $program_post) {
            $language_terms = get_the_terms($program_post->ID, 'sit-language');
            if ($language_terms && !is_wp_error($language_terms)) {
                foreach ($language_terms as $term) {
                    if (!isset($unique_languages[$term->term_id])) {
                        $unique_languages[$term->term_id] = $term;
                    }
                }
            }
        }

        // Get all degrees for the degree filter
        $all_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);

        // Create a separate query to get ALL programs for filter options (not paginated)
        $filter_args = $args;
        $filter_args['posts_per_page'] = 1000; // Limit to 1000 for filters to prevent memory issues
        $filter_args['fields'] = 'ids'; // Only get IDs
        $filter_args['no_found_rows'] = true;
        unset($filter_args['paged']); // Remove pagination
        $filter_query = new \WP_Query($filter_args);
        
        // Get all data from ALL results for filters
        $all_program_ids = $filter_query->posts;
        $all_universities_for_filter = [];
        $all_durations_for_filter = [];
        
        // Pre-warm caches for the filter extraction loop
        if (!empty($all_program_ids)) {
            update_meta_cache('post', $all_program_ids);
            update_object_term_cache($all_program_ids, 'sit-program');
        }

        foreach ($all_program_ids as $program_id) {
            // Extract universities
            $university_id = intval(get_post_meta($program_id, 'zh_university', true));
            if ($university_id) {
                // Fast cache access
                $university_title = get_the_title($university_id);
                if ($university_title && !in_array($university_title, $all_universities_for_filter)) {
                    $all_universities_for_filter[] = $university_title;
                }
            }
            
            // Extract durations
            $duration = get_post_meta($program_id, 'Study_Years', true);
            if ($duration && !in_array($duration, $all_durations_for_filter)) {
                $all_durations_for_filter[] = $duration;
            }
        }
        
        // Sort arrays
        sort($all_universities_for_filter);
        sort($all_durations_for_filter);

        Template::render('shortcodes/program-archive', [
            'programs' => $programs,
            'archiveitle' => $archive_title,
            'query' => $query,
            'cities' => '',
            'available_languages' => $unique_languages, // Pass unique languages to the template
            'all_degrees' => $all_degrees, // Pass all degrees to the template
            'all_universities_for_filter' => $all_universities_for_filter, // Pass all universities from ALL results
            'all_durations_for_filter' => $all_durations_for_filter, // Pass all durations from ALL results
        ]);
    }
}