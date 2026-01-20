<?php
require_once('wp-load.php');

$uni_id = 65909; // Antalya Bilim University
$target_speciality = 2679; // Dentistry
$target_degree = 2495; // Bachelor
$target_country = 2240; // Turkey

echo "Checking Programs for Uni ID: $uni_id\n";
echo "Looking for: Spec=$target_speciality, Degree=$target_degree, Country=$target_country\n\n";

$programs = get_posts([
    'post_type' => 'sit-program',
    'posts_per_page' => -1,
    'meta_key' => 'zh_university',
    'meta_value' => $uni_id,
    'post_status' => 'publish' // Ensure we only check published ones
]);

echo "Total Programs Found: " . count($programs) . "\n";
echo "---------------------------------------------------\n";

foreach ($programs as $p) {
    $specs = wp_get_post_terms($p->ID, 'sit-speciality', ['fields' => 'ids']);
    $degrees = wp_get_post_terms($p->ID, 'sit-degree', ['fields' => 'ids']);
    $countries = wp_get_post_terms($p->ID, 'sit-country', ['fields' => 'ids']);
    
    $is_spec_match = in_array($target_speciality, $specs);
    $is_degree_match = in_array($target_degree, $degrees);
    $is_country_match = in_array($target_country, $countries);
    
    if ($is_spec_match) {
        echo "[MATCH SPECIALITY] Program: " . $p->post_title . " (ID: $p->ID)\n";
        echo " - Specs: " . implode(', ', $specs) . "\n";
        echo " - Degrees: " . implode(', ', $degrees) . ($is_degree_match ? " [MATCH]" : " [FAIL]") . "\n";
        echo " - Countries: " . implode(', ', $countries) . ($is_country_match ? " [MATCH]" : " [FAIL]") . "\n";
        echo "\n";
    }
}
