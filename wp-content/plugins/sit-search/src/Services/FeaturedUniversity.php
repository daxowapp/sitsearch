<?php

namespace SIT\Search\Services;

class FeaturedUniversity {

    private const META_KEY_IS_FEATURED = 'sit_is_featured';
    private const META_KEY_PRIORITY = 'sit_featured_priority';
    private const META_KEY_EXPIRY = 'sit_featured_expiry';

    /**
     * Get all featured universities sorted by priority (Desc)
     * 
     * @param int $limit Optional limit
     * @return array Array of WP_Post objects
     */
    public function getFeatured($limit = -1): array {
        $args = [
            'post_type' => 'sit-university',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => self::META_KEY_IS_FEATURED,
                    'value' => '1',
                    'compare' => '='
                ]
            ],
            'meta_key' => self::META_KEY_PRIORITY,
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ];

        // Add expiry check if needed, though simpler to filter out expired ones in loop or separate cleanup
        // For now, let's assume we clean up or check expiry on retrieval
        
        $posts = get_posts($args);
        $valid_posts = [];
        $now = current_time('timestamp');

        foreach ($posts as $post) {
            $expiry = get_post_meta($post->ID, self::META_KEY_EXPIRY, true);
            if ($expiry && strtotime($expiry) < $now) {
                // Expired, maybe auto-remove feature status?
                // For now, just skip
                continue;
            }
            $valid_posts[] = $post;
        }

        return $valid_posts;
    }

    /**
     * Set a university as featured
     */
    public function setFeatured(int $post_id, int $priority = 5, string $expiry_date = ''): void {
        update_post_meta($post_id, self::META_KEY_IS_FEATURED, '1');
        update_post_meta($post_id, self::META_KEY_PRIORITY, $priority);
        update_post_meta($post_id, self::META_KEY_EXPIRY, $expiry_date);
        
        // Clear caches
        CachedData::clear_university_cache();
    }

    /**
     * Remove featured status
     */
    public function removeFeatured(int $post_id): void {
        delete_post_meta($post_id, self::META_KEY_IS_FEATURED);
        delete_post_meta($post_id, self::META_KEY_PRIORITY);
        delete_post_meta($post_id, self::META_KEY_EXPIRY);
        
        CachedData::clear_university_cache();
    }

    /**
     * Check if university is featured
     */
    public function isFeatured(int $post_id): bool {
        $is_featured = get_post_meta($post_id, self::META_KEY_IS_FEATURED, true);
        if (!$is_featured) return false;

        $expiry = get_post_meta($post_id, self::META_KEY_EXPIRY, true);
        if ($expiry && strtotime($expiry) < current_time('timestamp')) {
            return false;
        }

        return true;
    }

    /**
     * Get feature details
     */
    public function getDetails(int $post_id): array {
        return [
            'is_featured' => $this->isFeatured($post_id),
            'priority' => (int) get_post_meta($post_id, self::META_KEY_PRIORITY, true),
            'expiry' => get_post_meta($post_id, self::META_KEY_EXPIRY, true)
        ];
    }
}
