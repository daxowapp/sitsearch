<?php

class UPD_AjaxHandler {
    public function __construct() {
        add_action('wp_ajax_upd_get_programs_new', [$this, 'handle_request']);
        add_action('wp_ajax_nopriv_upd_get_programs_new', [$this, 'handle_request']);
        add_filter('cron_schedules', [$this, 'custom_cron_intervals']);
        add_action('wp', [$this, 'schedule_university_zoho_cron']);
        add_action('run_university_zoho_cron', [$this, 'handle_university_zoho_ids']);
        add_action('wp_ajax_program_search_ajax', [$this, 'handle_program_ajax']);
        add_action('wp_ajax_nopriv_program_search_ajax', [$this, 'handle_program_ajax']);
        
        // Add the new action for university pagination
        add_action('wp_ajax_load_universities_pagination', [$this, 'handle_university_pagination']);
        add_action('wp_ajax_nopriv_load_universities_pagination', [$this, 'handle_university_pagination']);
    }

    // New method for university pagination
    public function handle_university_pagination() {
        global $wpdb;
        
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'university_search_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        $offset = ($page - 1) * $per_page;
        
        // Get filter parameters
        $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        // Build your database query using the universities_zoho table
        $table = 'universities_zoho';
        $where_conditions = [];
        $params = [];
        
        if($search) {
            $where_conditions[] = "university_title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        if($country) {
            $where_conditions[] = "country = %s";
            $params[] = $country;
        }
        if($city) {
            $where_conditions[] = "city = %s";
            $params[] = $city;
        }
        if($type) {
            $where_conditions[] = "type = %s";
            $params[] = $type;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM {$table} {$where_clause}";
        if (!empty($params)) {
            $total = $wpdb->get_var($wpdb->prepare($count_query, ...$params));
        } else {
            $total = $wpdb->get_var($count_query);
        }
        
        // Get universities for current page
        $universities_query = "SELECT * FROM {$table} {$where_clause} ORDER BY university_title ASC LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        
        $universities = $wpdb->get_results($wpdb->prepare($universities_query, ...$params), ARRAY_A);
        
        // Format the data for the frontend
        $formatted_universities = [];
        foreach($universities as $university) {
            $formatted_universities[] = [
                'id' => $university['zoho_university_id'],
                'name' => $university['university_title'],
                'city' => $university['city'],
                'country' => $university['country'],
                'type' => $university['type'],
                'image' => $university['uni_image'] ?: '/wp-content/uploads/default-university.jpg',
                'logo' => $university['uni_logo'],
                'description' => wp_trim_words($university['description'], 20),
                'url' => $university['uni_url'],
                'ranking' => 'N/A', // Add if you have ranking data
                'students' => 'N/A' // Add if you have student count data
            ];
        }
        
        wp_send_json_success([
            'universities' => $formatted_universities,
            'total' => intval($total),
            'current_page' => $page,
            'per_page' => $per_page
        ]);
    }

    public function handle_program_ajax() {
        global $wpdb;
        check_ajax_referer('program_search_nonce', 'nonce');

        $keyword = sanitize_text_field($_POST['keyword']);
        $table = 'universities_programs';

        $parts = preg_split('/\s+/', $keyword);
        $title_parts = [];
        $fee = '';
        $level = '';
        $language = '';

        $known_levels = ['bachelor', 'master', 'phd', 'diploma', 'associate'];
        $known_languages = ['english', 'turkish', 'french', 'german', 'arabic']; // Add all possible options

        foreach ($parts as $part) {
            $cleaned_part = str_replace(',', '', $part);
            $lower_part = strtolower($part);

            if (is_numeric($cleaned_part)) {
                $fee = $cleaned_part;
            } elseif (in_array($lower_part, $known_levels)) {
                $level = $lower_part;
            } elseif (in_array($lower_part, $known_languages)) {
                $language = $lower_part;
            } else {
                $title_parts[] = $part;
            }
        }

        $program_name = implode(' ', $title_parts);

        $query = "SELECT * FROM {$table} WHERE 1=1";
        $params = [];

        if (!empty($program_name)) {
            $query .= " AND program_title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($program_name) . '%';
        }

        if (!empty($fee)) {
            $query .= " AND (REPLACE(fee, ',', '') = %d OR REPLACE(discounted_fee, ',', '') = %d)";
            $params[] = $fee;
            $params[] = $fee;
        }

        if (!empty($level)) {
            $query .= " AND level LIKE %s";
            $params[] = '%' . $wpdb->esc_like($level) . '%';
        }

        if (!empty($language)) {
            $query .= " AND language LIKE %s";
            $params[] = '%' . $wpdb->esc_like($language) . '%';
        }

        if (!empty($params)) {
            $results = $wpdb->get_results($wpdb->prepare($query, ...$params));
        } else {
            $results = $wpdb->get_results($query);
        }

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'url'        => $row->url,
                'title'      => $row->program_title,
                'university' => $row->uni_title,
                'language'   => $row->language,
                'tuition'    => $row->fee,
                'discounted' => $row->discounted_fee,
                'period'     => $row->duration,
                'level'      => $row->level,
            ];
        }

