<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class Universities
{
    public function __invoke()
    {
        ob_start();
        Template::render('shortcodes/universities');
        return ob_get_clean();
    }

}