<?php

namespace SIT\Search\Services;

class FeaturedUniversity {

    private const META_KEY_IS_FEATURED = 'sit_is_featured';
    private const META_KEY_PRIORITY = 'sit_featured_priority';
    private const META_KEY_EXPIRY = 'sit_featured_expiry';
    
    // In-memory cache to prevent N+1 queries during loops
    private static $featured_cache = null;

    /**
     * Load and cache all featured university statuses efficiently
     */
    private static function init_cache(): void {
        if (self::$featured_cache !== null) {
            return;
        }

        // Try transient
        $transient_key = 'sit_all_featured_universities';
        $cached = get_transient($transient_key);
        
        if (is_array($cached)) {
            self::$featured_cache = $cached;
            return;
        }

        // Otherwise query the DB explicitly for all
        $args = [
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => self::META_KEY_IS_FEATURED,
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];

        $post_ids = get_posts($args);
        $result = [];
        $now = current_time('timestamp');

        // Prime meta cache to avoid 1x1 lookups
        if (!empty($post_ids)) {
            update_meta_cache('post', $post_ids);
            
            foreach ($post_ids as $id) {
                $expiry = get_post_meta($id, self::META_KEY_EXPIRY, true);
                if ($expiry && strtotime($expiry) < $now) {
                    continue; // Expired
                }
                
                $result[$id] = [
                    'priority' => (int) get_post_meta($id, self::META_KEY_PRIORITY, true),
                    'expiry' => $expiry
                ];
            }
        }

        self::$featured_cache = $result;
        set_transient($transient_key, $result, HOUR_IN_SECONDS); // Cache for 1 hour
    }

    /**
     * Get all featured universities sorted by priority (Desc)
     * 
     * @param int $limit Optional limit
     * @return array Array of WP_Post objects
     */
    public function getFeatured($limit = -1): array {
        self::init_cache();
        
        $valid_ids = array_keys(self::$featured_cache);
        
        if (empty($valid_ids)) {
            return [];
        }
        
        $args = [
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'post__in' => $valid_ids,
            'posts_per_page' => $limit,
            'meta_key' => self::META_KEY_PRIORITY,
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ];
        
        return get_posts($args);
    }

    /**
     * Set a university as featured
     */
    public function setFeatured(int $post_id, int $priority = 5, string $expiry_date = ''): void {
        update_post_meta($post_id, self::META_KEY_IS_FEATURED, '1');
        update_post_meta($post_id, self::META_KEY_PRIORITY, $priority);
        update_post_meta($post_id, self::META_KEY_EXPIRY, $expiry_date);
        
        // Clear caches
        delete_transient('sit_all_featured_universities');
        self::$featured_cache = null;
        CachedData::clear_university_cache();
    }

    /**
     * Remove featured status
     */
    public function removeFeatured(int $post_id): void {
        delete_post_meta($post_id, self::META_KEY_IS_FEATURED);
        delete_post_meta($post_id, self::META_KEY_PRIORITY);
        delete_post_meta($post_id, self::META_KEY_EXPIRY);
        
        delete_transient('sit_all_featured_universities');
        self::$featured_cache = null;
        CachedData::clear_university_cache();
    }

    /**
     * Check if university is featured (Now uses O(1) in-memory cache)
     */
    public function isFeatured(int $post_id): bool {
        self::init_cache();
        return isset(self::$featured_cache[$post_id]);
    }

    /**
     * Get feature details
     */
    public function getDetails(int $post_id): array {
        self::init_cache();
        
        if (isset(self::$featured_cache[$post_id])) {
            return [
                'is_featured' => true,
                'priority' => self::$featured_cache[$post_id]['priority'],
                'expiry' => self::$featured_cache[$post_id]['expiry']
            ];
        }
        
        return [
            'is_featured' => false,
            'priority' => 0,
            'expiry' => ''
        ];
    }
}