        wp_send_json($data);
    }

    
    
    public function custom_cron_intervals($schedules) {
        $schedules['every_2_hours'] = array(
            'interval' => 2 * HOUR_IN_SECONDS,
            'display'  => __('Every 2 Hours')
        );
        return $schedules;
    }

    public function schedule_university_zoho_cron() {
        if (!wp_next_scheduled('run_university_zoho_cron')) {
            wp_schedule_event(time(), 'every_2_hours', 'run_university_zoho_cron');
        }
    }

    public function handle_university_zoho_ids() {
        global $wpdb;
        $table_name = 'universities_programs';
        $query = new WP_Query(array(
            'post_type'      => 'universities',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'university_zoho_id',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'university_zoho_id',
                    'value'   => '',
                    'compare' => '!='
                )
            )
        ));

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $zoho_id = get_field('university_zoho_id', $post_id);
                $api_url = 'https://search.studyinturkiye.com/wp-json/sit-search/v1/programs?zoho_university_id=' . $zoho_id;
                $response = wp_remote_get($api_url);
                if (is_wp_error($response)) continue;

                $body = wp_remote_retrieve_body($response);
                $programs = json_decode($body, true);
                if (empty($programs) || !is_array($programs)) continue;
                $wpdb->delete($table_name, ['zoho_university_id' => $zoho_id]);
                foreach ($programs['Programs'] as $program) {
                    $wpdb->insert($table_name, [
                        'id'                 => $program['id'],
                        'url'                => $program['url'],
                        'uni_title'         => $program['university_title'],
                        'zoho_university_id'=> $program['zoho_university_id'],
                        'program_title'     => $program['title'],
                        'content'           => $program['content'],
                        'duration'          => $program['duration'],
                        'fee'               => $program['fee'],
                        'discounted_fee'    => $program['discounted_fee'],
                        'Advanced_Discount' => $program['Advanced_Discount'],
                        'level'             => $program['level'],
                        'language'          => $program['language'],
                        'speciality'        => $program['speciality'],
                    ]);
                }
                $unitable_name = 'universities_zoho';

                $wpdb->delete($unitable_name, ['zoho_university_id' => $zoho_id]);

                $wpdb->insert($unitable_name, [
                    'uni_url'                => $programs['University']['url'],
                    'university_title'         => $programs['University']['title'],
                    'zoho_university_id'=> $programs['University']['zoho_university_id'],
                    'country'     => $programs['University']['country'],
                    'city'           => $programs['University']['city'],
                    'description'          => $programs['University']['uni_description'],
                    'uni_image'               => $programs['University']['image_url'],
                    'uni_logo'    => $programs['University']['uni_logo'],
                    'type'     => $programs['University']['type'],
                ]);
                update_post_meta($post_id, 'cron_done', '1');
            }
            wp_reset_postdata();
        }
    }
    
    public function handle_request() {
        global $wpdb;
    
        $post_id = intval($_POST['post_id']);
        $page = max(1, intval($_POST['page']));
        $title = get_the_title($post_id);
        $per_page = 5;
        $offset = ($page - 1) * $per_page;
    
        $zoho_id = get_field('university_zoho_id', $post_id);
        if (!$zoho_id) {
            echo '<p>No Zoho University ID found.</p>';
            wp_die();
        }
    
        $table = 'universities_programs';
    
        $level   = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : '';
        $search  = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    
        $where = "WHERE zoho_university_id = %s";
        $params = [$zoho_id];
    
        if (!empty($level) && $level !== 'All') {
            $where .= " AND level = %s";
            $params[] = $level;
        }
    
        if (!empty($search)) {
            $where .= " AND program_title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
    
        $count_sql = "SELECT COUNT(*) FROM $table $where";
        $total = $wpdb->get_var($wpdb->prepare($count_sql, ...$params));
    
        $data_sql = "SELECT * FROM $table $where LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
    
        $programs = $wpdb->get_results($wpdb->prepare($data_sql, ...$params), ARRAY_A);
    
        if (!$programs) {
            echo '<p>No programs found.</p>';
            wp_die();
        }
    
        echo '<div class="university-programs">';
        foreach ($programs as $program) {
            if(!empty($program['discounted_fee'])){
                $feedis='<strong>Fee:</strong> $' . esc_html($program['fee']) . ' | Discounted: $' . esc_html($program['discounted_fee']);
            }
            else{
                $feedis='<strong>Fee:</strong> $' . esc_html($program['fee']);
            }
            
            echo '<div class="upd-program-item">';
            echo '<a href="' . esc_url($program['url']) . '"><h3>' . esc_html($program['program_title']) . '</h3></a>';
            echo '<p><strong>Education Period:</strong> ' . esc_html($program['duration']) . ' years</p>';
            echo '<p><strong>'.esc_html($title).':</strong> ' . esc_html($program['level']) . '</p>';
            echo '<p>'. $feedis.'</p>';
            echo '<p><strong>Language:</strong> ' . esc_html($program['language']) . '</p>';
            echo '<p><a href="https://search.studyinturkiye.com/apply/?prog_id=' . esc_attr($program['id']) . '" target="_blank">Apply Now</a></p>';
            echo '</div><hr>';
        }
        echo '</div>';
    
        $total_pages = ceil($total / $per_page);
        if ($total_pages > 1) {
            echo '<div class="programs-pagination">';
        
            // Previous button
            if ($page > 1) {
                echo '<a href="#" class="prev" data-page="' . ($page - 1) . '">« Prev</a> ';
            }
        
            $range = 2;
            $ellipsis_added = false;
        
            for ($i = 1; $i <= $total_pages; $i++) {
                if (
                    $i == 1 || 
                    $i == $total_pages || 
                    ($i >= $page - $range && $i <= $page + $range)
                ) {
                    $class = ($i == $page) ? 'current' : '';
                    echo '<a href="#" data-page="' . $i . '" class="' . $class . '">' . $i . '</a> ';
                    $ellipsis_added = false;
                } else {
                    if (!$ellipsis_added) {
                        echo '<span class="dots">...</span> ';
                        $ellipsis_added = true;
                    }
                }
            }
        
            // Next button
            if ($page < $total_pages) {
                echo '<a href="#" class="next" data-page="' . ($page + 1) . '">Next »</a>';
            }
        
            echo '</div>';
        }
    
        wp_die();
    } 

    public function generate_pagination($total_pages, $current_page) {
        $pagination = '';
        $range = 2;
        $dots = false;
    
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == 1 || $i == $total_pages || ($i >= $current_page - $range && $i <= $current_page + $range)) {
                $active = ($i == $current_page) ? 'current' : '';
                $pagination .= "<a href='#' data-page='$i' class='$active'>$i</a>";
                $dots = true;
            } elseif ($dots) {
                $pagination .= "<a class='dots'>...</a>";
                $dots = false;
            }
        }
    
        return $pagination;
    }
}