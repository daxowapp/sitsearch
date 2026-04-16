<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

$programs = get_posts([
    'post_type' => 'sit-program',
    's' => 'Bahcesehir',
    'posts_per_page' => 5
]);

echo "=== BAHCESEHIR PROGRAMS ===\n";
foreach ($programs as $p) {
    echo "Program: " . $p->post_title . "\n";
    $uni_id = get_post_meta($p->ID, 'zh_university', true);
    echo "zh_university meta value: " . $uni_id . "\n";
    echo "University post Title: " . get_the_title($uni_id) . "\n";
    echo "Is Featured via service: " . (class_exists('\SIT\Search\Services\FeaturedUniversity') ? (new \SIT\Search\Services\FeaturedUniversity())->isFeatured((int)$uni_id) : 'skip') . "\n";
    echo "--------------------------\n";
}

$uni = get_posts([
    'post_type' => 'sit-university',
    's' => 'Bahcesehir',
    'posts_per_page' => 5
]);

echo "\n=== UNIVERSITIES NAMED BAHCESEHIR ===\n";
foreach ($uni as $u) {
    echo $u->ID . " : " . $u->post_title . "\n";
}
