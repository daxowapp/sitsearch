<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\SearchQueryLogger;

/**
 * AJAX handlers for the Search Queries admin panel.
 */
class SearchQueriesAjax
{
    public function __construct()
    {
        add_action('wp_ajax_sit_get_search_queries', [$this, 'get_queries']);
        add_action('wp_ajax_sit_get_search_stats', [$this, 'get_stats']);
        add_action('wp_ajax_sit_clear_old_queries', [$this, 'clear_old']);
        add_action('wp_ajax_sit_export_queries_csv', [$this, 'export_csv']);
    }

    /**
     * Get paginated search queries.
     */
    public function get_queries(): void
    {
        check_ajax_referer('sit_search_queries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        $args = [
            'page'      => isset($_POST['page']) ? absint($_POST['page']) : 1,
            'per_page'  => isset($_POST['per_page']) ? absint($_POST['per_page']) : 50,
            'search'    => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            'date_from' => isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '',
            'date_to'   => isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '',
            'orderby'   => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'created_at',
            'order'     => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC',
        ];

        $result = SearchQueryLogger::get_queries($args);
        wp_send_json_success($result);
    }

    /**
     * Get search query statistics.
     */
    public function get_stats(): void
    {
        check_ajax_referer('sit_search_queries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        $stats = SearchQueryLogger::get_stats();
        wp_send_json_success($stats);
    }

    /**
     * Clear queries older than N days.
     */
    public function clear_old(): void
    {
        check_ajax_referer('sit_search_queries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        $days    = isset($_POST['days']) ? absint($_POST['days']) : 30;
        $deleted = SearchQueryLogger::clear_old($days);

        wp_send_json_success([
            'deleted' => $deleted,
            'message' => sprintf('Deleted %d queries older than %d days.', $deleted, $days),
        ]);
    }

    /**
     * Export queries as CSV download.
     */
    public function export_csv(): void
    {
        check_ajax_referer('sit_search_queries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $args = [
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to'   => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
        ];

        $rows = SearchQueryLogger::get_for_export($args);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=search-queries-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Query', 'Expanded Terms', 'Filters', 'Results', 'Source', 'Date']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'],
                $row['query'],
                $row['expanded_terms'],
                $row['filters_extracted'],
                $row['results_count'],
                $row['source'],
                $row['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}
