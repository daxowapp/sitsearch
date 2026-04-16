<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');

$taxonomies = ['sit-degree', 'sit-language', 'sit-city', 'sit-country'];

foreach ($taxonomies as $tax) {
    echo "--- $tax ---\n";
    $terms = get_terms([
        'taxonomy' => $tax,
        'hide_empty' => false,
    ]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            echo "ID: {$term->term_id} | Name: {$term->name}\n";
        }
    }
}
