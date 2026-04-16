<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class UniversityPrograms
{
    public function __invoke()
    {
        ob_start();
        $uni_id = isset($_GET['uni-id']) ? intval($_GET['uni-id']) : '';

        if (empty($uni_id)) {
            echo 'in';
        }
        else{
            // Check if the university is active in search
            $active_in_search = get_field('Active_in_Search', $uni_id);
            if ($active_in_search != '1' && $active_in_search !== true) {
                echo 'University not active in search';
                return ob_get_clean();
            }
            $post_title = get_the_title($uni_id);
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $sort = $_GET['sort'] ?? '';
            $degree = !empty($_GET['level']) ? intval($_GET['level']) : '';
            $country = !empty($_GET['country']) ? intval($_GET['country']) : '';
            $speciality = !empty($_GET['speciality']) ? intval($_GET['speciality']) : '';
            $search = !empty($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            $feeFilter = $_GET['feeFiter'] ?? '';
            $duration = $_GET['duration'] ?? '';
            $isScholarShip = $_GET['isScholarShip'] ?? '';
            $language = $_GET['language'] ?? '';

            if (!empty($duration)) {
                $duration = explode(' ', $duration)[0];
            }

            $tax_query = array('relation' => 'AND');
            $meta_query = array('relation' => 'AND');

            $meta_query[] = array(
                'key'   => 'zh_university',
                'value' => $uni_id,
                'compare' => '='
            );

            if (!empty($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
            }

            if (!empty($language)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-language',
                    'field'    => 'term_id',
                    'terms'    => $language,
                );
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
                    'fields' => 'ids',
                ));

                if ($university_query->have_posts()) {
                    $university_ids = $university_query->posts;
                }
            }

        if (!empty($search)) {
            $meta_query[] = array(
                'key'     => 'Product_Name',
                'value'   => $search,
                'compare' => 'LIKE',
            );
        }

            $args = array(
                'post_type'      => 'sit-program',
                'posts_per_page' => 21,
                'post_status'    => 'publish',
                'paged'          => $paged,
                'meta_query'     => $meta_query,
            );

            $pdf_args=array(
                'post_type'      => 'sit-program',
                'posts_per_page' => -1,
                'no_found_rows'  => true,
                'post_status'    => 'publish',
                'meta_query'     => $meta_query,
            );

            if (!empty($degree) || !empty($country) || !empty($speciality) || !empty($language)) {
                $args['tax_query'] = $tax_query;
                $pdf_args['tax_query'] = $tax_query;
            }

            switch ($sort) {
                case 'fee_low':
                    $args['meta_key'] = 'Official_Tuition';
                    $args['orderby']  = 'meta_value_num';
                    $args['order']    = 'ASC';
                    $pdf_args['meta_key'] = 'Official_Tuition';
                    $pdf_args['orderby']  = 'meta_value_num';
                    $pdf_args['order']    = 'ASC';
                    break;

                case 'fee_high':
                    $args['meta_key'] = 'Official_Tuition';
                    $args['orderby']  = 'meta_value_num';
                    $args['order']    = 'DESC';
                    $pdf_args['meta_key'] = 'Official_Tuition';
                    $pdf_args['orderby']  = 'meta_value_num';
                    $pdf_args['order']    = 'DESC';
                    break;

                case 'popular':
                    $args['meta_key'] = 'views_count';
                    $args['orderby']  = 'meta_value_num';
                    $args['order']    = 'DESC';
                    $pdf_args['meta_key'] = 'views_count';
                    $pdf_args['orderby']  = 'meta_value_num';
                    $pdf_args['order']    = 'DESC';
                    break;

                case 'newest':
                    $args['orderby'] = 'date';
                    $args['order']   = 'DESC';
                    $pdf_args['orderby'] = 'date';
                    $pdf_args['order']   = 'DESC';
                    break;
            }
            $uni_query = new \WP_Query($args);
            $universities = $uni_query->get_posts();

            // Pre-warm caches to prevent N+1 queries during mapping
            $page_post_ids = wp_list_pluck($universities, 'ID');
            if (!empty($page_post_ids)) {
                update_meta_cache('post', $page_post_ids);
                update_object_term_cache($page_post_ids, 'sit-program');
                
                $uni_ids_for_page = [];
                foreach ($page_post_ids as $pid) {
                    $uid = intval(get_post_meta($pid, 'zh_university', true));
                    if ($uid && !in_array($uid, $uni_ids_for_page)) {
                        $uni_ids_for_page[] = $uid;
                    }
                }
                if (!empty($uni_ids_for_page)) {
                    _prime_post_caches($uni_ids_for_page, true, true);
                    update_object_term_cache($uni_ids_for_page, 'sit-country');
                }
            }

            $universities = array_map(function ($university) {
                $oth_uniid = get_post_meta($university->ID, 'zh_university', true);
                $uni_title = get_the_title($oth_uniid);
//                return [
//                    'uni_id' => $university->ID,
//                    'title' => $university->post_title,
//                    'guid' => $university->guid,
//                    'country' => get_the_terms($university->ID, 'sit-country')[0]->name,
//                    'description' => get_post_meta($university->ID, 'Description', true),
//                    'ranking' => get_post_meta($university->ID, 'QS_Rank', true),
//                    'accommodation' => is_array(get_post_meta($university->ID, 'Accommodation', true)) ?
//                        implode(', ', get_post_meta($university->ID, 'Accommodation', true)) :
//                        get_post_meta($university->ID, 'Accommodation', true),
//                    'students' => get_post_meta($university->ID, 'Number_Of_Students', true),
//                    'year' => get_post_meta($university->ID, 'Year_Founded', true) ?
//                        date('Y', strtotime(get_post_meta($university->ID, 'Year_Founded', true))) :
//                        null,
//                    'image_url'=>!empty(get_post_meta($oth_uniid, 'uni_image', true))  ?
//                        esc_url(get_post_meta($oth_uniid, 'uni_image', true))
//                        :'https://placehold.co/714x340?text=University',
//                ];
                $country_terms = get_the_terms($university->ID, 'sit-country');
                return [
                    'uni_id'        => $university->ID,
                    'title' => $university->post_title,
                    'link' => $university->guid,
                    'uni_title' => $uni_title,
                    'country' => (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '',
                    'description' => !empty(get_post_meta($university->ID, 'Description', true)) ?
                        get_post_meta($university->ID, 'Description', true)
                        : 'Empty',
                    'ranking' => get_post_meta($oth_uniid, 'QS_Rank', true),
                    'duration' => get_post_meta($university->ID, 'Study_Years', true),
                    'students' => get_post_meta($oth_uniid, 'Number_Of_Students', true),
                    'fee' => get_post_meta($university->ID, 'Official_Tuition', true),
                    'Tuition_Currency' => get_post_meta($university->ID, 'Tuition_Currency', true),
                    'discounted_fee' => get_post_meta($university->ID, 'Discounted_Tuition', true),
                    'Advanced_Discount' => get_post_meta($university->ID, 'Advanced_Discount', true),
                    'image_url' => (function() use ($oth_uniid) {
                        $keys = ['uni_image', 'University_Image', 'uni_banner', 'Banner', 'uni_logo', 'University_Logo', 'Logo'];
                        foreach ($keys as $key) {
                            $img = get_post_meta($oth_uniid, $key, true);
                            if (!empty($img)) return esc_url($img);
                        }
                        return 'https://placehold.co/714x340?text=University';
                    })(),
                    'logo_url' => (function() use ($oth_uniid) {
                        $keys = ['uni_logo', 'University_Logo', 'Logo', 'uni_image'];
                        foreach ($keys as $key) {
                            $img = get_post_meta($oth_uniid, $key, true);
                            if (!empty($img)) return esc_url($img);
                        }
                        return 'https://placehold.co/128x128?text=U';
                    })(),
                ];
            }, $universities);

            $pdf = [];
            if (isset($_GET['download']) && $_GET['download'] == 1) {
                $pdf_query = new \WP_Query($pdf_args);
                $pdf_posts = $pdf_query->get_posts();
                
                // Pre-warm caches for PDF mapping
                $pdf_post_ids = wp_list_pluck($pdf_posts, 'ID');
                if (!empty($pdf_post_ids)) {
                    update_meta_cache('post', $pdf_post_ids);
                    
                    $pdf_uni_ids = [];
                    foreach ($pdf_post_ids as $pid) {
                        $uid = intval(get_post_meta($pid, 'zh_university', true));
                        if ($uid && !in_array($uid, $pdf_uni_ids)) {
                            $pdf_uni_ids[] = $uid;
                        }
                    }
                    if (!empty($pdf_uni_ids)) {
                        _prime_post_caches($pdf_uni_ids, true, true);
                        update_object_term_cache($pdf_uni_ids, 'sit-country');
                    }
                }
                
                $pdf = array_map(function ($university) {
                    $oth_uniid = get_post_meta($university->ID, 'zh_university', true);
                    $uni_title = get_the_title($oth_uniid);
                    $country_terms = get_the_terms($university->ID, 'sit-country');
                    return [
                        'uni_id'        => $university->ID,
                        'title' => $university->post_title,
                        'link' => $university->guid,
                        'uni_title' => $uni_title,
                        'country' => (!empty($country_terms) && !is_wp_error($country_terms)) ? $country_terms[0]->name : '',
                        'description' => !empty(get_post_meta($university->ID, 'Description', true)) ?
                            get_post_meta($university->ID, 'Description', true)
                            : 'Empty',
                        'ranking' => get_post_meta($oth_uniid, 'QS_Rank', true),
                        'duration' => get_post_meta($university->ID, 'Study_Years', true),
                        'students' => get_post_meta($oth_uniid, 'Number_Of_Students', true),
                        'fee' => get_post_meta($university->ID, 'Official_Tuition', true),
                        'discounted_fee' => get_post_meta($university->ID, 'Discounted_Tuition', true),
                        'Advanced_Discount' => get_post_meta($university->ID, 'Advanced_Discount', true),
                        'image_url' => (function() use ($oth_uniid) {
                            $keys = ['uni_image', 'University_Image', 'uni_banner', 'Banner', 'uni_logo', 'University_Logo', 'Logo'];
                            foreach ($keys as $key) {
                                $img = get_post_meta($oth_uniid, $key, true);
                                if (!empty($img)) return esc_url($img);
                            }
                            return 'https://placehold.co/714x340?text=University';
                        })(),
                        'logo_url' => (function() use ($oth_uniid) {
                            $keys = ['uni_logo', 'University_Logo', 'Logo', 'uni_image'];
                            foreach ($keys as $key) {
                                $img = get_post_meta($oth_uniid, $key, true);
                                if (!empty($img)) return esc_url($img);
                            }
                            return 'https://placehold.co/128x128?text=U';
                        })(),
                    ];
                }, $pdf_posts);
            }

            $disstr='';
            if($post_title != ''){
                $disstr="This document provides a comprehensive list of programs degrees in ".$post_title.". Each program includes details about duration, tuition fees, language requirements, application deadlines, and more.";
            }

            Template::render('shortcodes/university-programs',['disstr'=>$disstr, 'pdf_program' => $pdf,'programs' => $universities,'uni_title' => $post_title,'query' => $uni_query, 'degreeid' => $degree, 'specialityid' => $speciality, 'countryid' => $country]);
        }
        return ob_get_clean();
    }
}