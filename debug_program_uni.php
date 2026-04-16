<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$uniid = get_post_meta(51811, 'zh_university', true);
echo "Uni ID for Program 51811 has postmeta zh_university = " . $uniid . "\n";

$active = [61572,61569,61421,61408,61391,60938,59517,58875,58873,58802,57810,57689,57553,57552,57551,57550,51779,51778,51777,51776,51775,51774,51773,51772,51771,51764,51763,51762,51761,51760,51759,51758,51757,51756,51755,51753,51752,51751,51750,51749,51748,51747,51746,51745,51744];

if (in_array($uniid, $active)) {
    echo "Is in active list YES\n";
} else {
    echo "Is in active list NO (Check if it's even a Turkey/NC university!)\n";
}

global $wpdb;
$matched_programs_sql = "
    SELECT DISTINCT post_id FROM {$wpdb->postmeta} p 
    INNER JOIN {$wpdb->posts} posts ON posts.ID = p.post_id 
    WHERE posts.post_type = 'sit-program' AND posts.post_status = 'publish'
    AND meta_key = 'Product_Name' AND meta_value LIKE '%computer science%'
";
$p_ids = $wpdb->get_col($matched_programs_sql);
echo "CS Programs count globally: " . count($p_ids) . "\n";

// How many of these belong to the active unis?
$valid_count = 0;
foreach ($p_ids as $pid) {
    if (in_array((int)get_post_meta($pid, 'zh_university', true), $active)) {
        $valid_count++;
    }
}
echo "CS Programs in Active Unis: " . $valid_count . "\n";
