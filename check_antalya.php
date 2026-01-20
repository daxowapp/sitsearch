<?php
require_once('wp-load.php');

echo "--- CHECKING 'Antalya Bilim University' ---\n";

$unis = get_posts([
    'post_type' => 'sit-university',
    'title' => 'Antalya Bilim University',
    'post_status' => 'any',
    'posts_per_page' => -1
]);

if (empty($unis)) {
    // Fallback search
    $unis = get_posts([
        'post_type' => 'sit-university',
        's' => 'Antalya Bilim',
        'post_status' => 'any',
        'posts_per_page' => -1
    ]);
}

foreach ($unis as $u) {
    echo "ID: {$u->ID} | Title: {$u->post_title}\n";
    
    // Status
    $active_search = get_post_meta($u->ID, 'Active_in_Search', true);
    $active_new = get_post_meta($u->ID, 'Active_in_New_Apps', true);
    echo "  Status -> Search: " . ($active_search ? 'ACTIVE (1)' : 'INACTIVE (0)') . " | NewApps: " . ($active_new ? 'ACTIVE (1)' : 'INACTIVE (0)') . "\n";
    
    // Language
    $lang = '-';
    if (function_exists('pll_get_post_language')) {
        $lang = pll_get_post_language($u->ID);
    }
    echo "  Language: $lang\n";
    
    // Programs
    $prog_count = count(get_posts([
        'post_type' => 'sit-program',
        'meta_key' => 'zh_university',
        'meta_value' => $u->ID,
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]));
    echo "  Linked Programs: $prog_count\n";
    
    if ($prog_count == 0) echo "  >>> WARNING: No programs linked! This is why it is hidden.\n";
    echo "--------------------------------------------------\n";
}
