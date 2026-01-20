<?php

namespace SIT\Search\Services;

class Template
{
    /**
     * Retrieves a template
     *
     * @param string $route Route, @param string Props
     * @return string
     */
    public static function get($route, $props = [])
    {
        ob_start();

        self::render($route, $props);

        return ob_get_clean();
    }

    /**
     * Renders a template
     *
     * @param string $route Route, @param array Props
     * @return void
     */
    public static function render($route, $props = [])
    {
        extract($props);

        include STI_SEARCH_DIR . "templates/{$route}.html.php";
    }
}
