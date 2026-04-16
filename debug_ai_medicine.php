<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');

$query = "طب في اسطنبول";
echo "Query: $query\n";
$result = \SIT\Search\Services\AiSearchHelper::expand_search($query);
print_r($result);

