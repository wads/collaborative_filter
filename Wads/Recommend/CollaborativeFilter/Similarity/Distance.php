<?php
/**
 * Culc score based on Euclidean distance
 *
 *=========================================
 *                  1
 *       -----------------------
 *        1 + √(∑(u1 - u2)^2)
 *=========================================
 */

require_once 'Wads/Recommend/CollaborativeFilter/Similarity/Interface.php';

class Wads_Recommend_CollaborativeFilter_Similarity_Distance
         implements Wads_Recommend_CollaborativeFilter_Similarity_Interface
{
    /**
     * Culc similarity score
     *
     * @param Wads_Recommend_CollaborativeFilter_Ratings $r1
     * @param Wads_Recommend_CollaborativeFilter_Ratings $r2
     * @return float
     */
    public function getScore(Wads_Recommend_CollaborativeFilter_Ratings $r1,
                             Wads_Recommend_CollaborativeFilter_Ratings $r2) {
        $sum_score = 0;
        $match_cnt = 0;

        foreach($r1 as $key=>$r1val) {
            if($r2->keyExists($key)) {
                $r2val = $r2->getValue($key);
                $sum_score += pow(($r1val - $r2val), 2);
                $match_cnt++;
            }
        }

        if($match_cnt == 0) {
            return 0.;
        }

        return  (float)1/(1 + sqrt($sum_score));
    }
}
