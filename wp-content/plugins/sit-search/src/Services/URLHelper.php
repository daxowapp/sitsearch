<?php

namespace SIT\Search\Services;

/**
 * URL Helper Service
 * 
 * Provides dynamic URL generation to replace hardcoded URLs.
 * All URLs are derived from WordPress site settings.
 */
class URLHelper
{
    /**
     * Get the site home URL
     */
    public static function home(): string
    {
        return home_url('/');
    }
    
    /**
     * Get the site URL
     */
    public static function site(): string
    {
        return site_url('/');
    }
    
    /**
     * Get the content URL (wp-content)
     */
    public static function content(): string
    {
        return content_url('/');
    }
    
    /**
     * Get the uploads URL
     */
    public static function uploads(): string
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/';
    }
    
    /**
     * Get plugin assets URL
     */
    public static function assets(): string
    {
        return STI_SEARCH_URL . 'assets/';
    }
    
    /**
     * Get upload file URL
     */
    public static function upload($path): string
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/' . ltrim($path, '/');
    }
    
    /**
     * Get the apply page URL with optional program ID
     */
    public static function apply($prog_id = null): string
    {
        $url = home_url('/apply/');
        if ($prog_id) {
            $url = add_query_arg('prog_id', $prog_id, $url);
        }
        return $url;
    }
    
    /**
     * Get the results page URL
     */
    public static function results($args = []): string
    {
        $url = home_url('/results/');
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }
        return $url;
    }
    
    /**
     * Get the university page URL
     */
    public static function university($uni_id = null): string
    {
        $url = home_url('/university/');
        if ($uni_id) {
            $url = add_query_arg('uni-id', $uni_id, $url);
        }
        return $url;
    }
    
    /**
     * Get plugin image URL
     * First checks if image exists in plugin assets, otherwise uses uploads
     */
    public static function image($filename): string
    {
        // Check if it's in plugin assets
        $asset_path = STI_SEARCH_DIR . 'assets/images/' . $filename;
        if (file_exists($asset_path)) {
            return STI_SEARCH_URL . 'assets/images/' . $filename;
        }
        
        // Otherwise, use uploads directory
        return self::upload($filename);
    }
    
    /**
     * Get placeholder image URL
     */
    public static function placeholder($width = 714, $height = 340, $text = 'Image'): string
    {
        return "https://placehold.co/{$width}x{$height}?text=" . urlencode($text);
    }
    
    /**
     * Get country flag URL
     */
    public static function flag($country_code): string
    {
        if (strtolower($country_code) === 'xk' || strtolower($country_code) === 'nc') {
            // North Cyprus or Kosovo - use local image
            return self::upload('2025/03/northern-cyprus.png');
        }
        return 'https://flagcdn.com/w640/' . strtolower($country_code) . '.png';
    }
    
    /**
     * Get admin URL
     */
    public static function admin($path = ''): string
    {
        return admin_url($path);
    }
    
    /**
     * Get REST API URL
     */
    public static function rest($route = ''): string
    {
        return rest_url('sit-search/v1/' . ltrim($route, '/'));
    }
    
    /**
     * Replace hardcoded domain with dynamic URL in a string
     */
    public static function replaceHardcoded($content): string
    {
        // List of domains to replace
        $hardcoded_domains = [
            'https://search.studyinturkiye.com',
            'http://search.studyinturkiye.com',
            'https://studyinturkiye.com',
            'http://studyinturkiye.com'
        ];
        
        $site_url = rtrim(home_url(), '/');
        
        foreach ($hardcoded_domains as $domain) {
            $content = str_replace($domain, $site_url, $content);
        }
        
        return $content;
    }
}
