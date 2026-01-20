<?php
/**
 * SIT Data Access Layer
 * 
 * Handles all database queries and data retrieval for programs.
 * Provides caching and filtering capabilities.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SIT_DAL {
    
    /**
     * Cache duration in seconds
     */
    private $cache_duration;
    
    /**
     * Program meta keys mapping
     */
    private $meta_keys;
    
    /**
     * Constructor
     */
    public function __construct() {
        $general_settings = get_option('sit_recommender_general', array());
        $this->cache_duration = $general_settings['cache_duration'] ?? 3600;
        
        $mapping = get_option('sit_recommender_mapping', array());
        $this->meta_keys = $mapping['program_meta_keys'] ?? array();
    }
    
    /**
     * Get programs based on filters
     * 
     * @param array $filters Filters to apply
     * @param array $args Additional query arguments
     * @return array Array of WP_Post objects
     */
    public function get_programs($filters = array(), $args = array()) {
        // Build cache key
        $cache_key = 'sit_recommender_programs_' . md5(serialize(array($filters, $args)));
        
        // Try to get from cache first
        $cached_programs = get_transient($cache_key);
        if ($cached_programs !== false) {
            return $cached_programs;
        }
        
        // Build WP_Query arguments
        $query_args = $this->build_query_args($filters, $args);
        
        // Execute query
        $query = new WP_Query($query_args);
        $programs = $query->posts;
        
        // Enhance programs with meta data
        $programs = $this->enhance_programs_with_meta($programs);
        
        // Cache the results
        set_transient($cache_key, $programs, $this->cache_duration);
        
        return $programs;
    }
    
    /**
     * Get a single program by ID
     * 
     * @param int $program_id Program post ID
     * @return WP_Post|null Program post object or null
     */
    public function get_program($program_id) {
        $cache_key = 'sit_recommender_program_' . $program_id;
        
        $cached_program = get_transient($cache_key);
        if ($cached_program !== false) {
            return $cached_program;
        }
        
        $program = get_post($program_id);
        
        if ($program && $program->post_type === 'sit_program') {
            $program = $this->enhance_programs_with_meta(array($program));
            $program = !empty($program) ? $program[0] : null;
            
            if ($program) {
                set_transient($cache_key, $program, $this->cache_duration);
            }
        }
        
        return $program;
    }
    
    /**
     * Search programs by text query
     * 
     * @param string $search_query Search query
     * @param array $filters Additional filters
     * @param array $args Query arguments
     * @return array Array of programs
     */
    public function search_programs($search_query, $filters = array(), $args = array()) {
        $cache_key = 'sit_recommender_search_' . md5($search_query . serialize($filters) . serialize($args));
        
        $cached_results = get_transient($cache_key);
        if ($cached_results !== false) {
            return $cached_results;
        }
        
        // Add search to filters
        $filters['search'] = $search_query;
        
        $programs = $this->get_programs($filters, $args);
        
        // Cache search results for shorter duration
        set_transient($cache_key, $programs, $this->cache_duration / 2);
        
        return $programs;
    }
    
    /**
     * Get programs by school/department
     * 
     * @param string $school School name or slug
     * @param array $additional_filters Additional filters
     * @return array Array of programs
     */
    public function get_programs_by_school($school, $additional_filters = array()) {
        $filters = array_merge($additional_filters, array(
            'school' => $school
        ));
        
        return $this->get_programs($filters);
    }
    
    /**
     * Get programs by level (undergraduate, postgraduate, etc.)
     * 
     * @param string $level Program level
     * @param array $additional_filters Additional filters
     * @return array Array of programs
     */
    public function get_programs_by_level($level, $additional_filters = array()) {
        $filters = array_merge($additional_filters, array(
            'level' => $level
        ));
        
        return $this->get_programs($filters);
    }
    
    /**
     * Get programs by study mode (full-time, part-time, etc.)
     * 
     * @param string $mode Study mode
     * @param array $additional_filters Additional filters
     * @return array Array of programs
     */
    public function get_programs_by_mode($mode, $additional_filters = array()) {
        $filters = array_merge($additional_filters, array(
            'mode' => $mode
        ));
        
        return $this->get_programs($filters);
    }
    
    /**
     * Get available filter options
     * 
     * @param string $filter_type Type of filter (school, level, mode, etc.)
     * @return array Available options
     */
    public function get_filter_options($filter_type) {
        $cache_key = 'sit_recommender_filter_options_' . $filter_type;
        
        $cached_options = get_transient($cache_key);
        if ($cached_options !== false) {
            return $cached_options;
        }
        
        $options = array();
        
        switch ($filter_type) {
            case 'school':
                $options = $this->get_meta_values($this->meta_keys['school'] ?? 'sit_program_school');
                break;
            
            case 'level':
                $options = $this->get_meta_values($this->meta_keys['level'] ?? 'sit_program_level');
                break;
            
            case 'mode':
                $options = $this->get_meta_values($this->meta_keys['mode'] ?? 'sit_program_mode');
                break;
            
            case 'duration':
                $options = $this->get_meta_values($this->meta_keys['duration'] ?? 'sit_program_duration');
                break;
            
            case 'intake':
                $options = $this->get_meta_values($this->meta_keys['intake'] ?? 'sit_program_intake');
                break;
            
            default:
                $options = array();
        }
        
        // Cache for longer duration since filter options don't change often
        set_transient($cache_key, $options, $this->cache_duration * 2);
        
        return $options;
    }
    
    /**
     * Get program statistics
     * 
     * @return array Statistics about programs
     */
    public function get_program_statistics() {
        $cache_key = 'sit_recommender_program_stats';
        
        $cached_stats = get_transient($cache_key);
        if ($cached_stats !== false) {
            return $cached_stats;
        }
        
        $stats = array(
            'total_programs' => $this->count_programs(),
            'by_school' => $this->count_programs_by_meta($this->meta_keys['school'] ?? 'sit_program_school'),
            'by_level' => $this->count_programs_by_meta($this->meta_keys['level'] ?? 'sit_program_level'),
            'by_mode' => $this->count_programs_by_meta($this->meta_keys['mode'] ?? 'sit_program_mode'),
            'last_updated' => current_time('mysql')
        );
        
        set_transient($cache_key, $stats, $this->cache_duration);
        
        return $stats;
    }
    
    /**
     * Clear all program caches
     */
    public function clear_cache() {
        global $wpdb;
        
        // Delete all transients related to SIT recommender
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sit_recommender_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_sit_recommender_%'");
    }
    
    /**
     * Build WP_Query arguments from filters
     * 
     * @param array $filters Filters to apply
     * @param array $args Additional arguments
     * @return array WP_Query arguments
     */
    private function build_query_args($filters = array(), $args = array()) {
        $default_args = array(
            'post_type' => 'sit_program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        $query_args = array_merge($default_args, $args);
        
        // Handle search
        if (!empty($filters['search'])) {
            $query_args['s'] = sanitize_text_field($filters['search']);
        }
        
        // Handle meta filters
        $meta_filters = array('school', 'level', 'mode', 'duration', 'intake');
        foreach ($meta_filters as $filter) {
            if (!empty($filters[$filter])) {
                $meta_key = $this->meta_keys[$filter] ?? 'sit_program_' . $filter;
                $meta_value = $filters[$filter];
                
                // Handle multiple values (comma-separated)
                if (strpos($meta_value, ',') !== false) {
                    $values = array_map('trim', explode(',', $meta_value));
                    $query_args['meta_query'][] = array(
                        'key' => $meta_key,
                        'value' => $values,
                        'compare' => 'IN'
                    );
                } else {
                    $query_args['meta_query'][] = array(
                        'key' => $meta_key,
                        'value' => $meta_value,
                        'compare' => 'LIKE'
                    );
                }
            }
        }
        
        // Handle numeric filters (fees, etc.)
        if (!empty($filters['max_fees'])) {
            $query_args['meta_query'][] = array(
                'key' => $this->meta_keys['fees'] ?? 'sit_program_fees',
                'value' => intval($filters['max_fees']),
                'type' => 'NUMERIC',
                'compare' => '<='
            );
        }
        
        if (!empty($filters['min_fees'])) {
            $query_args['meta_query'][] = array(
                'key' => $this->meta_keys['fees'] ?? 'sit_program_fees',
                'value' => intval($filters['min_fees']),
                'type' => 'NUMERIC',
                'compare' => '>='
            );
        }
        
        // Set meta_query relation if multiple meta queries
        if (count($query_args['meta_query']) > 1) {
            $query_args['meta_query']['relation'] = 'AND';
        }
        
        // Handle taxonomy filters (if any)
        if (!empty($filters['category'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'sit_program_category',
                'field' => 'slug',
                'terms' => $filters['category']
            );
        }
        
        // Handle ordering
        if (!empty($filters['orderby'])) {
            $query_args['orderby'] = $filters['orderby'];
            $query_args['order'] = !empty($filters['order']) ? $filters['order'] : 'ASC';
        }
        
        // Handle pagination
        if (!empty($filters['posts_per_page'])) {
            $query_args['posts_per_page'] = intval($filters['posts_per_page']);
        }
        
        if (!empty($filters['paged'])) {
            $query_args['paged'] = intval($filters['paged']);
        }
        
        return $query_args;
    }
    
    /**
     * Enhance programs with meta data
     * 
     * @param array $programs Array of WP_Post objects
     * @return array Enhanced programs
     */
    private function enhance_programs_with_meta($programs) {
        if (empty($programs)) {
            return $programs;
        }
        
        foreach ($programs as &$program) {
            // Add all meta data
            $program->meta = array();
            foreach ($this->meta_keys as $key => $meta_key) {
                $program->meta[$key] = get_post_meta($program->ID, $meta_key, true);
            }
            
            // Add featured image
            $program->featured_image = get_the_post_thumbnail_url($program->ID, 'medium');
            
            // Add excerpt if not present
            if (empty($program->post_excerpt)) {
                $program->post_excerpt = wp_trim_words($program->post_content, 30);
            }
            
            // Add permalink
            $program->permalink = get_permalink($program->ID);
        }
        
        return $programs;
    }
    
    /**
     * Get unique meta values for a specific meta key
     * 
     * @param string $meta_key Meta key to query
     * @return array Unique values
     */
    private function get_meta_values($meta_key) {
        global $wpdb;
        
        $values = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = %s 
            AND p.post_type = 'sit_program'
            AND p.post_status = 'publish'
            AND pm.meta_value != ''
            ORDER BY pm.meta_value ASC
        ", $meta_key));
        
        // Handle comma-separated values
        $unique_values = array();
        foreach ($values as $value) {
            if (strpos($value, ',') !== false) {
                $sub_values = array_map('trim', explode(',', $value));
                $unique_values = array_merge($unique_values, $sub_values);
            } else {
                $unique_values[] = trim($value);
            }
        }
        
        return array_unique(array_filter($unique_values));
    }
    
    /**
     * Count total programs
     * 
     * @return int Total program count
     */
    private function count_programs() {
        $count_query = new WP_Query(array(
            'post_type' => 'sit_program',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        return $count_query->found_posts;
    }
    
    /**
     * Count programs by meta value
     * 
     * @param string $meta_key Meta key to count by
     * @return array Counts by meta value
     */
    private function count_programs_by_meta($meta_key) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT pm.meta_value, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = %s 
            AND p.post_type = 'sit_program'
            AND p.post_status = 'publish'
            AND pm.meta_value != ''
            GROUP BY pm.meta_value
            ORDER BY count DESC
        ", $meta_key));
        
        $counts = array();
        foreach ($results as $result) {
            $counts[$result->meta_value] = intval($result->count);
        }
        
        return $counts;
    }
}

// Hook to clear cache when programs are updated
add_action('save_post', function($post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'sit_program') {
        $dal = new SIT_DAL();
        $dal->clear_cache();
    }
});

add_action('delete_post', function($post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'sit_program') {
        $dal = new SIT_DAL();
        $dal->clear_cache();
    }
});
