<?php

class Wads_Recommend_Data
{
    /**
     * Load Rating data
     *
     * @param string $file
     * @return array
     */
    public static function loadRatingData($file) {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($suffix) {
            case 'ini':
                return parse_ini_file($file, true);
                break;
            case 'php':
            case 'inc':
                include $file;
                if (!is_array($dataset)) {
                    require_once 'Wads/Recommend/Data/Exception.php';
                    throw new Wads_Recommend_Data_Exception('Invalid file was provided; PHP file needs to define $dataset array value');
                }
                return $dataset;
                break;
            default:
                require_once 'Wads/Recommend/Data/Exception.php';
                throw new Wads_Recommend_Data_Exception('Invalid file wads provided; unknown config type');
        }
    }
}
