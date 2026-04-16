<?php

namespace SIT\Search\Actions;

class AiSettingsAdmin
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings()
    {
        register_setting('sit_ai_search_options', 'sit_openai_api_key');
        register_setting('sit_ai_search_options', 'sit_openrouter_api_key');
    }
}
