<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class SearchBar
{
    public function __invoke()
    {
        $countries = get_terms(
            array(
                'taxonomy' => 'sit-country',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'active_on_university',
                        'compare' => '=',
                        'value' => '1'
                    )
                )
            )
        );


        $specialities = get_terms(
            array(
                'taxonomy' => 'sit-speciality',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'active_in_search',
                        'compare' => '=',
                        'value' => '1'
                    )
                )
            )
        );

        $degrees = get_terms(
            array(
                'taxonomy' => 'sit-degree',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'active_in_search',
                        'compare' => '=',
                        'value' => '1'
                    )
                )
            )
        );

        ob_start();
        Template::render(
            'shortcodes/search-bar',
            [
                'countries' => $countries,
                'specialities' => $specialities,
                'degrees' => $degrees
            ]);
        return ob_get_clean();
    }
}