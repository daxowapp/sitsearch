<?php
require_once('wp-load.php');

$uni = get_page_by_title('Antalya Bilim University', OBJECT, 'sit-university');
if (!$uni) exit('Uni not found');

$meta = get_post_meta($uni->ID);
echo "Meta Keys for " . $uni->post_title . ":\n";
foreach($meta as $k => $v) {
    echo "[$k] => " . print_r($v[0], true) . "\n";
}
