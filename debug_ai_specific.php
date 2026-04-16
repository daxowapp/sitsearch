<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$query = "كمبيوتر بالتركي";
echo "Testing Query: " . $query . "\n";
echo "AI Response: \n";
$result = \SIT\Search\Services\AiSearchHelper::expand_search($query);
print_r($result);

// Also look at WP_Query results for this
$args = [
    'post_type' => 'sit-program',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'meta_query' => ['relation' => 'AND']
];

if (!empty($result) && is_array($result)) {
    global $wpdb;
    $search_conditions = [];
    foreach ($result as $term) {
        $like_term = '%' . $wpdb->esc_like($term) . '%';
        $search_conditions[] = $wpdb->prepare("meta_value LIKE %s", $like_term);
    }
    
    if (!empty($search_conditions)) {
        $where_clause = implode(' OR ', $search_conditions);
        $matched_programs_sql = "
            SELECT DISTINCT post_id FROM {$wpdb->postmeta} p 
            INNER JOIN {$wpdb->posts} posts ON posts.ID = p.post_id 
            WHERE posts.post_type = 'sit-program' AND posts.post_status = 'publish'
            AND meta_key = 'Product_Name' AND ($where_clause)
        ";
        $search_program_ids = $wpdb->get_col($matched_programs_sql);
        echo "\nMatched program IDs count: " . count($search_program_ids) . "\n";
        
        $active_unis = [61572,61569,61421,61408,61391,60938,59517,58875,58873,58802,57810,57689,57553,57552,57551,57550,51779,51778,51777,51776,51775,51774,51773,51772,51771,51764,51763,51762,51761,51760,51759,51758,51757,51756,51755,51753,51752,51751,51750,51749,51748,51747,51746,51745,51744];
        $valid_count = 0;
        foreach ($search_program_ids as $pid) {
            $uniid = (int)get_post_meta($pid, 'zh_university', true);
            if (in_array($uniid, $active_unis)) {
                $valid_count++;
            }
        }
        echo "Active Matched count: " . $valid_count . "\n";
    }
}
