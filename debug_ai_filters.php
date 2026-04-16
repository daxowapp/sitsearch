<?php
require_once('/Users/darwish/Dev/sitsearch/wp-load.php');

$queries = [
    "ماجستير اداره اعمال",
    "كمبيوتر بالتركي",
    "طب في اسطنبول",
    "engineering in english"
];

foreach ($queries as $q) {
    echo "Query: $q\n";
    $result = \SIT\Search\Services\AiSearchHelper::expand_search($q);
    print_r($result);
    echo "-------------------\n";
}
