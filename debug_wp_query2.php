<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$_GET['search'] = 'كمبيوتر';
$filterSort = new \SIT\Search\Shortcodes\FilterSort();
$args = $filterSort->get_query();
