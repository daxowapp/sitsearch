<?php
// Load WordPress environment
require_once('wp-load.php');

$terms = get_terms(array(
    'taxonomy' => 'sit-country',
    'hide_empty' => false,
));

echo "ID | Name | Slug\n";
echo "---|------|-----\n";
foreach ($terms as $term) {
    if (is_a($term, 'WP_Term')) {
        echo "{$term->term_id} | {$term->name} | {$term->slug}\n";
    }
}
