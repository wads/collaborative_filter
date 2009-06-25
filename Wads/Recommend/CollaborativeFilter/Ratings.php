<?php
require_once 'Wads/Recommend/Data.php';

class Wads_Recommend_CollaborativeFilter_Ratings implements Iterator
{
    /**
     * @var string
     */
     protected $_name = null;

    /**
     * @var array
     */
    protected $_ratings = null;

    /**
     * @var int
     */
    protected $_size = 0;

    /**
     * @var array
     */
    protected $_keys = null;

    /**
     * constructor
     *
     * @param string|array $rating
     */
    public function __construct($name, $rating) {
        if(empty($name) || !is_string($name)) {
            require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
            throw new Wads_Recommend_CollaborativeFilterException("\$name must be string value");
        }
        $this->_name = $name;

        if(is_string($rating)) {
              Wads_Recommend_Data::loadRatingData($rating);
        }

        if(!is_array($rating)) {
            require_once 'Wads/Recommend/CollaborativeFilter/Exception.php';
            throw new Wads_Recommend_CollaborativeFilter_Exception("\$rating is invalid argument type");
        }

        foreach($rating as $k=>$v) {
            if(!is_numeric($v)) {
                unset($rating[$k]);
            }
        }

        if(empty($rating)) {
            $rating = array();
        }

        $this->_ratings = $rating;
        $this->_size = count($this->_ratings);
        $this->_keys = array_keys($this->_ratings);
    }

    /**
     * Implements Iterator
     */
    public function current() {
        if(($key = current($this->_keys)) === FALSE) {
            return $key;
        }

        return $this->_ratings[$key];
    }

    /**
     * Implements Iterator
     */
    public function key() {
       $key = current($this->_keys);

       if($key === FALSE) {
           return 0;
       }

       return $key;
    }

    /**
     * Implements Iterator
     */
    public function next() {
        next($this->_keys);
    }

    /**
     * Implements Iterator
     */
    public function rewind() {
        reset($this->_keys);
    }

    /**
     * Implements Iterator
     */
    public function valid() {
        return (boolean)current($this->_keys);
    }

    /**
     * Retreave ratings
     *
     * @param $key rating name
     * @return numeric
     */
    public function getValue($key) {
        if(!array_key_exists($key, $this->_ratings)) {
            return null;
        }

        return $this->_ratings[$key];
    }

    /**
     * Returns rating array data
     */
    public function toArray() {
        return $this->_ratings;
    }

    /**
     * Returns if contains specified key
     *
     * @param string $key
     * @return boolean
     */
    public function keyExists($key) {
        return array_key_exists($key, $this->_ratings);
    }

    /**
     * Returns name
     *
     * @return $string
     */
    public function getName() {
        return $this->_name;
    }
}