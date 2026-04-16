<?php

namespace SIT\Search\Endpoints;

use SIT\Search\Services\GeoSchema;

/**
 * FAQ REST API Endpoint for lazy-loading FAQs
 *
 * Provides paginated, category-filtered FAQ data for the frontend
 * AJAX-powered "Load More" functionality.
 *
 * @since 1.3.0
 */
class FaqEndpoint
{
    public function register_routes()
    {
        register_rest_route('sit-search/v1', '/faqs/(?P<post_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_faqs'],
            'permission_callback' => '__return_true',
            'args' => [
                'post_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ],
                'page' => [
                    'default' => 1,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    },
                ],
                'per_page' => [
                    'default' => 10,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0 && $param <= 100;
                    },
                ],
                'category' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Get paginated FAQs for a program
     */
    public function get_faqs(\WP_REST_Request $request)
    {
        $post_id = (int) $request->get_param('post_id');
        $page = (int) $request->get_param('page');
        $per_page = (int) $request->get_param('per_page');
        $category = $request->get_param('category');

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'sit-program') {
            return new \WP_REST_Response([
                'error' => 'Program not found',
            ], 404);
        }

        $offset = ($page - 1) * $per_page;
        $faqs = GeoSchema::get_faq_data($post_id, $per_page, $offset, $category);
        $total = GeoSchema::get_faq_count($post_id);
        $categories = GeoSchema::get_faq_categories($post_id);

        // If no AI FAQs, count template FAQs
        if ($total === 0) {
            $template_faqs = GeoSchema::get_faq_data($post_id);
            $total = count($template_faqs);
        }

        $total_pages = $per_page > 0 ? ceil($total / $per_page) : 1;

        return new \WP_REST_Response([
            'faqs' => $faqs,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages,
            'categories' => $categories,
            'has_more' => $page < $total_pages,
        ]);
    }
}
