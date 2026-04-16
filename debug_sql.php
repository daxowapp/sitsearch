<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$search_terms = ['computer science', 'software engineering', 'information technology'];
global $wpdb;

$term_sql = [];
foreach ($search_terms as $term) {
    if (is_array($term)) {
       echo "TERM IS ARRAY!\n";
       continue;
    }
    $term_sql[] = $wpdb->prepare("meta_value LIKE %s", '%' . $wpdb->esc_like(trim($term)) . '%');
}
$or_clause = implode(' OR ', $term_sql);

$uni_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'Account_Name' AND ($or_clause)");
echo "Uni IDs: " . json_encode($uni_ids) . "\n";

$uni_condition = "";
if (!empty($uni_ids)) {
    $escaped_uni_ids = implode(',', array_map('intval', $uni_ids));
    $uni_condition = " OR (meta_key = 'zh_university' AND meta_value IN ($escaped_uni_ids))";
}

$matched_programs_sql = "
    SELECT DISTINCT post_id FROM {$wpdb->postmeta} p 
    INNER JOIN {$wpdb->posts} posts ON posts.ID = p.post_id 
    WHERE posts.post_type = 'sit-program' AND posts.post_status = 'publish'
    AND (
        (meta_key = 'Product_Name' AND ($or_clause))
        $uni_condition
    )
";
echo "SQL: \n" . $matched_programs_sql . "\n\n";

$search_program_ids = $wpdb->get_col($matched_programs_sql);
echo "Program IDs: " . json_encode($search_program_ids) . "\n";
