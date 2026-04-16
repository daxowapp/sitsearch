<?php

namespace SIT\Search\Services;

/**
 * SearchQueryLogger — Logs AI search queries to a custom database table
 * so admins can see what people are searching for.
 */
class SearchQueryLogger
{
    /**
     * Database table name (without prefix).
     */
    const TABLE_NAME = 'sit_search_queries';

    /**
     * Get the full table name with WP prefix.
     */
    public static function table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    /**
     * Create or update the database table via dbDelta.
     */
    public static function create_table(): void
    {
        global $wpdb;

        $table_name      = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            query varchar(500) NOT NULL,
            expanded_terms longtext DEFAULT NULL,
            filters_extracted longtext DEFAULT NULL,
            results_count int(11) DEFAULT 0,
            ip_hash varchar(64) DEFAULT '',
            user_agent varchar(500) DEFAULT '',
            source varchar(50) DEFAULT 'server',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_created_at (created_at),
            KEY idx_query (query(100))
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Log a search query.
     *
     * @param string $query          Raw user search text
     * @param array  $expanded_terms AI-expanded terms
     * @param array  $filters        Extracted filters (degree, language, etc.)
     * @param int    $results_count  Number of results returned
     * @param string $source         'server' or 'rest_api'
     */
    public static function log(
        string $query,
        array  $expanded_terms = [],
        array  $filters = [],
        int    $results_count = 0,
        string $source = 'server'
    ): void {
        global $wpdb;

        // Skip empty queries
        $query = trim($query);
        if (empty($query)) {
            return;
        }

        // Hash IP for privacy
        $ip_raw  = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip_hash = $ip_raw ? hash('sha256', $ip_raw . wp_salt('auth')) : '';

        $user_agent = isset($_SERVER['HTTP_USER_AGENT'])
            ? substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 500)
            : '';

        $wpdb->insert(
            self::table_name(),
            [
                'query'             => substr($query, 0, 500),
                'expanded_terms'    => !empty($expanded_terms) ? wp_json_encode($expanded_terms) : null,
                'filters_extracted' => !empty($filters) ? wp_json_encode($filters) : null,
                'results_count'     => $results_count,
                'ip_hash'           => $ip_hash,
                'user_agent'        => $user_agent,
                'source'            => $source,
                'created_at'        => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Get search queries with pagination and filtering.
     *
     * @param array $args {
     *     @type int    $page      Page number (1-indexed)
     *     @type int    $per_page  Items per page
     *     @type string $search    Search within queries
     *     @type string $date_from Start date (Y-m-d)
     *     @type string $date_to   End date (Y-m-d)
     *     @type string $orderby   Column to order by
     *     @type string $order     ASC or DESC
     * }
     * @return array ['items' => [], 'total' => int, 'pages' => int]
     */
    public static function get_queries(array $args = []): array
    {
        global $wpdb;

        $defaults = [
            'page'      => 1,
            'per_page'  => 50,
            'search'    => '',
            'date_from' => '',
            'date_to'   => '',
            'orderby'   => 'created_at',
            'order'     => 'DESC',
        ];

        $args   = wp_parse_args($args, $defaults);
        $table  = self::table_name();
        $where  = ['1=1'];
        $values = [];

        // Search filter
        if (!empty($args['search'])) {
            $where[]  = 'query LIKE %s';
            $values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }

        // Date range filters
        if (!empty($args['date_from'])) {
            $where[]  = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }
        if (!empty($args['date_to'])) {
            $where[]  = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }

        $where_clause = implode(' AND ', $where);

        // Sanitize orderby
        $allowed_orderby = ['created_at', 'query', 'results_count'];
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order   = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Total count
        $count_sql = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        if (!empty($values)) {
            $count_sql = $wpdb->prepare($count_sql, $values);
        }
        $total = (int) $wpdb->get_var($count_sql);

        // Paginated results
        $offset   = max(0, ($args['page'] - 1) * $args['per_page']);
        $data_sql = "SELECT * FROM $table WHERE $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $values[] = $args['per_page'];
        $values[] = $offset;

        $items = $wpdb->get_results($wpdb->prepare($data_sql, $values));

        return [
            'items' => $items ?: [],
            'total' => $total,
            'pages' => ceil($total / $args['per_page']),
        ];
    }

    /**
     * Get stats for the admin dashboard.
     *
     * @return array
     */
    public static function get_stats(): array
    {
        global $wpdb;
        $table = self::table_name();

        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
        if (!$table_exists) {
            return [
                'total'          => 0,
                'today'          => 0,
                'unique_queries' => 0,
                'this_week'      => 0,
                'top_queries'    => [],
            ];
        }

        $today     = current_time('Y-m-d');
        $week_ago  = date('Y-m-d', strtotime('-7 days', current_time('timestamp')));

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $today_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE DATE(created_at) = %s",
            $today
        ));

        $unique = (int) $wpdb->get_var("SELECT COUNT(DISTINCT query) FROM $table");

        $week_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE created_at >= %s",
            $week_ago . ' 00:00:00'
        ));

        // Top 10 most searched queries
        $top_queries = $wpdb->get_results(
            "SELECT query, COUNT(*) as search_count 
             FROM $table 
             GROUP BY query 
             ORDER BY search_count DESC 
             LIMIT 10"
        );

        return [
            'total'          => $total,
            'today'          => $today_count,
            'unique_queries' => $unique,
            'this_week'      => $week_count,
            'top_queries'    => $top_queries ?: [],
        ];
    }

    /**
     * Delete queries older than N days.
     *
     * @param int $days
     * @return int Number of rows deleted
     */
    public static function clear_old(int $days = 30): int
    {
        global $wpdb;
        $table    = self::table_name();
        $cutoff   = date('Y-m-d H:i:s', strtotime("-{$days} days", current_time('timestamp')));

        return (int) $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < %s",
            $cutoff
        ));
    }

    /**
     * Get all queries as CSV-ready rows.
     *
     * @param array $args Same args as get_queries but with per_page = -1
     * @return array
     */
    public static function get_for_export(array $args = []): array
    {
        global $wpdb;
        $table = self::table_name();

        $where  = ['1=1'];
        $values = [];

        if (!empty($args['date_from'])) {
            $where[]  = 'created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }
        if (!empty($args['date_to'])) {
            $where[]  = 'created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }

        $where_clause = implode(' AND ', $where);
        $sql = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql, ARRAY_A) ?: [];
    }
}
