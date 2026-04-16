<?php

namespace SIT\Search\Services;

class Constants
{
    public static function get_zoho_client_id(): string
    {
        return get_field('zoho_client_id', 'option') ?? '';
    }

    public static function get_zoho_client_secret(): string
    {
        return get_field('zoho_client_secret', 'option') ?? '';
    }

    public static function get_zoho_refresh_token(): string
    {
        return get_field('zoho_secret_code', 'option') ?? '';
    }

    /**
     * Get OpenAI API key from WordPress options.
     * Checks ACF option field first, then falls back to standard WP option.
     */
    public static function get_openai_api_key(): string
    {
        // ACF option field
        if (function_exists('get_field')) {
            $key = get_field('openai_api_key', 'option');
            if (!empty($key)) {
                return $key;
            }
        }

        // Standard WP option (set via AI Settings admin page)
        return get_option('sit_openai_api_key', '');
    }

    /**
     * Get OpenRouter API key from WordPress options.
     * Used for GEO FAQ generation via OpenRouter LLM API.
     */
    public static function get_openrouter_api_key(): string
    {
        return get_option('sit_openrouter_api_key', '');
    }
}