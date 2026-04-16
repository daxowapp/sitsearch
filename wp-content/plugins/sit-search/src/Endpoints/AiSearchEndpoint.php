<?php

namespace SIT\Search\Endpoints;

class AiSearchEndpoint
{
    public function register_routes()
    {
        register_rest_route('sit-search/v1', '/ai-search', [
            'methods'  => 'GET',
            'callback' => [$this, 'expand_search'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function expand_search(\WP_REST_Request $request)
    {
        $query = sanitize_text_field($request->get_param('q'));
        if (empty($query)) {
            return new \WP_REST_Response(['terms' => []], 200);
        }

        $api_key = get_option('sit_openai_api_key');
        if (empty($api_key)) {
            // Fallback to original query
            return new \WP_REST_Response(['terms' => [$query]], 200);
        }

        // Rate limiting (max 15 requests per minute per IP) to protect OpenAI credits
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rate_limit_key = 'sit_ai_rl_' . md5($ip);
        $attempts = (int) get_transient($rate_limit_key);

        if ($attempts >= 15) {
            // Fallback gracefully without throwing HTTP 429 to keep UI functional
            return new \WP_REST_Response(['terms' => [$query]], 200);
        }
        set_transient($rate_limit_key, $attempts + 1, MINUTE_IN_SECONDS);

        $system_prompt = "You are an AI search assistant for a university program database (study abroad portal). Follow these 3 rules:
1. Typo Correction: Fix spelling mistakes in the user query.
2. Translation: Translate any non-English query into English. (Our database expects English terms).
3. Expansion: Provide 5-8 highly relevant related synonyms or broader academic fields.

Return ONLY a JSON object exactly in this format:
{
    \"terms\": [\"corrected_query_or_translation\", \"synonym1\", \"synonym2\", \"synonym3\", \"synonym4\", \"synonym5\"]
}";

        $body = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => "Query: " . $query]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            error_log('AI Search Error: ' . $response->get_error_message());
            return new \WP_REST_Response(['terms' => [$query]], 200);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            error_log('AI Search API Error: ' . print_r($response_body, true));
            return new \WP_REST_Response(['terms' => [$query]], 200);
        }

        $data = json_decode($response_body, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $content = json_decode($data['choices'][0]['message']['content'], true);
            if (isset($content['terms']) && is_array($content['terms'])) {
                // Make sure to always include the original query as well, just in case
                $terms = array_unique(array_merge([$query], $content['terms']));
                // Ensure all strings are lowercase for easier matching later
                $terms = array_map('strtolower', $terms);
                return new \WP_REST_Response(['terms' => array_values($terms)], 200);
            }
        }

        return new \WP_REST_Response(['terms' => [strtolower($query)]], 200);
    }
}
