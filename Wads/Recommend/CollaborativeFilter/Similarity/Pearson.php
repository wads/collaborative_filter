<?php
/**
 * Culc score based on Pearson correlation
 *
 * ============================================
 *  Cov($r1, $r2) / σ($r1) * σ($r2)
 * ============================================
 */

require_once 'Wads/Recommend/CollaborativeFilter/Similarity/Interface.php';

class Wads_Recommend_CollaborativeFilter_Similarity_Pearson
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
        $ckeys = array();

        foreach($r1 as $key=>$val) {
            if($r2->keyExists($key)) {
                $ckeys[] = $key;
            }
        }

        $len = count($ckeys);
        if($len == 0) {
            return 0;
        }

        $sum1 = $sum2 = 0;
        $sum1Sq = $sum2Sq = 0;
        $sum12  = 0;

        foreach($ckeys as $key) {
            $p1 = $r1->getValue($key);
            $p2 = $r2->getValue($key);

            $sum1 += $p1;
            $sum2 += $p2;

            $sum1Sq += pow($p1, 2);
            $sum2Sq += pow($p2, 2);

            $sum12 += $p1 * $p2;
        }

        // Culc Cov
        $num = (float)($sum12 - ($sum1 * $sum2 / $len));

        // σ($r1) * σ($r2)
        $den = sqrt(($sum1Sq - (pow($sum1, 2) / $len)) * ($sum2Sq - (pow($sum2, 2) / $len)));
        if($den == 0) {
            return 0;
        }

        return (float) ($num / $den);
    }
}