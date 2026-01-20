<?php
require_once('wp-load.php');

// 1. Check University Duplicates
echo "--- UNIVERSITY CHECK ---\n";
$unis = get_posts([
    'post_type' => 'sit-university',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
]);

echo "Total Universities Found: " . count($unis) . "\n";
$seen_titles = [];
foreach ($unis as $u) {
    echo "ID: {$u->ID} | Title: {$u->post_title} | Status: {$u->post_status}\n";
    if (isset($seen_titles[$u->post_title])) {
        echo "   >>> DUPLICATE TITLE DETECTED! (Previous ID: {$seen_titles[$u->post_title]})\n";
    }
    $seen_titles[$u->post_title] = $u->ID;
}

// 2. Check Programs Status
echo "\n--- PROGRAM CHECK ---\n";
$total_programs = wp_count_posts('sit-program');
echo "Total Published Programs: " . $total_programs->publish . "\n";

// 3. Search for Antalya-related programs broadly
echo "\n--- SEARCH ANTALYA/BILIM PROGRAMS ---\n";
$p_query = new WP_Query([
    'post_type' => 'sit-program',
    's' => 'Antalya',
    'posts_per_page' => 10
]);
if ($p_query->have_posts()) {
    foreach($p_query->posts as $p) {
        $uni_id = get_post_meta($p->ID, 'zh_university', true);
        echo "Program: {$p->post_title} (ID: {$p->ID}) -> UniID: $uni_id\n";
    }
} else {
    echo "No programs found matching 'Antalya'.\n";
}
