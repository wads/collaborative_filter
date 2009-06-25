<?php

require_once 'Wads/Recommend/CollaborativeFilter/Ratings.php';

interface Wads_Recommend_CollaborativeFilter_Similarity_Interface
{
    /**
     * Culc similarity score
     *
     * @param Wads_Recommend_CollaborativeFilter_Ratings $r1
     * @param Wads_Recommend_CollaborativeFilter_Ratings $r2
     * @return float
     */
    public function getScore(Wads_Recommend_CollaborativeFilter_Ratings $r1,
                             Wads_Recommend_CollaborativeFilter_Ratings $r2);
}
