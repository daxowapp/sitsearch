<?php
require_once('wp-load.php');
$programs = get_posts([
    'post_type' => 'sit-program',
    's' => 'Bahcesehir',
    'posts_per_page' => 5
]);
foreach ($programs as $p) {
    echo "Program: " . $p->post_title . "\n";
    $uni_id = get_post_meta($p->ID, 'zh_university', true);
    echo "zh_university meta value: " . $uni_id . "\n";
    echo "University post Title: " . get_the_title($uni_id) . "\n";
    echo "--------------------------\n";
}

$uni = get_posts([
    'post_type' => 'sit-university',
    's' => 'Bahcesehir',
    'posts_per_page' => 5
]);
echo "ALL universities named Bahcesehir:\n";
foreach ($uni as $u) {
    echo $u->ID . " : " . $u->post_title . "\n";
}
