<?php

namespace SIT\Search\Shortcodes;
use SIT\Search\Services\Template;

class ProgramSteps
{
    public function __invoke()
    {
        ob_start();
        Template::render('shortcodes/program-steps');
        return ob_get_clean();
    }
}