<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');
$terms = \SIT\Search\Services\AiSearchHelper::expand_search('كمبيوتر');
echo "TERMS: " . json_encode($terms) . "\n";
