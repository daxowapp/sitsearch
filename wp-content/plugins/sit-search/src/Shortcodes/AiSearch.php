<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class AiSearch
{
    public function __invoke()
    {
        ob_start();
        Template::render('shortcodes/ai-search');
        return ob_get_clean();
    }
}
