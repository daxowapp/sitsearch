<?php
define( 'WP_USE_THEMES', false );
require( __DIR__ . '/wp-load.php' );

$program_id = get_posts([
    'post_type' => 'sit-program',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'fields' => 'ids'
])[0];

$meta = get_post_meta($program_id);
print_r($meta);
