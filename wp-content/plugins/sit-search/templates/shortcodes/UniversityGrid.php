<?php

namespace SIT\Search\Shortcodes;

class UniversityGrid {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_filter_universities', [$this, 'ajax_filter_universities']);
        add_action('wp_ajax_nopriv_filter_universities', [$this, 'ajax_filter_universities']);
        add_action('wp_ajax_get_cities_by_country', [$this, 'ajax_get_cities_by_country']);
        add_action('wp_ajax_nopriv_get_cities_by_country', [$this, 'ajax_get_cities_by_country']);
    }

    public function __invoke($atts) {
        return $this->render_university_grid($atts);
    }

    public function enqueue_scripts() {
        if (is_admin()) return;

        wp_enqueue_script(
            'university-grid-js',
            SIT_SEARCH_ASSETS . 'js/university-grid.js',
            ['jquery'],
            STI_SEARCH_VERSION,
            true
        );
        
        wp_enqueue_style(
            'university-grid-css',
            SIT_SEARCH_ASSETS . 'css/university-grid.css',
            [],
            STI_SEARCH_VERSION
        );

        wp_localize_script('university-grid-js', 'university_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('university_filter_nonce'),
            'text_domain' => SIT_SEARCH_TEXT_DOMAIN
        ]);
    }

    public function render_university_grid($atts) {
        $atts = shortcode_atts([
            'posts_per_page' => 12,
            'show_filters' => 'true',
            'show_search' => 'true',
            'columns' => 3,
            'show_country' => 'true',
            'show_sector' => 'true', 
            'show_city' => 'true',
            'orderby' => 'title',
            'order' => 'ASC',
            'country' => '',
            'sector' => '',
            'city' => '',
            'search' => '',
            'debug' => 'false'
        ], $atts, 'university_grid');

        $this->enqueue_scripts();
        ob_start();
        
        // Get filter options from database
        $countries = $this->get_countries_from_db();
        $sectors = $this->get_sectors_from_db();
        $cities = $this->get_cities_from_db();
        
        $initial_filters = array_filter([
            'country' => $atts['country'],
            'sector' => $atts['sector'], 
            'city' => $atts['city'],
            'search' => $atts['search']
        ]);

        $universities = $this->get_universities([
            'posts_per_page' => intval($atts['posts_per_page']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'filters' => $initial_filters
        ]);

        ?>
        <div class="university-grid-container" 
             data-columns="<?php echo esc_attr($atts['columns']); ?>"
             data-posts_per_page="<?php echo esc_attr($atts['posts_per_page']); ?>">
            
            <?php if ($atts['show_filters'] === 'true'): ?>
            <div class="university-filters">
                <?php if ($atts['show_search'] === 'true'): ?>
                <div class="search-row">
                    <div class="search-group">
                        <label for="university-search">Search Universities</label>
                        <input type="text" id="university-search" name="search" placeholder="Search by university name..." value="<?php echo esc_attr($atts['search']); ?>">
                        <button type="button" id="search-universities" class="btn btn-primary">Search</button>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="filter-row">
                    
                    <?php if ($atts['show_country'] === 'true'): ?>
                    <div class="filter-group">
                        <label for="country-filter">Country</label>
                        <select id="country-filter" name="country">
                            <option value="">All Countries</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?php echo esc_attr($country); ?>" <?php selected($atts['country'], $country); ?>>
                                    <?php echo esc_html($country); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($atts['show_sector'] === 'true'): ?>
                    <div class="filter-group">
                        <label for="sector-filter">Sector</label>
                        <select id="sector-filter" name="sector">
                            <option value="">All Sectors</option>
                            <?php foreach ($sectors as $sector): ?>
                                <option value="<?php echo esc_attr($sector); ?>" <?php selected($atts['sector'], $sector); ?>>
                                    <?php echo esc_html($sector); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($atts['show_city'] === 'true'): ?>
                    <div class="filter-group">
                        <label for="city-filter">City</label>
                        <select id="city-filter" name="city">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo esc_attr($city); ?>" <?php selected($atts['city'], $city); ?>>
                                    <?php echo esc_html($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="filter-group filter-actions">
                        <label>&nbsp;</label>
                        <div class="button-group">
                            <button type="button" id="apply-filters" class="btn btn-primary">Apply Filters</button>
                            <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                        </div>
                    </div>
                </div>

                <?php if ($atts['debug'] === 'false'): ?>
                <div class="debug-info" style="margin-top: 15px; padding: 15px; background: #f0f0f0; border-radius: 5px; font-size: 12px;">
                    <strong>Debug Info:</strong><br>
                    Total Universities: <?php echo $universities->found_posts; ?><br>
                    Countries from DB: <?php echo count($countries); ?> (<?php echo implode(', ', array_slice($countries, 0, 5)); ?>)<br>
                    Sectors from DB: <?php echo count($sectors); ?> (<?php echo implode(', ', $sectors); ?>)<br>
                    Cities from DB: <?php echo count($cities); ?> (<?php echo implode(', ', array_slice($cities, 0, 5)); ?>)<br>
                    
                    <?php if (!empty($initial_filters)): ?>
                        Active Filters: <?php echo json_encode($initial_filters); ?><br>
                    <?php endif; ?>
                    
                    <?php
                    // Test each field to see what data exists
                    global $wpdb;
                    $field_tests = [
                        '_University_Country' => $wpdb->get_var("SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_University_Country' AND meta_value != '' AND meta_value != '0' AND meta_value != 'university_country'"),
                        'University_Country' => $wpdb->get_var("SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'University_Country' AND meta_value != '' AND meta_value != '0' AND meta_value != 'university_country'"),
                        '_University_City' => $wpdb->get_var("SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_University_City' AND meta_value != '' AND meta_value != '0' AND meta_value != 'university_city'"),
                        'University_City' => $wpdb->get_var("SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'University_City' AND meta_value != '' AND meta_value != '0' AND meta_value != 'university_city'"),
                        '_Sector' => $wpdb->get_var("SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_Sector' AND meta_value != '' AND meta_value != 'sector'"),
                        'Sector' => $wpdb->get_var("SELECT COUNT(DISTINCT meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'Sector' AND meta_value != '' AND meta_value != 'sector'")
                    ];
                    
                    echo "Field Tests: ";
                    foreach ($field_tests as $field => $count) {
                        echo "{$field}={$count} ";
                    }
                    
                    // Check taxonomies
                    echo "<br>Taxonomies: ";
                    echo "sit-country=" . (taxonomy_exists('sit-country') ? 'YES' : 'NO') . " ";
                    echo "sit-city=" . (taxonomy_exists('sit-city') ? 'YES' : 'NO') . " ";
                    
                    if (taxonomy_exists('sit-country')) {
                        $country_terms = get_terms(['taxonomy' => 'sit-country', 'hide_empty' => false]);
                        echo "sit-country-terms=" . (is_wp_error($country_terms) ? 0 : count($country_terms)) . " ";
                    }
                    
                    if (taxonomy_exists('sit-city')) {
                        $city_terms = get_terms(['taxonomy' => 'sit-city', 'hide_empty' => false]);
                        echo "sit-city-terms=" . (is_wp_error($city_terms) ? 0 : count($city_terms)) . " ";
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="university-grid-loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p>Loading universities...</p>
            </div>

            <div class="university-grid columns-<?php echo esc_attr($atts['columns']); ?>" id="university-grid">
                <?php $this->render_university_items($universities); ?>
            </div>

            <?php if ($universities->max_num_pages > 1): ?>
            <div class="university-grid-pagination" id="university-pagination">
                <?php 
                echo paginate_links([
                    'total' => $universities->max_num_pages,
                    'current' => 1,
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'prev_next' => true,
                    'prev_text' => '« Previous',
                    'next_text' => 'Next »',
                    'type' => 'plain'
                ]);
                ?>
            </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    private function get_countries_from_db() {
        // Get only Turkey and Northern Cyprus
        $allowed_countries = ['Turkey', 'Northern Cyprus'];
        $countries = [];
        
        $terms = get_terms([
            'taxonomy' => 'sit-country',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        if (!is_wp_error($terms) && !empty($terms)) {
            $all_countries = wp_list_pluck($terms, 'name');
            // Filter to only show Turkey and Northern Cyprus
            $countries = array_intersect($all_countries, $allowed_countries);
        }

        return array_values($countries);
    }

    private function get_cities_from_db($country = '') {
        // Get cities from sit-city taxonomy, optionally filtered by country
        $cities = [];
        
        if (!empty($country)) {
            // Get cities for specific country by finding universities that belong to that country
            global $wpdb;
            
            // Get university IDs that belong to the selected country
            $university_ids = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT tr.object_id 
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                WHERE tt.taxonomy = 'sit-country'
                AND t.name = %s
                AND p.post_type = 'sit-university'
                AND p.post_status = 'publish'
            ", $country));
            
            if (!empty($university_ids)) {
                // Get cities for these universities
                $terms = get_terms([
                    'taxonomy' => 'sit-city',
                    'hide_empty' => false,
                    'object_ids' => $university_ids,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ]);
                
                if (!is_wp_error($terms) && !empty($terms)) {
                    $cities = wp_list_pluck($terms, 'name');
                }
            }
        } else {
            // Get all cities for Turkey and Northern Cyprus universities
            $allowed_countries = ['Turkey', 'Northern Cyprus'];
            $all_university_ids = [];
            
            global $wpdb;
            foreach ($allowed_countries as $country_name) {
                $country_university_ids = $wpdb->get_col($wpdb->prepare("
                    SELECT DISTINCT tr.object_id 
                    FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                    WHERE tt.taxonomy = 'sit-country'
                    AND t.name = %s
                    AND p.post_type = 'sit-university'
                    AND p.post_status = 'publish'
                ", $country_name));
                
                $all_university_ids = array_merge($all_university_ids, $country_university_ids);
            }
            
            if (!empty($all_university_ids)) {
                $terms = get_terms([
                    'taxonomy' => 'sit-city',
                    'hide_empty' => false,
                    'object_ids' => array_unique($all_university_ids),
                    'orderby' => 'name',
                    'order' => 'ASC'
                ]);
                
                if (!is_wp_error($terms) && !empty($terms)) {
                    $cities = wp_list_pluck($terms, 'name');
                }
            }
        }

        return array_values($cities);
    }

    private function get_sectors_from_db() {
        global $wpdb;
        
        // Use only the 'Sector' field (without underscore) since that's where the data is
        $sectors = $wpdb->get_col("
            SELECT DISTINCT pm.meta_value
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'Sector'
            AND pm.meta_value IS NOT NULL
            AND pm.meta_value != ''
            AND pm.meta_value != 'sector'
            AND p.post_type = 'sit-university'
            AND p.post_status = 'publish'
            ORDER BY pm.meta_value
        ");

        return array_filter($sectors);
    }

    private function get_universities($args = []) {
        $default_args = [
            'post_type' => 'sit-university',
            'post_status' => 'publish', 
            'posts_per_page' => 12,
            'paged' => 1,
            'orderby' => 'title',
            'order' => 'ASC'
        ];

        $args = wp_parse_args($args, $default_args);

        // ALWAYS filter to only Turkey and Northern Cyprus universities
        $tax_query = ['relation' => 'AND'];
        $meta_query = ['relation' => 'AND'];
        
        // Filter to only active universities
        $meta_query[] = [
            'key' => 'Active_in_Search',
            'value' => '1',
            'compare' => '='
        ];
        
        // Default country filter - ALWAYS show only Turkey and Northern Cyprus
        $allowed_countries = ['Turkey', 'Northern Cyprus'];
        $tax_query[] = [
            'taxonomy' => 'sit-country',
            'field' => 'name',
            'terms' => $allowed_countries,
            'operator' => 'IN'
        ];

        // Apply additional filters if they exist
        if (!empty($args['filters'])) {
            foreach ($args['filters'] as $key => $value) {
                if (!empty($value)) {
                    if ($key === 'country') {
                        // Replace the default country filter with the specific one
                        // Remove the default filter first
                        array_pop($tax_query);
                        
                        // Add the specific country filter
                        $tax_query[] = [
                            'taxonomy' => 'sit-country',
                            'field' => 'name',
                            'terms' => $value
                        ];
                    } elseif ($key === 'city') {
                        $tax_query[] = [
                            'taxonomy' => 'sit-city',
                            'field' => 'name', 
                            'terms' => $value
                        ];
                    } elseif ($key === 'sector') {
                        // Debug logging
                        error_log('Adding sector meta query for: ' . $value);
                        
                        $meta_query[] = [
                            'key' => 'Sector',
                            'value' => $value,
                            'compare' => '='
                        ];
                    } elseif ($key === 'search') {
                        // Add search functionality - search in title only for better results
                        $args['s'] = $value;
                        
                        // Debug logging for search
                        error_log('Search query applied: ' . $value);
                    }
                }
            }
            
            unset($args['filters']);
        }

        // Apply queries - tax_query will always have at least the country restriction
        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
            error_log('Tax query applied: ' . print_r($tax_query, true));
        } else {
            // Always apply the default country filter even if no other filters
            $args['tax_query'] = $tax_query;
        }
        
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
            error_log('Meta query applied: ' . print_r($meta_query, true));
        }

        $query = new \WP_Query($args);
        error_log('Final WP_Query SQL: ' . $query->request);
        
        return $query;
    }

    private function render_university_items($query) {
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_university_card(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-universities-found">';
            echo '<h3>No universities found matching your criteria.</h3>';
            echo '<p>Please try adjusting your filters or <button id="reset-filters-inline" class="btn btn-secondary">Reset All Filters</button></p>';
            echo '</div>';
        }
    }

    private function render_university_card($university_id) {
        $university = get_post($university_id);
        if (!$university) return;

        // Try to get location data from taxonomies first (like TopUniversities shortcode)
        $country = '';
        $city = '';
        
        // Get country from taxonomy
        $country_terms = get_the_terms($university_id, 'sit-country');
        if (!is_wp_error($country_terms) && !empty($country_terms)) {
            $country = $country_terms[0]->name;
        }
        
        // Get city from taxonomy
        $city_terms = get_the_terms($university_id, 'sit-city');
        if (!is_wp_error($city_terms) && !empty($city_terms)) {
            $city = $city_terms[0]->name;
        }
        
        $sector = get_post_meta($university_id, 'Sector', true);
        $website = get_post_meta($university_id, 'Website', true);
        $logo = get_post_meta($university_id, 'uni_image', true);

        // Build Apply Now URL to the programs page (NEW)
        $apply_url = esc_url( add_query_arg( 'uni-id', (int) $university_id, 'https://search.studyinturkiye.com/university/' ) );

        // Get program count
        $programs_query = new \WP_Query([
            'post_type' => 'sit-program',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                ['key' => 'zh_university', 'value' => $university_id, 'compare' => '=']
            ]
        ]);
        $program_count = $programs_query->found_posts;
        wp_reset_postdata();

        // Clean up placeholder values
        if ($sector === 'sector') $sector = '';

        ?>
        <div class="university-card" data-university-id="<?php echo esc_attr($university_id); ?>">
            <div class="university-card-inner">
                
                <?php if ($logo): ?>
                <div class="university-logo">
                    <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($university->post_title); ?>" loading="lazy">
                </div>
                <?php else: ?>
                <div class="university-logo university-logo-placeholder">
                    <div class="logo-placeholder">
                        <?php echo esc_html(substr($university->post_title, 0, 1)); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="university-content">
                    <h3 class="university-title">
                        <a href="<?php echo get_permalink($university_id); ?>">
                            <?php echo esc_html($university->post_title); ?>
                        </a>
                    </h3>

                    <div class="university-meta">
                        <?php if ($country || $city): ?>
                        <div class="university-location">
                            <span class="location-icon">📍</span>
                            <?php 
                            $location_parts = array_filter([$city, $country]);
                            echo esc_html(implode(', ', $location_parts)); 
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($sector): ?>
                        <div class="university-sector">
                            <span class="sector-icon">🏛️</span>
                            <span class="meta-value"><?php echo esc_html($sector); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($program_count > 0): ?>
                        <div class="university-programs">
                            <span class="programs-icon">📚</span>
                            <span class="meta-value">
                                <?php printf($program_count === 1 ? '%d Program' : '%d Programs', $program_count); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="university-actions">
                        <a href="<?php echo get_permalink($university_id); ?>" class="btn btn-primary">View Details</a>
                        <a href="<?php echo $apply_url; ?>" class="btn btn-secondary" target="_blank" rel="noopener">Apply Now</a>
                        <?php /* kept for reference, now replaced by Apply Now:
                        <?php if ($website): ?>
                        <a href="<?php echo esc_url('https://' . $website); ?>" class="btn btn-secondary" target="_blank" rel="noopener">Visit Website</a>
                        <?php endif; ?>
                        */ ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_filter_universities() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'university_filter_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        $filters = [
            'country' => sanitize_text_field($_POST['country'] ?? ''),
            'sector' => sanitize_text_field($_POST['sector'] ?? ''),
            'city' => sanitize_text_field($_POST['city'] ?? ''),
            'search' => sanitize_text_field($_POST['search'] ?? '')
        ];

        // Debug logging for all filters including search
        error_log('Search filter received: ' . ($filters['search'] ?: 'EMPTY'));
        error_log('Sector filter received: ' . ($filters['sector'] ?: 'EMPTY'));
        error_log('All filters: ' . print_r($filters, true));
        
        // Special handling for search-only queries
        if (!empty($filters['search']) && empty($filters['country']) && empty($filters['sector']) && empty($filters['city'])) {
            error_log('Search-only query detected, removing empty filters');
            $filters = ['search' => $filters['search']];
        }

        $paged = intval($_POST['paged'] ?? 1);
        $posts_per_page = intval($_POST['posts_per_page'] ?? 12);

        $args = [
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'filters' => array_filter($filters)
        ];

        $query = $this->get_universities($args);

        // Debug the actual query
        error_log('WP_Query args: ' . print_r($query->query, true));
        error_log('Found posts: ' . $query->found_posts);

        ob_start();
        $this->render_university_items($query);
        $html = ob_get_clean();

        $pagination = '';
        if ($query->max_num_pages > 1) {
            $pagination = paginate_links([
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'show_all' => false,
                'end_size' => 1,
                'mid_size' => 2,
                'prev_next' => true,
                'prev_text' => '« Previous',
                'next_text' => 'Next »',
                'type' => 'plain'
            ]);
        }

        wp_send_json_success([
            'html' => $html,
            'pagination' => $pagination,
            'found_posts' => $query->found_posts,
            'max_num_pages' => $query->max_num_pages
        ]);
    }

    public function ajax_get_cities_by_country() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'university_filter_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        $country = sanitize_text_field($_POST['country'] ?? '');
        $cities = $this->get_cities_from_db($country);

        wp_send_json_success([
            'cities' => $cities
        ]);
    }
}
