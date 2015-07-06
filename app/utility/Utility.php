<?php

/**
 * Utility class.
 *
 * Provides methods for manipulating and extracting data from arrays.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Utility{

    private function __construct(){}

    /**
     * Normalizes an array, and converts it to a standard format.
     *
     * @param  array $arr
     * @return array normalized array
     */
    public static function normalize($arr){

        $keys = array_keys($arr);
        $count = count($keys);

        $newArr = [];
        for ($i = 0; $i < $count; $i++) {
            if (is_int($keys[$i])) {
                $newArr[$arr[$keys[$i]]] = null;
            } else {
                $newArr[$keys[$i]] = $arr[$keys[$i]];
            }
        }
        return $newArr;
    }

    /**
     * returns a string by separating array elements with commas
     *
     * @param  array $arr
     * @return array
     */
    public static function commas($arr){
        return implode(",", (array)$arr);
    }

    /**
     * Merging two arrays
     *
     * @param  mixed   $arr1
     * @param  mixed   $arr2
     * @return array   The merged array
     *
     */
    public static function merge($arr1, $arr2){
        return array_merge((array)$arr1, (array)$arr2);
    }

 }
