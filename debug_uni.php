<?php
require_once('wp-load.php');

// 1. Find University
$uni = get_page_by_title('Antalya Bilim University', OBJECT, 'sit-university');
if (!$uni) {
    echo "ERROR: University 'Antalya Bilim University' not found.\n";
    exit;
}

echo "University Found: " . $uni->post_title . " (ID: " . $uni->ID . ")\n";

// 2. Check Meta
$active = get_post_meta($uni->ID, 'Active_in_Search', true);
$featured = get_post_meta($uni->ID, 'sit_is_featured', true);
$priority = get_post_meta($uni->ID, 'university_priority', true);
$new_priority = get_post_meta($uni->ID, 'sit_featured_priority', true);

echo "Active_in_Search: " . var_export($active, true) . "\n";
echo "Is Featured: " . var_export($featured, true) . "\n";
echo "Old Priority: " . var_export($priority, true) . "\n";
echo "New Priority: " . var_export($new_priority, true) . "\n";

// 3. Check Programs
$programs = get_posts([
    'post_type' => 'sit-program',
    'posts_per_page' => -1,
    'meta_key' => 'zh_university',
    'meta_value' => $uni->ID
]);

echo "Total Programs: " . count($programs) . "\n";

// 4. Check Filters (Speciality: 2679, Country: 2240, Level: 2495)
$speciality_id = 2679;
$country_id = 2240;
$degree_id = 2495;

$matching_programs = 0;
foreach ($programs as $p) {
    $terms = wp_get_post_terms($p->ID, ['sit-speciality', 'sit-country', 'sit-degree']);
    $has_spec = false;
    $has_country = false;
    $has_degree = false;

    foreach ($terms as $t) {
        if ($t->term_id == $speciality_id) $has_spec = true;
        if ($t->term_id == $country_id) $has_country = true; // Note: Country is usually on Uni, but synced to Program?
        if ($t->term_id == $degree_id) $has_degree = true;
    }
    
    // Country logic usually checks University country, but let's check Program terms too
    // The main search logic checks taxonomies on the PROGRAM post type.
    
    if ($has_spec && $has_degree) {
        $matching_programs++;
    }
}

echo "Programs matching Speciality($speciality_id) AND Degree($degree_id): $matching_programs\n";
