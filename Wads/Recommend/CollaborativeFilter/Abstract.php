<?php

require_once 'Wads/Recommend/CollaborativeFilter/Similarity/Interface.php';
require_once 'Wads/Recommend/CollaborativeFilter/Ratings.php';
require_once 'Wads/Recommend/Data.php';

class Wads_Recommend_CollaborativeFilter_Abstract
{
    /**
     * @var Wads_Recommend_CollaborativeFilter_Similarity_Interface
     */
    protected $_sim;

    /**
     * @var array
     */
    protected $_score;

    /**
     * constructor
     *
     * @param Wads_Recommend_CollaborativeFilter_Similarity_Interface $sim
     */
    public function __construct(Wads_Recommend_CollaborativeFilter_Similarity_Interface $sim) {
        $this->_score = array();
        $this->setSimilarity($sim);
    }

    /**
     * Returns similarity
     *
     * @return Wads_Recommend_CollaborativeFilter_Similarity_Interface
     */
    public function getSimilarity() {
        return $this->_sim;
    }

    /**
     * Set similarity
     *
     * @param Wads_Recommend_CollaborativeFilter_Similarity_Interface $sim
     */
    public function setSimilarity(Wads_Recommend_CollaborativeFilter_Similarity_Interface $sim) {
        $this->_sim = $sim;
    }

    /**
     * Returs similarity score
     *
     * @param Wads_Recommend_CollaborativeFilter_Ratings $r1
     * @param Wads_Recommend_CollaborativeFilter_Ratings $r2
     * @return float
     */
    public function getSimilarityScore(Wads_Recommend_CollaborativeFilter_Ratings $r1,
                                       Wads_Recommend_CollaborativeFilter_Ratings $r2) {
        $key = $this->_genScoreKey($r1->getName(), $r2->getName());
        if(isset($this->_score[$key])) {
            return $this->_score[$key];
        }

        $score = $this->_sim->getScore($r1, $r2);
        $this->_score[$key] = $score;

        return $score;
    }

    /**
     * Returs similarity scores
     *
     * @param array|string $user
     * @param array|string $ratings
     * @return array
     */
    public function getSimilarityScores($user, $ratings) {
        $similarities = array();

        list($user, $ratings) = $this->_prepareDataSet($user, $ratings);

        $r1 = new Wads_Recommend_CollaborativeFilter_Ratings(key($user), current($user));
        foreach($ratings as $name=>$rating) {
            if(!is_string($name)) {
                require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
                throw new Wads_Recommend_CollaborativeFilter_Exception('Invalid rating data name.');
            }

            if(!is_array($rating)) {
                require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
                throw new Wads_Recommend_CollaborativeFilter_Exception('Invalid rating data.');
            }

            $r2 = new Wads_Recommend_CollaborativeFilter_Ratings($name, $rating);

            $similarities[$name] = $this->getSimilarityScore($r1, $r2);
        }
        arsort($similarities);

        return $similarities;
    }

    /**
     * Returns recommendation items
     *
     * @param array|string $user
     * @param array|string $ratings
     * @return array
     */
    public function getRecommendations($user, $ratings) {
        list($user, $ratings) = $this->_prepareDataSet($user, $ratings);

        $similarities = $this->getSimilarityScores($user, $ratings);

        $item_name_list = array();
        foreach($ratings as $name=>$rating) {
            $item_name_list = array_merge($item_name_list, array_keys($rating));
        }
        $item_name_list = array_diff(array_unique($item_name_list), array_keys(current($user)));

        $wavg = array();
        foreach($item_name_list as $item) {
            $wavg[$item] = $this->_getWeightedAvarage($item, $ratings, $similarities);
        }
        arsort($wavg);

        return $wavg;
    }

    /**
     * Returns weighted avarage
     *
     * @param string $key
     * @param array  $ratings
     * @param array  $similarities
     * @return float
     *
     */
    protected function _getWeightedAvarage($key, $ratings, $similarities) {
        $dataset = array();
        foreach($ratings as $name => $rating) {
            if(isset($rating[$key])) {
                $dataset[$name] = array(
                    'rating' => $rating[$key],
                    'weight' => $similarities[$name]
                );
            }
        }

        return (float)$this->_calcWeightedAvarage($dataset);
    }

    /**
     * Culc weighted avarage
     *
     * @param array dataset
     * @return float
     *
     * ex.
     * $dataset = array(
     *     'name' => array(
     *         'rating' => $rating_value,
     *         'weight' => $weight_value
     *     ),
     *     ...
     * );
     */
    protected function _calcWeightedAvarage($dataset) {
        $numerator = 0;
        $denominator = 0;
        foreach($dataset as $name=>$data) {
            $numerator += $data['rating']*$data['weight'];
            $denominator += $data['weight'];
        }

        if($denominator == 0) {
            return 0.;
        }

        return (float)($numerator/$denominator);
    }

    /**
     * Generate score key name
     *
     * @param string $n1
     * @param string $n2
     * @return string
     */
    protected function _genScoreKey($n1, $n2) {
        if(strcmp($n1, $n2) > 0) {
            $tmp = $n1;
            $n1 = $n2;
            $n2 = $tmp;
        }
        return sprintf('%s-%s', $n1, $n2);
    }

    /**
     * Load and Prepare dataset
     *
     * @param string|array $user
     * @param string|array $ratings
     * @return array
     */
    protected function _prepareDataSet($user, $ratings) {
        if(is_string($ratings)) {
            $ratings = Wads_Recommend_Data::loadRatingData($ratings);
        }

        if(!is_array($ratings)) {
            require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
            throw new Wads_Recommend_CollaborativeFilter_Exception('Invalid rating dataset is specified (observation user).');
        }

        if(is_string($user)) {
            if(!array_key_exists($user, $ratings)) {
                require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
                throw new Wads_Recommend_CollaborativeFilter_Exception("'$user' is not found in the dataset.");
            }

            $user = array($user=>$ratings[$user]);
            unset($ratings[key($user)]);
        }

        if(!is_array($user)) {
            require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
            throw new Wads_Recommend_CollaborativeFilter_Exception('Invalid rating dataset is specified(user data).');
        }

        if(count($user) > 1) {
            require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
            throw new Wads_Recommend_CollaborativeFilter_Exception('Duplicate user dataset.');
        }

        return array($user, $ratings);
    }
}
