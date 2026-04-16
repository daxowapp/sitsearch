<?php

namespace SIT\Search\Endpoints;

/**
 * Supabase Webhook Sync Endpoint
 * 
 * Receives webhook notifications from Supabase database triggers
 * and syncs data to WordPress custom post types and taxonomies.
 */
class SupabaseSyncEndpoint
{
    private const NAMESPACE = 'sit-search/v1';
    private const ROUTE = '/supabase-sync';
    
    /**
     * Table to WordPress entity mapping
     */
    private const TABLE_MAPPING = [
        'zoho_universities' => [
            'type' => 'post',
            'post_type' => 'sit-university',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_programs' => [
            'type' => 'post',
            'post_type' => 'sit-program',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_campus' => [
            'type' => 'post',
            'post_type' => 'sit-campus',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_countries' => [
            'type' => 'taxonomy',
            'taxonomy' => 'sit-country',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_cities' => [
            'type' => 'taxonomy',
            'taxonomy' => 'sit-city',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_degrees' => [
            'type' => 'taxonomy',
            'taxonomy' => 'sit-degree',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_faculty' => [
            'type' => 'taxonomy',
            'taxonomy' => 'sit-faculty',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_speciality' => [
            'type' => 'taxonomy',
            'taxonomy' => 'sit-speciality',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ],
        'zoho_languages' => [
            'type' => 'taxonomy',
            'taxonomy' => 'sit-language',
            'id_field' => 'id',
            'meta_key' => 'supabase_id'
        ]
    ];
    
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => [$this, 'verify_webhook']
        ]);
        
        // Health check endpoint
        register_rest_route(self::NAMESPACE, '/health', [
            'methods' => 'GET',
            'callback' => function() {
                return new \WP_REST_Response(['status' => 'ok', 'timestamp' => current_time('mysql')], 200);
            },
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * Verify webhook authenticity
     */
    public function verify_webhook(\WP_REST_Request $request): bool
    {
        // Verify the webhook secret if configured
        $webhook_secret = defined('SUPABASE_WEBHOOK_SECRET') ? SUPABASE_WEBHOOK_SECRET : '';
        
        if (!empty($webhook_secret)) {
            $signature = $request->get_header('x-supabase-signature');
            if ($signature !== $webhook_secret) {
                error_log('Supabase webhook signature mismatch');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Handle incoming webhook from Supabase
     */
    public function handle_webhook(\WP_REST_Request $request): \WP_REST_Response
    {
        $payload = $request->get_json_params();
        
        error_log('Supabase webhook received: ' . json_encode($payload));
        
        // Validate payload structure
        if (empty($payload['type']) || empty($payload['table'])) {
            return new \WP_REST_Response([
                'success' => false,
                'error' => 'Invalid payload structure'
            ], 400);
        }
        
        $type = $payload['type']; // INSERT, UPDATE, DELETE
        $table = $payload['table'];
        $record = $payload['record'] ?? [];
        $old_record = $payload['old_record'] ?? [];
        
        // Check if we handle this table
        if (!isset(self::TABLE_MAPPING[$table])) {
            return new \WP_REST_Response([
                'success' => false,
                'error' => "Table '{$table}' not configured for sync"
            ], 400);
        }
        
        $mapping = self::TABLE_MAPPING[$table];
        
        try {
            switch ($type) {
                case 'INSERT':
                    $result = $this->handle_insert($mapping, $record);
                    break;
                case 'UPDATE':
                    $result = $this->handle_update($mapping, $record);
                    break;
                case 'DELETE':
                    $result = $this->handle_delete($mapping, $old_record);
                    break;
                default:
                    return new \WP_REST_Response([
                        'success' => false,
                        'error' => "Unknown event type: {$type}"
                    ], 400);
            }
            
            // Clear any related caches
            $this->clear_related_cache($table);
            
            return new \WP_REST_Response([
                'success' => true,
                'action' => $type,
                'table' => $table,
                'result' => $result
            ], 200);
            
        } catch (\Exception $e) {
            error_log('Supabase sync error: ' . $e->getMessage());
            return new \WP_REST_Response([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle INSERT event
     */
    private function handle_insert(array $mapping, array $record): array
    {
        if ($mapping['type'] === 'post') {
            return $this->create_or_update_post($mapping, $record);
        } else {
            return $this->create_or_update_term($mapping, $record);
        }
    }
    
    /**
     * Handle UPDATE event
     */
    private function handle_update(array $mapping, array $record): array
    {
        return $this->handle_insert($mapping, $record); // Same logic, upsert pattern
    }
    
    /**
     * Handle DELETE event
     */
    private function handle_delete(array $mapping, array $record): array
    {
        $supabase_id = $record[$mapping['id_field']] ?? '';
        
        if (empty($supabase_id)) {
            throw new \Exception('Missing ID field for delete');
        }
        
        if ($mapping['type'] === 'post') {
            $posts = get_posts([
                'post_type' => $mapping['post_type'],
                'meta_key' => $mapping['meta_key'],
                'meta_value' => $supabase_id,
                'posts_per_page' => 1,
                'lang' => '' // Query all languages
            ]);
            
            if (!empty($posts)) {
                wp_delete_post($posts[0]->ID, true);
                return ['deleted_post_id' => $posts[0]->ID];
            }
        } else {
            $terms = get_terms([
                'taxonomy' => $mapping['taxonomy'],
                'meta_key' => $mapping['meta_key'],
                'meta_value' => $supabase_id,
                'hide_empty' => false
            ]);
            
            if (!empty($terms) && !is_wp_error($terms)) {
                wp_delete_term($terms[0]->term_id, $mapping['taxonomy']);
                return ['deleted_term_id' => $terms[0]->term_id];
            }
        }
        
        return ['message' => 'No matching entity found to delete'];
    }
    
    /**
     * Create or update a WordPress post
     */
    private function create_or_update_post(array $mapping, array $record): array
    {
        $supabase_id = $record[$mapping['id_field']] ?? '';
        $post_type = $mapping['post_type'];
        
        // Check if post already exists — try supabase_id first, then zoho_account_id
        $existing = get_posts([
            'post_type' => $post_type,
            'meta_key' => $mapping['meta_key'],
            'meta_value' => $supabase_id,
            'posts_per_page' => 1,
            'post_status' => 'any',
            'lang' => '' // Query all languages
        ]);
        
        // Fallback: try zoho_account_id / zoho_product_id
        if (empty($existing)) {
            $fallback_key = ($post_type === 'sit-university') ? 'zoho_account_id' : 'zoho_product_id';
            $existing = get_posts([
                'post_type' => $post_type,
                'meta_key' => $fallback_key,
                'meta_value' => $supabase_id,
                'posts_per_page' => 1,
                'post_status' => 'any',
                'lang' => ''
            ]);
        }
        
        $post_id = !empty($existing) ? $existing[0]->ID : 0;
        
        // Prepare post data based on post type
        $post_data = $this->prepare_post_data($post_type, $record, $post_id);
        
        if ($post_id) {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
            // Ensure supabase_id is set for future lookups
            update_post_meta($post_id, $mapping['meta_key'], $supabase_id);
        } else {
            $post_id = wp_insert_post($post_data);
            update_post_meta($post_id, $mapping['meta_key'], $supabase_id);
            
            // Also keep zoho_account_id/zoho_product_id for backwards compatibility
            if ($post_type === 'sit-university') {
                update_post_meta($post_id, 'zoho_account_id', $supabase_id);
            } elseif ($post_type === 'sit-program') {
                update_post_meta($post_id, 'zoho_product_id', $supabase_id);
            }
        }
        
        // Update meta fields
        $this->update_post_meta_from_record($post_id, $post_type, $record);
        
        // Update taxonomy relationships
        $this->update_post_taxonomies($post_id, $post_type, $record);
        
        // Sync status to ALL Polylang translations (same zoho_account_id)
        if ($post_type === 'sit-university' || $post_type === 'sit-program') {
            $compat_key = ($post_type === 'sit-university') ? 'zoho_account_id' : 'zoho_product_id';
            $sibling_posts = get_posts([
                'post_type' => $post_type,
                'meta_key' => $compat_key,
                'meta_value' => $supabase_id,
                'posts_per_page' => -1,
                'post_status' => 'any',
                'lang' => ''
            ]);
            
            foreach ($sibling_posts as $sibling) {
                if ($sibling->ID === $post_id) continue; // Already updated above
                
                // Sync post_status
                wp_update_post([
                    'ID' => $sibling->ID,
                    'post_status' => $post_data['post_status'],
                ]);
                
                // Sync critical meta fields to translations
                $this->update_post_meta_from_record($sibling->ID, $post_type, $record);
                
                // Ensure supabase_id is set on translations too
                update_post_meta($sibling->ID, $mapping['meta_key'], $supabase_id);
            }
        }
        
        return [
            'post_id' => $post_id,
            'action' => !empty($existing) ? 'updated' : 'created'
        ];
    }
    
    /**
     * Prepare post data for insert/update
     */
    private function prepare_post_data(string $post_type, array $record, int $post_id = 0): array
    {
        $active = $record['active'] ?? true;
        // Normalize: Supabase may send boolean true/false, string 'true'/'false', or '1'/'0'
        $is_active = ($active === true || $active === 'true' || $active === '1' || $active === 1);
        
        $data = [
            'post_type' => $post_type,
            'post_status' => $is_active ? 'publish' : 'draft'
        ];
        
        switch ($post_type) {
            case 'sit-university':
                $data['post_title'] = $record['name'] ?? '';
                $data['post_content'] = $record['description'] ?? '';
                break;
                
            case 'sit-program':
                $data['post_title'] = $record['name'] ?? '';
                // Generate slug from name
                $data['post_name'] = sanitize_title($record['name'] ?? '');
                break;
                
            case 'sit-campus':
                $data['post_title'] = $record['name'] ?? '';
                break;
        }
        
        return $data;
    }
    
    /**
     * Update post meta from Supabase record
     */
    private function update_post_meta_from_record(int $post_id, string $post_type, array $record): void
    {
        // Map Supabase fields to post meta
        $field_mappings = $this->get_field_mappings($post_type);
        
        // Boolean fields that need normalization to '1'/'' for WordPress
        $boolean_fields = ['active', 'active_in_apps', 'active_applications', 'featured_university'];
        
        // Dual-write mappings: Supabase uses lowercase but many templates read old Zoho CamelCase keys
        // Write BOTH so all templates work without migration
        $dual_write_aliases = [
            // University fields
            'qs_rank'            => 'QS_Rank',
            'year_founded'       => 'Year_Founded',
            'acomodation'        => 'Accommodation',
            'description'        => 'Description',
            // Program fields
            'official_tuition'   => 'Official_Tuition',
            'discounted_tuition' => 'Discounted_Tuition',
            'tuition_currency'   => 'Tuition_Currency',
            'study_years'        => 'Study_Years',
            'advanced_discount'  => 'Advanced_Discount',
        ];
        
        foreach ($field_mappings as $supabase_field => $meta_key) {
            if (isset($record[$supabase_field])) {
                $value = $record[$supabase_field];
                
                // Normalize boolean values to '1' or '' for WP meta compatibility
                if (in_array($supabase_field, $boolean_fields)) {
                    $value = ($value === true || $value === 'true' || $value === '1' || $value === 1) ? '1' : '';
                }
                
                update_post_meta($post_id, $meta_key, $value);
                
                // Also update ACF field if it exists
                if (function_exists('update_field')) {
                    update_field($meta_key, $value, $post_id);
                }
                
                // Write alias meta key for old Zoho CamelCase compatibility
                if (isset($dual_write_aliases[$supabase_field])) {
                    $alias = $dual_write_aliases[$supabase_field];
                    update_post_meta($post_id, $alias, $value);
                }
            }
        }
        
        // Store raw Supabase data for reference
        update_post_meta($post_id, '_supabase_data', $record);
        update_post_meta($post_id, '_supabase_synced_at', current_time('mysql'));
    }
    
    /**
     * Get field mappings for post type
     */
    private function get_field_mappings(string $post_type): array
    {
        $mappings = [
            'sit-university' => [
                // Actual Supabase columns → WP meta keys
                'sector' => 'sector',
                'phone' => 'phone',
                'wesbite' => 'website',      // Typo in Supabase
                // 'logo' and 'profile_image' excluded — images managed in WordPress
                'address' => 'address',
                'year_founded' => 'year_founded',
                'qs_rank' => 'qs_rank',
                'admission_email' => 'admission_email',
                'acomodation' => 'acomodation',
                'active' => 'Active_in_Search',
                'active_in_apps' => 'Active_in_Apps',
                'featured_university' => 'Featured_Univesity',
                'number_of_students' => 'Number_Of_Students',
                'description' => 'description',
            ],
            'sit-program' => [
                'active' => 'Active_in_Search',
                'active_applications' => 'Active_in_Apps',  // Supabase uses 'active_applications'
                'official_tuition' => 'official_tuition',
                'discounted_tuition' => 'discounted_tuition',
                'tuition_currency' => 'tuition_currency',
                'study_years' => 'study_years',
                'university_id' => 'zh_university_supabase_id',
                'university_name' => 'University',
                'university_sector' => 'university_sector',
                'degree_name' => 'Degree',
                'speciality_name' => 'Speciality',
                'language_name' => 'Language',
                'faculty_name' => 'Faculty',
                'country_name' => 'Country',
                'city_name' => 'City',
                'tuition_fee_usd' => 'tuition_fee_usd',
                'searchable_content' => 'searchable_content',
                'advanced_discount' => 'advanced_discount',  // Will work once column is added to Supabase
            ],
            'sit-campus' => [
                'address' => 'address',
                'university' => 'zh_university_supabase_id',  // Supabase uses 'university' not 'university_id'
                'Faculty' => 'campus_faculty_ids'             // Array of faculty Zoho IDs
            ]
        ];
        
        return $mappings[$post_type] ?? [];
    }
    
    /**
     * Update post taxonomy relationships
     */
    private function update_post_taxonomies(int $post_id, string $post_type, array $record): void
    {
        // Programs use *_id fields, universities use bare names (city, country)
        $taxonomy_mappings = [
            'country_id' => 'sit-country',
            'city_id' => 'sit-city',
            'degree_id' => 'sit-degree',
            'faculty_id' => 'sit-faculty',
            'speciality_id' => 'sit-speciality',
            'language_id' => 'sit-language'
        ];
        
        foreach ($taxonomy_mappings as $field => $taxonomy) {
            if (!empty($record[$field])) {
                $term = $this->get_term_by_supabase_id($taxonomy, $record[$field]);
                if ($term) {
                    wp_set_object_terms($post_id, $term->term_id, $taxonomy);
                }
            }
        }
        
        // Universities have 'city' and 'country' columns (without _id suffix)
        // These contain Supabase/Zoho IDs just like the _id fields on programs
        if ($post_type === 'sit-university') {
            $uni_taxonomy_fields = [
                'country' => 'sit-country',
                'city' => 'sit-city',
            ];
            foreach ($uni_taxonomy_fields as $field => $taxonomy) {
                if (!empty($record[$field])) {
                    $term = $this->get_term_by_supabase_id($taxonomy, $record[$field]);
                    if (!$term) {
                        // Fallback: try the old zoho meta key
                        $old_key = 'zoho_' . $field . '_id';
                        $terms = get_terms([
                            'taxonomy' => $taxonomy,
                            'meta_key' => $old_key,
                            'meta_value' => $record[$field],
                            'hide_empty' => false
                        ]);
                        $term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
                    }
                    if ($term) {
                        wp_set_object_terms($post_id, $term->term_id, $taxonomy);
                    }
                }
            }
        }
        
        // Also try name-based taxonomy assignment by looking up term by name
        $name_taxonomy_mappings = [
            'country_name' => 'sit-country',
            'city_name' => 'sit-city',
            'degree_name' => 'sit-degree',
            'faculty_name' => 'sit-faculty',
            'speciality_name' => 'sit-speciality',
            'language_name' => 'sit-language'
        ];
        
        foreach ($name_taxonomy_mappings as $field => $taxonomy) {
            $id_field = str_replace('_name', '_id', $field);
            // Only use name-based if we don't have the ID field
            if (empty($record[$id_field]) && !empty($record[$field])) {
                $term = get_term_by('name', $record[$field], $taxonomy);
                if ($term && !is_wp_error($term)) {
                    wp_set_object_terms($post_id, $term->term_id, $taxonomy);
                }
            }
        }
        
        // Campus: link Faculty array to sit-faculty taxonomy
        if ($post_type === 'sit-campus' && !empty($record['Faculty']) && is_array($record['Faculty'])) {
            $faculty_term_ids = [];
            foreach ($record['Faculty'] as $faculty_supabase_id) {
                $term = $this->get_term_by_supabase_id('sit-faculty', $faculty_supabase_id);
                if (!$term) {
                    // Fallback: old zoho meta key
                    $terms = get_terms([
                        'taxonomy' => 'sit-faculty',
                        'meta_key' => 'zoho_faculty_id',
                        'meta_value' => $faculty_supabase_id,
                        'hide_empty' => false
                    ]);
                    $term = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;
                }
                if ($term) {
                    $faculty_term_ids[] = $term->term_id;
                }
            }
            if (!empty($faculty_term_ids)) {
                wp_set_object_terms($post_id, $faculty_term_ids, 'sit-faculty');
            }
        }
        
        // Link to university post for programs and campuses
        // Programs use 'university_id', campuses use 'university'
        $uni_ref = $record['university_id'] ?? $record['university'] ?? null;
        if (in_array($post_type, ['sit-program', 'sit-campus']) && !empty($uni_ref)) {
            // Try supabase_id first, then zoho_account_id
            $university_posts = get_posts([
                'post_type' => 'sit-university',
                'meta_key' => 'supabase_id',
                'meta_value' => $uni_ref,
                'posts_per_page' => 1,
                'lang' => '' // Query all languages
            ]);
            
            if (empty($university_posts)) {
                $university_posts = get_posts([
                    'post_type' => 'sit-university',
                    'meta_key' => 'zoho_account_id',
                    'meta_value' => $uni_ref,
                    'posts_per_page' => 1,
                    'lang' => ''
                ]);
            }
            
            if (!empty($university_posts)) {
                update_post_meta($post_id, 'zh_university', $university_posts[0]->ID);
            }
        }
    }
    
    /**
     * Create or update a taxonomy term
     */
    private function create_or_update_term(array $mapping, array $record): array
    {
        $supabase_id = $record[$mapping['id_field']] ?? '';
        $taxonomy = $mapping['taxonomy'];
        $name = $record['name'] ?? '';
        
        if (empty($name)) {
            throw new \Exception('Missing name for taxonomy term');
        }
        
        // Check if term exists
        $existing = get_terms([
            'taxonomy' => $taxonomy,
            'meta_key' => $mapping['meta_key'],
            'meta_value' => $supabase_id,
            'hide_empty' => false
        ]);
        
        if (!empty($existing) && !is_wp_error($existing)) {
            $term = $existing[0];
            wp_update_term($term->term_id, $taxonomy, ['name' => $name]);
            $term_id = $term->term_id;
            $action = 'updated';
        } else {
            $result = wp_insert_term($name, $taxonomy);
            if (is_wp_error($result)) {
                // Term might exist with same name
                $existing_term = get_term_by('name', $name, $taxonomy);
                if ($existing_term) {
                    $term_id = $existing_term->term_id;
                    $action = 'linked';
                } else {
                    throw new \Exception('Failed to create term: ' . $result->get_error_message());
                }
            } else {
                $term_id = $result['term_id'];
                $action = 'created';
            }
        }
        
        // Update term meta
        update_term_meta($term_id, $mapping['meta_key'], $supabase_id);
        
        // Store additional fields in term meta
        $term_meta_fields = ['code', 'active', 'country', 'country_code', 'active_on_nationalities', 'active_on_university', 'active_in_university', 'faculty_id'];
        foreach ($term_meta_fields as $field) {
            if (isset($record[$field])) {
                update_term_meta($term_id, $field, $record[$field]);
            }
        }
        
        // Backwards compatibility - keep old zoho meta keys
        $old_meta_keys = [
            'sit-country' => 'zoho_country_id',
            'sit-city' => 'zoho_city_id',
            'sit-degree' => 'zoho_degree_id',
            'sit-faculty' => 'zoho_faculty_id',
            'sit-speciality' => 'zoho_speciality_id',
            'sit-language' => 'zoho_language_id'
        ];
        
        if (isset($old_meta_keys[$taxonomy])) {
            update_term_meta($term_id, $old_meta_keys[$taxonomy], $supabase_id);
        }
        
        return [
            'term_id' => $term_id,
            'action' => $action
        ];
    }
    
    /**
     * Get term by Supabase ID
     */
    private function get_term_by_supabase_id(string $taxonomy, string $supabase_id): ?\WP_Term
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'meta_key' => 'supabase_id',
            'meta_value' => $supabase_id,
            'hide_empty' => false
        ]);
        
        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0];
        }
        
        return null;
    }
    
    /**
     * Clear related caches after sync
     */
    private function clear_related_cache(string $table): void
    {
        // Clear Supabase service cache
        $supabase = new \SIT\Search\Services\Supabase();
        $supabase->clear_cache($table);
        
        // Clear all university-related caches (active IDs + top universities shortcode)
        \SIT\Search\Services\CachedData::clear_university_cache();
        
        // Clear any transients related to the table
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_%' . str_replace('zoho_', '', $table) . '%'
            )
        );
    }
}
