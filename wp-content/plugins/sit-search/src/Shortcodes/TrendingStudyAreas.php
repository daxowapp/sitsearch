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
                'orderby' => 'name', // Get all terms first
            )
        );

        // Randomize the order
        if (!empty($areas) && !is_wp_error($areas)) {
            shuffle($areas);
            // Limit to the requested number (if number is empty or 0, show all)
            $number = (int) $atts['number'];
            if ($number > 0) {
                $areas = array_slice($areas, 0, $number);
            }
            // If number is 0 or empty, keep all areas (no slicing)
        }

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