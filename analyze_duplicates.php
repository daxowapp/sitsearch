<?php
require_once('wp-load.php');

echo "--- DUPLICATE ANALYSIS ---\n";

// Fetch all universities (suppress filters via WP_Query raw or get_posts with lang='')
$unis = get_posts([
    'post_type' => 'sit-university',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'lang' => '' // Polylang bypass
]);

echo "Total Universities: " . count($unis) . "\n";

$by_zoho = [];
$missing_supabase = 0;

foreach ($unis as $u) {
    $z_id = get_post_meta($u->ID, 'zoho_account_id', true);
    $s_id = get_post_meta($u->ID, 'supabase_id', true);
    
    if ($z_id) {
        if (!isset($by_zoho[$z_id])) $by_zoho[$z_id] = [];
        $by_zoho[$z_id][] = [
            'id' => $u->ID,
            'title' => $u->post_title,
            'has_supabase' => !empty($s_id),
            'date' => $u->post_date
        ];
    }
    
    if (empty($s_id)) $missing_supabase++;
}

echo "Universities missing 'supabase_id': $missing_supabase\n";

$duplicate_groups = 0;
foreach ($by_zoho as $zid => $posts) {
    if (count($posts) > 1) {
        $duplicate_groups++;
        echo "\nDuplicate Group (ZohoID: $zid):\n";
        foreach ($posts as $p) {
            echo " - ID: {$p['id']} | Title: {$p['title']} | Date: {$p['date']} | Has SupabaseID: " . ($p['has_supabase'] ? 'YES' : 'NO') . "\n";
        }
    }
}

if ($duplicate_groups == 0) {
    echo "\nNo duplicates found based on Zoho ID.\n";
} else {
    echo "\nFound $duplicate_groups groups of duplicates.\n";
}
