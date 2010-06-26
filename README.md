# CollaborativeFilter Lib

## Sample code

```php

<?php

require_once 'Wads/Recommend/CollaborativeFilter.php';
require_once 'Wads/Recommend/CollaborativeFilter/Similarity/Distance.php';
require_once 'Wads/Recommend/CollaborativeFilter/Similarity/Pearson.php';
require_once 'Wads/Recommend/CollaborativeFilter/Ratings.php';

/*
 * Input data
 * Ini file or array
 * Need to use $dataset variable name when use array
 */
//$ratings = 'data.inc';
$ratings = 'data.ini';

/* 計算方法 */
$method = array('distance', 'pearson');

/* 対象ユーザー */
$person = 'yamamoto';

foreach($method as $m) {
    $sim_class = "Wads_Recommend_CollaborativeFilter_Similarity_" . ucfirst(strtolower($m));
    $filter = new Wads_Recommend_CollaborativeFilter(new $sim_class);

    // 似ているユーザーを探す
    $res = $filter->getSimilarityScores($person, $ratings);
    _print($res, "Similarity ($m)", $person);

    // アイテムを推薦する
    $res = $filter->getRecommendations($person, $ratings);;
    _print($res, "Recommendations ($m)", $person);
}


function _print(array $res, $title, $user = 'noname') {
    echo "------------------------------\n";
    echo "[$user]'s $title\n";
    echo "------------------------------\n";

    foreach($res as $n=>$r) {
        echo "$n ($r)\n";
    }
    echo "\n";
}

```
