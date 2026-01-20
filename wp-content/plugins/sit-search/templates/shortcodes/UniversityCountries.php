<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Functions;
use SIT\Search\Services\Template;

class UniversityCountries
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


        $countries = array_map(function ($country) {
            return [
                'id' => $country->term_id,
                'name' => $country->name,
                'flag' => Functions::getCountryFlag($country->name),
                'slug' => $country->slug
            ];
        }, $countries);

        ob_start();
        Template::render(
            'shortcodes/university-countries',
            [
                'countries' => $countries
            ]
        );
        return ob_get_clean();
    }
}