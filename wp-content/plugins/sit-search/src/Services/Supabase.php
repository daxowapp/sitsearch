<?php

namespace SIT\Search\Services;

/**
 * Supabase REST API Client for WordPress
 * 
 * Handles all communication with Supabase database
 */
class Supabase
{
    private string $url;
    private string $anon_key;
    private string $service_role_key;
    private SIT_Logger $logger;
    
    // Cache duration in seconds
    private const CACHE_SHORT = 300;      // 5 minutes
    private const CACHE_MEDIUM = 3600;    // 1 hour
    private const CACHE_LONG = 86400;     // 24 hours
    
    public function __construct()
    {
        $this->url = defined('SUPABASE_URL') ? SUPABASE_URL : '';
        $this->anon_key = defined('SUPABASE_ANON_KEY') ? SUPABASE_ANON_KEY : '';
        $this->service_role_key = defined('SUPABASE_SERVICE_ROLE_KEY') ? SUPABASE_SERVICE_ROLE_KEY : '';
        $this->logger = SIT_Logger::get_instance();
    }
    
    /**
     * Check if Supabase is properly configured
     */
    public function is_configured(): bool
    {
        return !empty($this->url) && !empty($this->anon_key);
    }
    
    /**
     * Make GET request to Supabase REST API
     * 
     * @param string $table Table name
     * @param array $query Query parameters (select, filter, order, limit, etc.)
     * @param bool $use_cache Whether to cache the result
     * @param int $cache_duration Cache duration in seconds
     * @return array|null
     */
    public function get(string $table, array $query = [], bool $use_cache = true, int $cache_duration = self::CACHE_MEDIUM): ?array
    {
        $cache_key = 'supabase_' . md5($table . serialize($query));
        
        // Check cache first
        if ($use_cache) {
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $url = $this->build_url($table, $query);
        $response = $this->request($url, 'GET');
        
        if ($response !== null && $use_cache) {
            set_transient($cache_key, $response, $cache_duration);
        }
        
        return $response;
    }
    
    /**
     * Get a single record by ID
     */
    public function get_by_id(string $table, string $id, bool $use_cache = true): ?array
    {
        $result = $this->get($table, [
            'id' => 'eq.' . $id,
            'limit' => 1
        ], $use_cache);
        
        return $result && count($result) > 0 ? $result[0] : null;
    }
    
    /**
     * Get all records from a table with pagination
     */
    public function get_all(string $table, int $page = 1, int $per_page = 100, array $filters = [], string $order = 'created_at.desc'): array
    {
        $offset = ($page - 1) * $per_page;
        
        $query = array_merge($filters, [
            'order' => $order,
            'limit' => $per_page,
            'offset' => $offset
        ]);
        
        return $this->get($table, $query, true, self::CACHE_SHORT) ?? [];
    }
    
    /**
     * Get count of records in a table
     */
    public function count(string $table, array $filters = []): int
    {
        $url = $this->url . '/rest/v1/' . $table . '?select=count';
        
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $url .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        
        $response = $this->request($url, 'GET', [], [
            'Prefer' => 'count=exact'
        ]);
        
        return is_array($response) ? count($response) : 0;
    }
    
    /**
     * Get exact count of records matching filters using content-range header
     */
    public function count_filtered(string $table, array $query = []): int
    {
        $query['select'] = 'id';
        $query['limit'] = 1;
        $url = $this->build_url($table, $query);
        
        $headers = [
            'apikey' => $this->anon_key,
            'Authorization' => 'Bearer ' . $this->anon_key,
            'Content-Type' => 'application/json',
            'Prefer' => 'count=exact',
        ];
        
        $response = wp_remote_request($url, [
            'method' => 'GET',
            'headers' => $headers,
            'timeout' => 15,
        ]);
        
        if (is_wp_error($response)) {
            return 0;
        }
        
        // Parse count from content-range header: "0-0/1234"
        $content_range = wp_remote_retrieve_header($response, 'content-range');
        if ($content_range && preg_match('/\/(\d+)$/', $content_range, $matches)) {
            return (int) $matches[1];
        }
        
        // Fallback: count body array
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($body) ? count($body) : 0;
    }
    
    /**
     * Create a new record
     */
    public function insert(string $table, array $data): ?array
    {
        $url = $this->url . '/rest/v1/' . $table;
        
        return $this->request($url, 'POST', $data, [
            'Prefer' => 'return=representation'
        ], true);
    }
    
    /**
     * Update a record by ID
     */
    public function update(string $table, string $id, array $data): ?array
    {
        $url = $this->url . '/rest/v1/' . $table . '?id=eq.' . $id;
        
        return $this->request($url, 'PATCH', $data, [
            'Prefer' => 'return=representation'
        ], true);
    }
    
    /**
     * Delete a record by ID
     */
    public function delete(string $table, string $id): bool
    {
        $url = $this->url . '/rest/v1/' . $table . '?id=eq.' . $id;
        
        $response = $this->request($url, 'DELETE', [], [], true);
        
        return $response !== null;
    }
    
    /**
     * Clear cache for a specific table or all Supabase caches
     */
    public function clear_cache(string $table = ''): void
    {
        global $wpdb;
        
        if (!empty($table)) {
            // Clear specific table cache
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_supabase_' . md5($table) . '%'
                )
            );
        } else {
            // Clear all Supabase caches
            $wpdb->query(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_supabase_%'"
            );
        }
    }
    
    // =====================================
    // Table-Specific Helper Methods
    // =====================================
    
    /**
     * Get all active universities
     */
    public function get_universities(array $filters = []): array
    {
        $default_filters = ['active' => 'eq.true'];
        return $this->get('zoho_universities', array_merge($default_filters, $filters));
    }
    
    /**
     * Get university by ID
     */
    public function get_university(string $id): ?array
    {
        return $this->get_by_id('zoho_universities', $id);
    }
    
    /**
     * Get all active programs
     */
    public function get_programs(array $filters = []): array
    {
        $default_filters = ['active' => 'eq.true'];
        return $this->get('zoho_programs', array_merge($default_filters, $filters));
    }
    
    /**
     * Get program by ID
     */
    public function get_program(string $id): ?array
    {
        return $this->get_by_id('zoho_programs', $id);
    }
    
    /**
     * Search programs using full-text search
     */
    public function search_programs(string $query, int $limit = 50): array
    {
        // Supabase full-text search using the 'fts' column
        $search_query = str_replace(' ', ' & ', trim($query));
        
        return $this->get('zoho_programs', [
            'fts' => 'fts.' . $search_query,
            'active' => 'eq.true',
            'limit' => $limit,
            'order' => 'tuition_fee_usd.asc'
        ], false); // Don't cache search results
    }
    
    /**
     * Get all degrees
     */
    public function get_degrees(): array
    {
        return $this->get('zoho_degrees', ['active' => 'eq.true', 'order' => 'name.asc'], true, self::CACHE_LONG) ?? [];
    }
    
    /**
     * Get all countries
     */
    public function get_countries(): array
    {
        return $this->get('zoho_countries', ['order' => 'name.asc'], true, self::CACHE_LONG) ?? [];
    }
    
    /**
     * Get all cities, optionally filtered by country
     */
    public function get_cities(string $country_id = ''): array
    {
        $filters = ['order' => 'name.asc'];
        if (!empty($country_id)) {
            $filters['country'] = 'eq.' . $country_id;
        }
        return $this->get('zoho_cities', $filters, true, self::CACHE_LONG) ?? [];
    }
    
    /**
     * Get all faculties
     */
    public function get_faculties(): array
    {
        return $this->get('zoho_faculty', ['order' => 'name.asc'], true, self::CACHE_LONG) ?? [];
    }
    
    /**
     * Get all specialities
     */
    public function get_specialities(): array
    {
        return $this->get('zoho_speciality', ['order' => 'name.asc'], true, self::CACHE_LONG) ?? [];
    }
    
    /**
     * Get all languages
     */
    public function get_languages(): array
    {
        return $this->get('zoho_languages', ['active' => 'eq.true', 'order' => 'name.asc'], true, self::CACHE_LONG) ?? [];
    }
    
    /**
     * Get all campuses
     */
    public function get_campuses(string $university_id = ''): array
    {
        $filters = ['order' => 'name.asc'];
        if (!empty($university_id)) {
            $filters['university_id'] = 'eq.' . $university_id;
        }
        return $this->get('zoho_campus', $filters, true, self::CACHE_MEDIUM) ?? [];
    }
    
    /**
     * Create an application/lead
     */
    public function create_application(array $data): ?array
    {
        return $this->insert('zoho_applications', $data);
    }
    
    // =====================================
    // Private Helper Methods
    // =====================================
    
    /**
     * Build URL with query parameters
     */
    private function build_url(string $table, array $query = []): string
    {
        $url = $this->url . '/rest/v1/' . $table;
        
        if (!empty($query)) {
            $params = [];
            foreach ($query as $key => $value) {
                $params[] = urlencode($key) . '=' . urlencode($value);
            }
            $url .= '?' . implode('&', $params);
        }
        
        return $url;
    }

    /**
     * Upload a file to Supabase Storage
     *
     * @param string $file_path Local path to the file
     * @param string $file_name Target filename in the bucket
     * @param string $file_type MIME type of the file
     * @param string $bucket Bucket name (optional, defaults to SUPABASE_BUCKET)
     * @return array|null Response data containing Key/id or null on failure
     */
    public function upload_file(string $file_path, string $file_name, string $file_type, string $bucket = ''): ?array
    {
        if (empty($bucket)) {
            $bucket = defined('SUPABASE_BUCKET') ? SUPABASE_BUCKET : 'uploads';
        }
        
        $url = rtrim($this->url, '/') . '/storage/v1/object/' . $bucket . '/' . $file_name;
        
        $file_content = file_get_contents($file_path);
        
        if ($file_content === false) {
            $this->logger->log_message('error', 'Supabase file upload error: Cannot read local file ' . $file_path);
            return null;
        }

        $headers = [
            'apikey' => $this->anon_key,
            'Authorization' => 'Bearer ' . $this->anon_key,
            'Content-Type' => $file_type,
        ];
        
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $file_content,
            'timeout' => 60,
        ];
        
        $this->logger->log_message('info', "Supabase uploading file to: {$url}");
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $this->logger->log_message('error', 'Supabase upload request error: ' . $response->get_error_message());
            return null;
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($http_code >= 400) {
            $this->logger->log_message('error', "Supabase HTTP error {$http_code} during upload: {$body}");
            return null;
        }
        
        $decoded = json_decode($body, true);
        return $decoded;
    }
    
    /**
     * Make HTTP request to Supabase
     */
    private function request(string $url, string $method = 'GET', array $data = [], array $extra_headers = [], bool $use_service_key = false): ?array
    {
        $headers = [
            'apikey' => $this->anon_key,
            'Authorization' => 'Bearer ' . ($use_service_key ? $this->service_role_key : $this->anon_key),
            'Content-Type' => 'application/json',
        ];
        
        $headers = array_merge($headers, $extra_headers);
        
        $args = [
            'method' => $method,
            'headers' => $headers,
            'timeout' => 30,
        ];
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = json_encode($data);
        }
        
        $this->logger->log_message('info', "Supabase {$method} request to: {$url}");
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $this->logger->log_message('error', 'Supabase request error: ' . $response->get_error_message());
            return null;
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($http_code >= 400) {
            $this->logger->log_message('error', "Supabase HTTP error {$http_code}: {$body}");
            return null;
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log_message('error', 'Supabase JSON decode error: ' . json_last_error_msg());
            return null;
        }
        
        return $decoded;
    }
}
