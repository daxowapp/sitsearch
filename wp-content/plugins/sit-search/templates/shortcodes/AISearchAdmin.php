<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class AISearchAdmin
{
    public function __invoke()
    {
        // Check if user has admin capabilities
        if (!current_user_can('manage_options')) {
            return '<p>You do not have permission to access this page.</p>';
        }

        ob_start();
        Template::render('admin/ai-search-admin');
        return ob_get_clean();
    }
}
