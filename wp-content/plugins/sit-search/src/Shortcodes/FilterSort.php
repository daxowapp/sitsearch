<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Modules\Program;
use SIT\Search\Services\Template;
use SIT\Search\Services\FeaturedUniversity;
use SIT\Search\Services\CachedData;

class FilterSort
{
    public function __invoke()
    {
        // Session should be started in App.php init hook, not here
        // Removed session_start() to prevent "headers already sent" warning
        
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Sanitize input
        $type = isset($_GET['univerity-type']) && $_GET['univerity-type'] != 0 ? $_GET['univerity-type'] : '';

        
        $feeFilter = $_GET['feeFiter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';
        $city = $_GET['city'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');
        $degree_name='';
        $country_name='';
        $speciality_name='';
        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
                // Get names of all selected degrees
                $degree_names = array();
                foreach ($degree as $degree_id) {
                    $term = get_term($degree_id);
                    if ($term) {
                        $degree_names[] = $term->name;
                    }
                }
                $degree_name = implode(', ', $degree_names);
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
                $term = get_term($degree);
                $degree_name = $term ? $term->name : '';
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($lang_value)) {
                    // It's a term ID
                    $language_terms[] = intval($lang_value);
                } else {
                    // It's a term name, find by name
                    $lang_term = get_term_by('name', trim($lang_value), 'sit-language');
                    if ($lang_term) {
                        $language_terms[] = $lang_term->term_id;
                    }
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

        // Add city filter (supports multiple selections)
        $city_name = '';
        if (!empty($city)) {
            $cities = is_array($city) ? $city : [$city];
            $city_terms = array();
            foreach ($cities as $city_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($city_value)) {
                    // It's a term ID
                    $city_terms[] = intval($city_value);
                } else {
                    // It's a term name, find by name
                    $city_term = get_term_by('name', trim($city_value), 'sit-city');
                    if ($city_term) {
                        $city_terms[] = $city_term->term_id;
                    }
                }
            }
            if (!empty($city_terms)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-city',
                    'field'    => 'term_id',
                    'terms'    => $city_terms,
                    'operator' => 'IN',
                );
                // Get city name for display
                if (count($city_terms) === 1) {
                    $term = get_term($city_terms[0]);
                    $city_name = $term ? $term->name : '';
                }
            }
        }

        // Always enforce Turkey and North Cyprus restriction
        $allowed_countries = array();
        
        // Get Turkey and North Cyprus term IDs
        $turkey_term = get_term_by('name', 'Turkey', 'sit-country');
        $north_cyprus_term = get_term_by('name', 'North Cyprus', 'sit-country');
        
        if ($turkey_term) {
            $allowed_countries[] = $turkey_term->term_id;
        }
        if ($north_cyprus_term) {
            $allowed_countries[] = $north_cyprus_term->term_id;
        }

        // Determine target country
        $target_country_ids = [];
        if (!empty($country)) {
            // Trust user selection if set
            $target_country_ids = [$country];
            $term = get_term($country);
            $country_name = $term ? $term->name : '';
        } else {
            // Default to Allowed (Turkey + NC)
            $target_country_ids = $allowed_countries;
        }

        // Prepare University Query Args to get Active Unis in Target Countries
        $uni_args = array(
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $target_country_ids,
                    'operator' => 'IN'
                )
            )
        );

        $potential_unis = get_posts($uni_args);
        
        $active_university_ids = array();
        foreach($potential_unis as $uid) {
             // Check ACF field for active in search
             $is_active = get_field('Active_in_Search', $uid);
             if($is_active == '1' || $is_active === true) {
                 $active_university_ids[] = $uid;
             }
        }
        
        // Restore Speciality Filter
        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
            $term = get_term($speciality);
            $speciality_name=$term->name;
        }

        $meta_query = array('relation' => 'AND');

        $meta_query[] = array(
            'key'     => 'zh_university',
            'compare' => 'EXISTS',
        );

        // Filter valid universities by country
        
        if (!empty($active_university_ids)) {
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $active_university_ids,
                'compare' => 'IN',
            );
        } else {
            // No active universities found in the selected country
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
                $active_university_ids = array();
                foreach ($university_ids as $uni_id) {
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
                        'value'   => array(-1), // Non-existent ID to return no results
                        'compare' => 'IN',
                    );
                }
            }
        }

        $university_ids = array();

        if (!empty($search)) {
            $uni_type_search_ids=self::get_uni__search_ids();
            foreach ($uni_type_search_ids as $item) {
                if (!in_array($item, $university_ids)) {
                    $university_ids[] = $item;
                }
            }
        }

        $uni_type_ids=self::get_uni_ids();
        if((!empty($type) && $type != 'All') && empty($search)){
            foreach ($uni_type_ids as $item) {
                if (!in_array($item, $university_ids)) {
                    $university_ids[] = $item;
                }
            }
        }

        if((!empty($type) && $type != 'All') && !empty($search)){
            $university_ids = array_values(array_filter($university_ids, function($item) use ($uni_type_ids) {
                return in_array($item, $uni_type_ids);
            }));
        }




        $search_terms = !empty($search) ? array_map('trim', explode(',', $search)) : [];
        $program_search_conditions = ['relation' => 'OR'];
        if (!empty($search_terms)) {
            foreach ($search_terms as $term) {
                $program_search_conditions[] = [
                    'key'     => 'Product_Name',
                    'value'   => $term,
                    'compare' => 'LIKE',
                ];
            }
        }

        $safe_uni_ids = empty($university_ids) ? [-1] : $university_ids;

        if (!empty($search_terms) && (!empty($type) && $type != 'All')) {
            $meta_query[] = array(
                'relation' => 'AND',
                array(
                    'key'     => 'zh_university',
                    'value'   => $safe_uni_ids,
                    'compare' => 'IN',
                ),
                $program_search_conditions,
            );
        } elseif(empty($search_terms) && (!empty($type) && $type != 'All')){
            $meta_query[] = array(
                'key'     => 'zh_university',
                'value'   => $safe_uni_ids,
                'compare' => 'IN',
            );
        } elseif(!empty($search_terms) && empty($type)){
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'zh_university',
                    'value'   => $safe_uni_ids,
                    'compare' => 'IN',
                ),
                $program_search_conditions,
            );
        } 




        // 1. Fetch ALL Matching Posts (No Pagination in Query)
        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1, // Fetch ALL
            'post_status'    => 'publish',
            // 'paged'          => $paged, // Removed, we handle paging manually
            'meta_query'     => $meta_query,
            'no_found_rows'  => false,
            'distinct'       => true, 
        );
        $pdf_args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
            'distinct'       => true, 
        );

        if (!empty($degree) || !empty($country) || !empty($speciality) || !empty($city) || !empty($language)) {
            $args['tax_query'] = $tax_query;
            $pdf_args['tax_query'] = $tax_query;
        }

        // Execute Queries
        $query = new \WP_Query($args);
        $pdf_query = new \WP_Query($pdf_args);
        
        $all_programs_raw = $query->get_posts();
        $pdf_programs = $pdf_query->get_posts();
        
        // 2. Map Data & Deduplicate (Prepare for Sorting)
        // We need to map FIRST to get the 'is_featured' and 'priority' flags for sorting
        $mapped_programs = [];
        $seen_ids = [];
        
        foreach ($all_programs_raw as $program) {
            if (in_array($program->ID, $seen_ids)) continue;
            
            $uniid = get_post_meta($program->ID, 'zh_university', true);
            $university = get_post($uniid);
            if (!$university) continue;

            $is_featured = (new FeaturedUniversity())->isFeatured($uniid);
            $zoho_product_id = get_post_meta($program->ID, 'zoho_product_id', true);
            
            // Minimal data needed for sorting + display
            // We map fully here so the sort function has access to everything
            $mapped_programs[] = [
                'id'                => $program->ID,
                'pro_id'            => $zoho_product_id,
                'university_id'     => $uniid,
                'program_name'      => $program->post_title,
                'title'             => $program->post_title,
                'description'       => get_post_meta($program->ID, 'Description', true) ?: '',
                'link'              => get_permalink($program->ID),
                'university_name'   => $university->post_title,
                'uni_title'         => $university->post_title,
                'country'           => !empty(get_the_terms($university->ID, 'sit-country')) ? get_the_terms($university->ID, 'sit-country')[0]->name : '',
                'program_lang'      => !empty(get_the_terms($program->ID, 'sit-language')) ? get_the_terms($program->ID, 'sit-language')[0]->name : '',
                'language'          => !empty(get_the_terms($program->ID, 'sit-language')) ? get_the_terms($program->ID, 'sit-language')[0]->name : '',
                'program_price'     => get_post_meta($program->ID, 'official_tuition', true) ?: get_post_meta($program->ID, 'Official_Tuition', true),
                'program_price_dis' => get_post_meta($program->ID, 'advanced_discount', true) ?: get_post_meta($program->ID, 'Advanced_Discount', true),
                'program_years'     => get_post_meta($program->ID, 'study_years', true) ?: get_post_meta($program->ID, 'Study_Years', true),
                'program_degree'    => strip_tags(get_the_term_list($program->ID, 'sit-degree', '', ', ')),
                'university_slug'   => $university->post_name,
                'is_featured'       => $is_featured,
                'featured_class'    => $is_featured ? 'sit-featured-program' : '',
                'ranking'           => get_post_meta($university->ID, 'qs_rank', true) ?: get_post_meta($university->ID, 'QS_Rank', true),
                'duration'          => get_post_meta($program->ID, 'study_years', true) ?: get_post_meta($program->ID, 'Study_Years', true),
                'students'          => get_post_meta($university->ID, 'number_of_students', true) ?: get_post_meta($university->ID, 'Number_Of_Students', true),
                'fee'               => get_post_meta($program->ID, 'official_tuition', true) ?: get_post_meta($program->ID, 'Official_Tuition', true),
                'Tuition_Currency'  => get_post_meta($program->ID, 'tuition_currency', true) ?: get_post_meta($program->ID, 'Tuition_Currency', true),
                'discounted_fee'    => get_post_meta($program->ID, 'discounted_tuition', true) ?: get_post_meta($program->ID, 'Discounted_Tuition', true),
                'Advanced_Discount' => get_post_meta($program->ID, 'advanced_discount', true) ?: get_post_meta($program->ID, 'Advanced_Discount', true),
                'university_priority' => get_post_meta($university->ID, 'university_priority', true) ?: get_post_meta($university->ID, 'University_Priority', true),
                'sit_featured_priority' => get_post_meta($university->ID, 'sit_featured_priority', true),
                'views_count'       => get_post_meta($program->ID, 'views_count', true),
                'date'              => $program->post_date,
                'image_url'         => (function() use ($university) {
                    $keys = ['uni_image', 'University_Image', 'uni_logo', 'University_Logo', 'Logo', 'uni_banner', 'Banner'];
                    foreach ($keys as $key) {
                        $img = get_post_meta($university->ID, $key, true);
                        if (!empty($img)) return esc_url($img);
                    }
                    return \SIT\Search\Services\URLHelper::placeholder(714, 340, 'University');
                })(),
                'city'              => !empty(get_the_terms($program->ID, 'sit-city')) ? get_the_terms($program->ID, 'sit-city')[0]->name : '',
            ];
            $seen_ids[] = $program->ID;
        }

        // 3. Global Sorting
        usort($mapped_programs, function($a, $b) use ($sort) {
            // 0. Sort by Featured University status (highest priority)
            $featured_a = !empty($a['is_featured']) ? 1 : 0;
            $featured_b = !empty($b['is_featured']) ? 1 : 0;
            
            if ($featured_a !== $featured_b) {
                return $featured_b - $featured_a; // Featured first
            }

            // 1. Sort by university priority (higher priority first)
            // Check both old 'university_priority' and new 'sit_featured_priority'
            $priority_a = max(intval($a['university_priority'] ?: 0), intval($a['sit_featured_priority'] ?: 0));
            $priority_b = max(intval($b['university_priority'] ?: 0), intval($b['sit_featured_priority'] ?: 0));
            
            if ($priority_a !== $priority_b) {
                return $priority_b - $priority_a; // Higher priority first
            }
            
            // 2. User Selected Sort
            switch ($sort) {
                case 'fee_low':
                case 'fee_high':
                    // Use effective fee: discounted_fee if available, otherwise Advanced_Discount, otherwise fee (official)
                    $effective_fee_a = !empty($a['discounted_fee']) ? intval($a['discounted_fee']) : 
                                       (!empty($a['Advanced_Discount']) ? intval($a['Advanced_Discount']) : intval($a['fee'] ?: 0));
                    $effective_fee_b = !empty($b['discounted_fee']) ? intval($b['discounted_fee']) : 
                                       (!empty($b['Advanced_Discount']) ? intval($b['Advanced_Discount']) : intval($b['fee'] ?: 0));
                    
                    if ($sort === 'fee_low') {
                        return $effective_fee_a - $effective_fee_b;
                    } else {
                        return $effective_fee_b - $effective_fee_a;
                    }
                case 'popular':
                    return intval($b['views_count'] ?: 0) - intval($a['views_count'] ?: 0);
                case 'newest':
                    return strtotime($b['date'] ?: '0') - strtotime($a['date'] ?: '0');
                default:
                    return 0; 
            }
        });

        // 4. Pagination (Manual Slicing)
        $items_per_page = 21;
        $total_items = count($mapped_programs);
        $max_num_pages = ceil($total_items / $items_per_page);
        
        // Ensure paged is valid
        if ($paged < 1) $paged = 1;
        if ($paged > $max_num_pages) $paged = $max_num_pages;
        if($max_num_pages == 0) $paged = 1;

        $offset = ($paged - 1) * $items_per_page;
        $programs_slice = array_slice($mapped_programs, $offset, $items_per_page);

        // Fetch Featured Universities for the top section (if needed)
        // Only show strictly separate featured items if we are on page 1 AND we want a separate section
        // BUT user asked to integrate them. 
        // We'll keep this array empty or just for the 'Featured' object if the template uses it for something else.
        $featured_universities = []; // Logic integrated into main list

        // Fetch degree terms for filters
        $all_degrees = get_terms(['taxonomy' => 'sit-degree', 'hide_empty' => false]);

        // ========================================
        // DYNAMIC FILTERS: Extract unique values from ALL mapped programs (not just paginated slice)
        // This ensures filter options reflect the full result set, not just current page
        // ========================================
        $unique_languages = [];
        $unique_universities = [];
        $unique_durations = [];
        $unique_degree_ids = [];
        $unique_cities = [];

        foreach ($mapped_programs as $program) {
            // Languages
            if (!empty($program['language'])) {
                $unique_languages[$program['language']] = true;
            }
            
            // Universities
            if (!empty($program['university_name'])) {
                $unique_universities[$program['university_name']] = true;
            }
            
            // Durations (normalized to standard format)
            if (!empty($program['duration'])) {
                $dur = intval($program['duration']);
                if ($dur >= 1 && $dur <= 4) {
                    $unique_durations[$dur . ($dur == 1 ? ' year' : ' years')] = true;
                } elseif ($dur > 4) {
                    $unique_durations['4+ years'] = true;
                }
            }
            
            // Cities - extract from program
            if (!empty($program['city'])) {
                $unique_cities[$program['city']] = true;
            }
        }

        // Get available language terms that exist in results
        $available_languages = [];
        foreach (array_keys($unique_languages) as $lang_name) {
            $term = get_term_by('name', $lang_name, 'sit-language');
            if ($term && !is_wp_error($term)) {
                $available_languages[] = $term;
            }
        }

        // Filter degrees to only those that exist in the result set
        // We need to check the original programs for degree terms
        $available_degrees = [];
        if (!empty($all_degrees) && !is_wp_error($all_degrees)) {
            foreach ($all_degrees as $degree_term) {
                // Check if any program in results has this degree
                foreach ($all_programs_raw as $prog) {
                    if (has_term($degree_term->term_id, 'sit-degree', $prog->ID)) {
                        $available_degrees[] = $degree_term;
                        break; // Found one, no need to check more
                    }
                }
            }
        }
        
        // Sort universities alphabetically
        $all_universities_for_filter = array_keys($unique_universities);
        sort($all_universities_for_filter);
        
        // Available durations (sorted)
        $available_durations = array_keys($unique_durations);
        usort($available_durations, function($a, $b) {
            return intval($a) - intval($b);
        });

        // Get available city terms that exist in results
        $available_cities = [];
        foreach (array_keys($unique_cities) as $city_name_key) {
            $term = get_term_by('name', $city_name_key, 'sit-city');
            if ($term && !is_wp_error($term)) {
                $available_cities[] = $term;
            }
        }
        // Sort cities alphabetically
        usort($available_cities, function($a, $b) {
            return strcasecmp($a->name, $b->name);
        });

        ob_start();
        Template::render('shortcodes/filter-sort', [
            'programs'            => $programs_slice, // Pass the sliced items
            'pdf_programs'        => $pdf_programs,
            'featured_universities' => $featured_universities,
            'paged'               => $paged,
            'max_num_pages'       => $max_num_pages, // Pass calculated max pages
            'found_posts'         => $total_items,   // Pass total count
            'degree'              => $degree_name,
            'country'             => $country_name,
            'speciality'          => $speciality_name,
            'search_keyword'      => $search,
            'query'               => $query, // Pass original query object (though its posts are huge now, template uses max_num_pages mainly)
            'degreeid'            => $degree,
            'countryid'           => $country,
            'specialityid'        => $speciality,
            'all_degrees'         => $all_degrees, // Pass all degrees for filter sidebar
            // Dynamic filter options based on current results
            'available_languages' => $available_languages,
            'available_degrees'   => !empty($available_degrees) ? $available_degrees : $all_degrees,
            'all_universities_for_filter' => $all_universities_for_filter,
            'available_durations' => $available_durations,
            'available_cities'    => $available_cities,
            'cityid'              => !empty($city_terms) ? $city_terms : [],
            'city_name'           => $city_name,
        ]);
        return ob_get_clean();


    }

    public function get_uni_ids(){

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Sanitize input
        $type = isset($_GET['univerity-type']) && $_GET['univerity-type'] != 0 ? $_GET['univerity-type'] : '';

        $feeFilter = $_GET['feeFiter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');
        $degree_name='';
        $country_name='';
        $speciality_name='';
        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
                // Get names of all selected degrees
                $degree_names = array();
                foreach ($degree as $degree_id) {
                    $term = get_term($degree_id);
                    if ($term) {
                        $degree_names[] = $term->name;
                    }
                }
                $degree_name = implode(', ', $degree_names);
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
                $term = get_term($degree);
                $degree_name = $term ? $term->name : '';
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($lang_value)) {
                    // It's a term ID
                    $language_terms[] = intval($lang_value);
                } else {
                    // It's a term name, find by name
                    $lang_term = get_term_by('name', trim($lang_value), 'sit-language');
                    if ($lang_term) {
                        $language_terms[] = $lang_term->term_id;
                    }
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

        // Always enforce Turkey and North Cyprus restriction
        $allowed_countries = array();
        
        // Get Turkey and North Cyprus term IDs
        $turkey_term = get_term_by('name', 'Turkey', 'sit-country');
        $north_cyprus_term = get_term_by('name', 'North Cyprus', 'sit-country');
        
        if ($turkey_term) {
            $allowed_countries[] = $turkey_term->term_id;
        }
        if ($north_cyprus_term) {
            $allowed_countries[] = $north_cyprus_term->term_id;
        }
        
        // If user selected a specific country, use it only if it's Turkey or North Cyprus
        if (!empty($country)) {
            if (in_array($country, $allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $country,
                );
                $term = get_term($country);
                $country_name = $term->name;
            } else {
                // If selected country is not Turkey/North Cyprus, default to both
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        } else {
            // No specific country selected, default to Turkey and North Cyprus only
            if (!empty($allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        }

        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
            $term = get_term($speciality);
            $speciality_name=$term->name;
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
        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        );
        if (!empty($degree) || !empty($country) || !empty($speciality)) {
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
        $query = new \WP_Query($args);
        $programs = $query->get_posts();
        $zh_university_values = array();
        $filtered_university_ids = array();
        if (!empty($programs)) {
            foreach ($programs as $program) {
                $value = get_post_meta($program->ID, 'zh_university', true);
                if (!empty($value)) {
                    $zh_university_values[] = $value;
                }
            }
            $zh_university_values = array_unique($zh_university_values);
        }
        if (!empty($zh_university_values)) {
            foreach ($zh_university_values as $university_id) {
                $sector_value = get_post_meta($university_id, 'Sector', true);
                if ($sector_value === $type) {
                    $filtered_university_ids[] = $university_id;
                }
            }

            $filtered_university_ids = array_unique($filtered_university_ids);
        }
        return $filtered_university_ids;
    }

    public function get_uni__search_ids(){

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        // Handle both single level and multiple levels (level[])
        $degree = '';
        if (isset($_GET['level']) && $_GET['level'] != 0) {
            if (is_array($_GET['level'])) {
                $degree = array_map('intval', $_GET['level']);
            } else {
                $degree = intval($_GET['level']);
            }
        }
        $country = isset($_GET['country']) && $_GET['country'] != 0 ? intval($_GET['country']) : '';
        $speciality = isset($_GET['speciality']) && $_GET['speciality'] != 0 ? intval($_GET['speciality']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''; // Sanitize input
        $type = isset($_GET['univerity-type']) && $_GET['univerity-type'] != 0 ? $_GET['univerity-type'] : '';

        $feeFilter = $_GET['feeFiter'] ?? '';
        $duration = $_GET['duration'] ?? '';
        $isScholarShip = $_GET['isScholarShip'] ?? '';
        $language = $_GET['language'] ?? '';

        if (!empty($duration)) {
            $duration = explode(' ', $duration)[0];
        }

        $tax_query = array('relation' => 'AND');
        $degree_name='';
        $country_name='';
        $speciality_name='';
        if (!empty($degree)) {
            if (is_array($degree)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                    'operator' => 'IN',
                );
                // Get names of all selected degrees
                $degree_names = array();
                foreach ($degree as $degree_id) {
                    $term = get_term($degree_id);
                    if ($term) {
                        $degree_names[] = $term->name;
                    }
                }
                $degree_name = implode(', ', $degree_names);
            } else {
                $tax_query[] = array(
                    'taxonomy' => 'sit-degree',
                    'field'    => 'term_id',
                    'terms'    => $degree,
                );
                $term = get_term($degree);
                $degree_name = $term ? $term->name : '';
            }
        }

        // Add language filter (supports multiple selections)
        if (!empty($language)) {
            $languages = is_array($language) ? $language : [$language];
            $language_terms = array();
            foreach ($languages as $lang_value) {
                // Check if it's a term ID (numeric) or term name (string)
                if (is_numeric($lang_value)) {
                    // It's a term ID
                    $language_terms[] = intval($lang_value);
                } else {
                    // It's a term name, find by name
                    $lang_term = get_term_by('name', trim($lang_value), 'sit-language');
                    if ($lang_term) {
                        $language_terms[] = $lang_term->term_id;
                    }
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

        // Always enforce Turkey and North Cyprus restriction
        $allowed_countries = array();
        
        // Get Turkey and North Cyprus term IDs
        $turkey_term = get_term_by('name', 'Turkey', 'sit-country');
        $north_cyprus_term = get_term_by('name', 'North Cyprus', 'sit-country');
        
        if ($turkey_term) {
            $allowed_countries[] = $turkey_term->term_id;
        }
        if ($north_cyprus_term) {
            $allowed_countries[] = $north_cyprus_term->term_id;
        }
        
        // If user selected a specific country, use it only if it's Turkey or North Cyprus
        if (!empty($country)) {
            if (in_array($country, $allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $country,
                );
                $term = get_term($country);
                $country_name = $term->name;
            } else {
                // If selected country is not Turkey/North Cyprus, default to both
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        } else {
            // No specific country selected, default to Turkey and North Cyprus only
            if (!empty($allowed_countries)) {
                $tax_query[] = array(
                    'taxonomy' => 'sit-country',
                    'field'    => 'term_id',
                    'terms'    => $allowed_countries,
                    'operator' => 'IN',
                );
            }
        }

        if (!empty($speciality)) {
            $tax_query[] = array(
                'taxonomy' => 'sit-speciality',
                'field'    => 'term_id',
                'terms'    => $speciality,
            );
            $term = get_term($speciality);
            $speciality_name=$term->name;
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
        $args = array(
            'post_type'      => 'sit-program',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
        );
        if (!empty($degree) || !empty($country) || !empty($speciality)) {
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
        $query = new \WP_Query($args);
        $programs = $query->get_posts();
        $zh_university_values = array();
        $filtered_university_ids = array();
        if (!empty($programs)) {
            foreach ($programs as $program) {
                $value = get_post_meta($program->ID, 'zh_university', true);
                if (!empty($value)) {
                    $zh_university_values[] = $value;
                }
            }
            $zh_university_values = array_unique($zh_university_values);
        }
        if (!empty($zh_university_values)) {
            $search_terms = !empty($search) ? array_map('trim', explode(',', $search)) : [];
            $uni_meta_conditions = ['relation' => 'OR'];
            if (!empty($search_terms)) {
                foreach ($search_terms as $term) {
                    $uni_meta_conditions[] = [
                        'key'     => 'Account_Name',
                        'value'   => $term,
                        'compare' => 'LIKE',
                    ];
                }
            }

            $university_query = new \WP_Query(array(
                'post_type'      => 'sit-university',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'post__in'       => $zh_university_values,
                'meta_query'     => array($uni_meta_conditions),
                'fields'         => 'ids',
            ));

            if ($university_query->have_posts()) {
                $filtered_university_ids = $university_query->posts;
            }
            $filtered_university_ids = array_unique($filtered_university_ids);
        }
        return $filtered_university_ids;
    }
}