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
}