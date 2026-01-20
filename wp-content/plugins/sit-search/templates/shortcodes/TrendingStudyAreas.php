<?php

namespace SIT\Search\Shortcodes;

use SIT\Search\Services\Template;

class TrendingStudyAreas
{
    public function __invoke($atts = [])
    {
        $atts = shortcode_atts(
            [
                'number' => 5,
            ],
            $atts
        );

        $areas = get_terms(
            array(
                'taxonomy' => 'sit-speciality',
                'hide_empty' => false,
                'number' => (int) $atts['number'],
            )
        );

        $areas = array_map(function ($area) {
            return [
                'id' => $area->term_id,
                'name' => $area->name,
                'slug' => $area->slug,
                'count' => $area->count,
                'image_url'=>!empty(get_term_meta($area->term_id, 'spec_image', true))  ?
                    esc_url(get_term_meta($area->term_id, 'spec_image', true))
                    :'https://placehold.co/60x60',
            ];
        }, $areas);

        ob_start();
        Template::render('shortcodes/trending-study-areas', ['areas' => $areas]);
        return ob_get_clean();
    }
}