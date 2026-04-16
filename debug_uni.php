<?php
require_once('wp-load.php');

// Find Antalya Bilim University
$uni = get_page_by_title('Antalya Bilim University', OBJECT, 'sit-university');

if (!$uni) {
    echo "University not found by exact title. Searching...\n";
    $args = [
        'post_type' => 'sit-university',
        's' => 'Antalya Bilim',
        'posts_per_page' => 1
    ];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $uni = $query->posts[0];
    }
}

if ($uni) {
    echo "University Found: " . $uni->post_title . " (ID: " . $uni->ID . ")\n";
    $terms = get_the_terms($uni->ID, 'sit-country');
    echo "Country Terms:\n";
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            echo "- " . $term->name . " (ID: " . $term->term_id . ")\n";
        }
    } else {
        echo "No country terms found.\n";
    }
} else {
    echo "University 'Antalya Bilim University' NOT found.\n";
}
