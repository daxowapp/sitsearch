<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$_GET['search'] = 'كمبيوتر';
$filterSort = new \SIT\Search\Shortcodes\FilterSort();
ob_start();
$filterSort->__invoke(); // Invoke the complete shortcode, bypassing the template render bug
$html = ob_get_clean();
echo "HTML LENGTH: " . strlen($html) . "\n";
echo "CONTAINS 'No programs found'? " . (strpos($html, 'No programs found') !== false ? 'YES' : 'NO') . "\n";
echo "PROGRAMS COUNT: " . substr_count($html, 'sit-program-card') . "\n";
