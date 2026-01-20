<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;
use SIT\Search\Services\Zoho;
class SearchProgram
{
    public function __invoke()
    {
        ob_start();
        
        Template::render('shortcodes/search-program');

        return ob_get_clean();
    }
}