<?php

namespace SIT\Search\Services;

/**
 * Cached Data Service
 * 
 * Provides cached access to frequently used data to improve performance.
 */
class CachedData
{
    private const CACHE_GROUP = 'sit_search';
    private const CACHE_EXPIRY = 3600; // 1 hour
    
    /**
     * Get active university IDs (cached)
     */
    public static function get_active_university_ids(): array
    {
        $cache_key = 'active_university_ids';
        
        // Try object cache first
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($cached !== false) {
            return $cached;
        }
        
        // Try transient cache
        $transient_key = 'sit_' . $cache_key;
        $cached = get_transient($transient_key);
        if ($cached !== false) {
            wp_cache_set($cache_key, $cached, self::CACHE_GROUP, self::CACHE_EXPIRY);
            return $cached;
        }
        
        // Query database
        global $wpdb;
        
        // This query is much faster than looping through all posts
        $active_ids = $wpdb->get_col("
            SELECT DISTINCT p.ID 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'sit-university'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'Active_in_Search'
            AND (pm.meta_value = '1' OR pm.meta_value = 'true')
        ");
        
        $active_ids = array_map('intval', $active_ids);
        
        // Cache the result
        set_transient($transient_key, $active_ids, self::CACHE_EXPIRY);
        wp_cache_set($cache_key, $active_ids, self::CACHE_GROUP, self::CACHE_EXPIRY);
        
        return $active_ids;
    }
    
    /**
     * Get taxonomy terms (cached)
     */
    public static function get_taxonomy_terms(string $taxonomy, bool $hide_empty = false): array
    {
        $cache_key = "taxonomy_{$taxonomy}";
        
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($cached !== false) {
            return $cached;
        }
        
        $transient_key = 'sit_' . $cache_key;
        $cached = get_transient($transient_key);
        if ($cached !== false) {
            wp_cache_set($cache_key, $cached, self::CACHE_GROUP, self::CACHE_EXPIRY);
            return $cached;
        }
        
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => $hide_empty,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        if (is_wp_error($terms)) {
            return [];
        }
        
        set_transient($transient_key, $terms, self::CACHE_EXPIRY * 24); // 24 hours for taxonomy
        wp_cache_set($cache_key, $terms, self::CACHE_GROUP, self::CACHE_EXPIRY * 24);
        
        return $terms;
    }
    
    /**
     * Get degrees (cached)
     */
    public static function get_degrees(): array
    {
        return self::get_taxonomy_terms('sit-degree');
    }
    
    /**
     * Get countries (cached)
     */
    public static function get_countries(): array
    {
        return self::get_taxonomy_terms('sit-country');
    }
    
    /**
     * Get cities (cached)
     */
    public static function get_cities(): array
    {
        return self::get_taxonomy_terms('sit-city');
    }
    
    /**
     * Get faculties (cached)
     */
    public static function get_faculties(): array
    {
        return self::get_taxonomy_terms('sit-faculty');
    }
    
    /**
     * Get specialities (cached)
     */
    public static function get_specialities(): array
    {
        return self::get_taxonomy_terms('sit-speciality');
    }
    
    /**
     * Get languages (cached)
     */
    public static function get_languages(): array
    {
        return self::get_taxonomy_terms('sit-language');
    }
    
    /**
     * Get Turkey and North Cyprus term IDs (cached)
     */
    public static function get_allowed_country_ids(): array
    {
        $cache_key = 'allowed_country_ids';
        
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($cached !== false) {
            return $cached;
        }
        
        $allowed_countries = [];
        
        $turkey_term = get_term_by('name', 'Turkey', 'sit-country');
        $north_cyprus_term = get_term_by('name', 'North Cyprus', 'sit-country');
        
        if ($turkey_term) {
            $allowed_countries[] = $turkey_term->term_id;
        }
        if ($north_cyprus_term) {
            $allowed_countries[] = $north_cyprus_term->term_id;
        }
        
        wp_cache_set($cache_key, $allowed_countries, self::CACHE_GROUP, self::CACHE_EXPIRY * 24);
        
        return $allowed_countries;
    }
    
    /**
     * Clear all caches
     */
    public static function clear_all(): void
    {
        global $wpdb;
        
        // Clear transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sit_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_sit_%'");
        
        // Clear object cache
        wp_cache_delete('active_university_ids', self::CACHE_GROUP);
        
        $taxonomies = ['sit-degree', 'sit-country', 'sit-city', 'sit-faculty', 'sit-speciality', 'sit-language'];
        foreach ($taxonomies as $tax) {
            wp_cache_delete("taxonomy_{$tax}", self::CACHE_GROUP);
        }
        
        wp_cache_delete('allowed_country_ids', self::CACHE_GROUP);
    }
    
    /**
     * Clear university cache
     */
    public static function clear_university_cache(): void
    {
        delete_transient('sit_active_university_ids');
        wp_cache_delete('active_university_ids', self::CACHE_GROUP);
    }
}
