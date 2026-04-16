<?php
require_once 'wp-load.php';
global $wpdb;

$programs = $wpdb->get_results("
    SELECT p.ID, p.post_title, pm.meta_value as zh_university 
    FROM wp_posts p 
    JOIN wp_postmeta pm ON p.ID = pm.post_id 
    WHERE p.post_title LIKE '%Dentistry%' 
    AND p.post_title LIKE '%Bahce%' 
    AND pm.meta_key = 'zh_university'
    LIMIT 5
");

print_r($programs);

// Also let's check Bahçeşehir University actual post ID
$unis = $wpdb->get_results("SELECT ID, post_title FROM wp_posts WHERE post_type = 'sit-university' AND post_title LIKE '%Bahce%'");
print_r($unis);

