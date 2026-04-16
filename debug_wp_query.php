<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$_GET['search'] = 'كمبيوتر';

// Let's copy the logic from FilterSort to see what $args looks like
$search = sanitize_text_field($_GET['search']);
$search_terms = !empty($search) ? \SIT\Search\Services\AiSearchHelper::expand_search($search) : [];
$search_program_ids = [];

if (!empty($search_terms)) {
    global $wpdb;
    $term_sql = [];
    foreach ($search_terms as $term) {
        $term_sql[] = $wpdb->prepare("meta_value LIKE %s", '%' . $wpdb->esc_like(trim($term)) . '%');
    }
    $or_clause = implode(' OR ', $term_sql);

    // 1. Find universities that match the AI terms
    $uni_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'Account_Name' AND ($or_clause)");
    
    $uni_condition = "";
    if (!empty($uni_ids)) {
        $escaped_uni_ids = implode(',', array_map('intval', $uni_ids));
        $uni_condition = " OR (meta_key = 'zh_university' AND meta_value IN ($escaped_uni_ids))";
    }

    // 2. Find all programs that either match the terms directly OR belong to matching universities
    $matched_programs_sql = "
        SELECT DISTINCT post_id FROM {$wpdb->postmeta} p 
        INNER JOIN {$wpdb->posts} posts ON posts.ID = p.post_id 
        WHERE posts.post_type = 'sit-program' AND posts.post_status = 'publish'
        AND (
            (meta_key = 'Product_Name' AND ($or_clause))
            $uni_condition
        )
    ";
    $search_program_ids = $wpdb->get_col($matched_programs_sql);
    
    if (empty($search_program_ids)) {
        // If AI terms found nothing, force zero results
        $search_program_ids = [-1];
    }
}

$meta_query = array('relation' => 'AND');

$args = array(
    'post_type'      => 'sit-program',
    'posts_per_page' => -1, // Fetch ALL
    'post_status'    => 'publish',
    'meta_query'     => $meta_query,
    'no_found_rows'  => false,
    'distinct'       => true, 
);

if (!empty($search_program_ids)) {
    $args['post__in'] = $search_program_ids;
}

echo "ARGS JSON: \n" . json_encode($args, JSON_PRETTY_PRINT) . "\n";

$query = new \WP_Query($args);
echo "FOUND POSTS: " . $query->found_posts . "\n";
