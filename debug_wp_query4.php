<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$args = array(
    'post_type' => 'sit-program',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        'relation' => 'AND',
        array('key' => 'zh_university', 'compare' => 'EXISTS'),
        array('key' => 'zh_university', 'value' => [61572,61569,61421,61408,61391,60938,59517,58875,58873,58802,57810,57689,57553,57552,57551,57550], 'compare' => 'IN')
    ),
    'no_found_rows' => false,
    'distinct' => true,
    'post__in' => [51811, 51812, 51830]
);
$query = new \WP_Query($args);
echo "FOUND POSTS: " . $query->found_posts . "\n";
