<?php

/**
 * Add this to your main plugin file or create a new file in src/Shortcodes/
 */

class UniversityGridShortcode {
    
    public function __construct() {
        add_shortcode('university_grid', [$this, 'render_university_grid']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_filter_universities', [$this, 'ajax_filter_universities']);
        add_action('wp_ajax_nopriv_filter_universities', [$this, 'ajax_filter_universities']);
    }

    public function enqueue_scripts() {
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
            'nonce' => wp_create_nonce('university_filter_nonce')
        ]);
    }

    public function render_university_grid($atts) {
        $atts = shortcode_atts([
            'posts_per_page' => 12,
            'show_filters' => 'true',
            'columns' => 3,
            'show_country' => 'true',
            'show_type' => 'true',
            'show_city' => 'true'
        ], $atts);

        ob_start();
        
        // Get filter options
        $countries = $this->get_filter_options('country');
        $types = $this->get_filter_options('type');
        $cities = $this->get_filter_options('city');
        
        // Get initial universities
        $universities = $this->get_universities([
            'posts_per_page' => $atts['posts_per_page']
        ]);

        ?>
        <div class="university-grid-container" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            
            <?php if ($atts['show_filters'] === 'true'): ?>
            <div class="university-filters">
                <div class="filter-row">
                    
                    <?php if ($atts['show_country'] === 'true' && !empty($countries)): ?>
                    <div class="filter-group">
                        <label for="country-filter"><?php _e('Country', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                        <select id="country-filter" name="country">
                            <option value=""><?php _e('All Countries', SIT_SEARCH_TEXT_DOMAIN); ?></option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?php echo esc_attr($country); ?>"><?php echo esc_html($country); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($atts['show_type'] === 'true' && !empty($types)): ?>
                    <div class="filter-group">
                        <label for="type-filter"><?php _e('University Type', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                        <select id="type-filter" name="type">
                            <option value=""><?php _e('All Types', SIT_SEARCH_TEXT_DOMAIN); ?></option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if ($atts['show_city'] === 'true' && !empty($cities)): ?>
                    <div class="filter-group">
                        <label for="city-filter"><?php _e('City', SIT_SEARCH_TEXT_DOMAIN); ?></label>
                        <select id="city-filter" name="city">
                            <option value=""><?php _e('All Cities', SIT_SEARCH_TEXT_DOMAIN); ?></option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo esc_attr($city); ?>"><?php echo esc_html($city); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="filter-group">
                        <button type="button" id="apply-filters" class="btn btn-primary">
                            <?php _e('Apply Filters', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </button>
                        <button type="button" id="reset-filters" class="btn btn-secondary">
                            <?php _e('Reset', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="university-grid-loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p><?php _e('Loading universities...', SIT_SEARCH_TEXT_DOMAIN); ?></p>
            </div>

            <div class="university-grid columns-<?php echo esc_attr($atts['columns']); ?>" id="university-grid">
                <?php $this->render_university_items($universities); ?>
            </div>

            <div class="university-grid-pagination" id="university-pagination">
                <!-- Pagination will be loaded via AJAX -->
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    private function get_filter_options($field) {
        global $wpdb;
        
        $meta_key_map = [
            'country' => 'Country', // Adjust these meta keys based on your actual field names
            'type' => 'University_Type',
            'city' => 'City'
        ];

        if (!isset($meta_key_map[$field])) {
            return [];
        }

        $meta_key = $meta_key_map[$field];
        
        $results = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s 
            AND pm.meta_value != ''
            AND p.post_type = 'sit-university'
            AND p.post_status = 'publish'
            ORDER BY pm.meta_value ASC
        ", $meta_key));

        return array_filter($results);
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

        // Handle meta query for filters
        if (!empty($args['filters'])) {
            $meta_query = ['relation' => 'AND'];
            
            foreach ($args['filters'] as $key => $value) {
                if (!empty($value)) {
                    $meta_key_map = [
                        'country' => 'Country',
                        'type' => 'University_Type',
                        'city' => 'City'
                    ];
                    
                    if (isset($meta_key_map[$key])) {
                        $meta_query[] = [
                            'key' => $meta_key_map[$key],
                            'value' => $value,
                            'compare' => '='
                        ];
                    }
                }
            }
            
            if (count($meta_query) > 1) {
                $args['meta_query'] = $meta_query;
            }
            
            unset($args['filters']);
        }

        return new WP_Query($args);
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
            echo '<p>' . __('No universities found matching your criteria.', SIT_SEARCH_TEXT_DOMAIN) . '</p>';
            echo '</div>';
        }
    }

    private function render_university_card($university_id) {
        $university = get_post($university_id);
        $country = get_post_meta($university_id, 'Country', true);
        $city = get_post_meta($university_id, 'City', true);
        $type = get_post_meta($university_id, 'University_Type', true);
        $website = get_post_meta($university_id, 'Website', true);
        $logo = get_post_meta($university_id, 'Logo_URL', true);
        
        ?>
        <div class="university-card" data-university-id="<?php echo esc_attr($university_id); ?>">
            <div class="university-card-inner">
                
                <?php if ($logo): ?>
                <div class="university-logo">
                    <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($university->post_title); ?>" loading="lazy">
                </div>
                <?php endif; ?>

                <div class="university-content">
                    <h3 class="university-title">
                        <a href="<?php echo get_permalink($university_id); ?>">
                            <?php echo esc_html($university->post_title); ?>
                        </a>
                    </h3>

                    <div class="university-meta">
                        <?php if ($country): ?>
                        <div class="university-country">
                            <span class="meta-label"><?php _e('Country:', SIT_SEARCH_TEXT_DOMAIN); ?></span>
                            <span class="meta-value"><?php echo esc_html($country); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($city): ?>
                        <div class="university-city">
                            <span class="meta-label"><?php _e('City:', SIT_SEARCH_TEXT_DOMAIN); ?></span>
                            <span class="meta-value"><?php echo esc_html($city); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($type): ?>
                        <div class="university-type">
                            <span class="meta-label"><?php _e('Type:', SIT_SEARCH_TEXT_DOMAIN); ?></span>
                            <span class="meta-value"><?php echo esc_html($type); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($university->post_excerpt): ?>
                    <div class="university-excerpt">
                        <?php echo wp_trim_words($university->post_excerpt, 20, '...'); ?>
                    </div>
                    <?php endif; ?>

                    <div class="university-actions">
                        <a href="<?php echo get_permalink($university_id); ?>" class="btn btn-primary">
                            <?php _e('View Programs', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </a>
                        
                        <?php if ($website): ?>
                        <a href="<?php echo esc_url($website); ?>" class="btn btn-secondary" target="_blank" rel="noopener">
                            <?php _e('Visit Website', SIT_SEARCH_TEXT_DOMAIN); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function ajax_filter_universities() {
        if (!wp_verify_nonce($_POST['nonce'], 'university_filter_nonce')) {
            wp_die('Security check failed');
        }

        $filters = [
            'country' => sanitize_text_field($_POST['country'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? ''),
            'city' => sanitize_text_field($_POST['city'] ?? '')
        ];

        $paged = intval($_POST['paged'] ?? 1);
        $posts_per_page = intval($_POST['posts_per_page'] ?? 12);

        $args = [
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'filters' => array_filter($filters) // Remove empty filters
        ];

        $query = $this->get_universities($args);

        ob_start();
        $this->render_university_items($query);
        $html = ob_get_clean();

        // Generate pagination
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
                'prev_text' => __('« Previous', SIT_SEARCH_TEXT_DOMAIN),
                'next_text' => __('Next »', SIT_SEARCH_TEXT_DOMAIN),
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
}

// Initialize the shortcode
new UniversityGridShortcode();