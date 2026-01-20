<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class NewShortcode
{
    public function __invoke()
    {
        ob_start();
        Template::render('shortcodes/filter-sort');
        return ob_get_clean();
    }
}