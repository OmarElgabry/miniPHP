<?php

/**
 * Encryption and Decryption Class
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Encryption{

    /**
     * constructor for Encryption object.
     *
     * @access private
     */
    private function __construct(){}

    /**
     * Encrypt an id.
     * This method encrypt by converting to base 16, after some random multiply and additions.
     *
     * @access public
     * @static static method
     * @param  integer|string	$id
     * @return string
     */
    public static function encryptId($id){

        $enctypId = base_convert(($id * 9518436) + 1142, 10, 16);
        return $enctypId;
    }

    /**
     * Decryption for Id.
     * This method decrypt by converting to base 10, then division and subtraction.
     *
     * @access public
     * @static static method
     * @param  string	$id
     * @return integer
     */
    public static function decryptId($id){

        if(empty($id)){ return 0; }

        $decrypId = (int)base_convert($id, 16, 10);
        $decrypId = ($decrypId - 1142) / 9518436;

        return (int)$decrypId;
    }

    /**
     * Decryption for Ids with dash '-', Example: "feed-km1chg3"
     *
     * @access public
     * @static static method
     * @param  string	$id
     * @return integer
     */
    public static function decryptIdWithDash($id){

        if(empty($id)){ return 0; }

        $decrypId = explode("-", $id)[1];
        $decrypId = (int)base_convert($decrypId, 16, 10);
        $decrypId = ($decrypId - 1142) / 9518436;

        return (int)$decrypId ;
    }

    /**
     * Encrypt a string.
     *
     * @access public
     * @static static method
     * @param  string	$str
     * @return string
     */
    public static function encrypt($str){

        //choose cipher type and mode, must match the decrypt() method
        $cipher_type = MCRYPT_RIJNDAEL_256;
        $cipher_mode = MCRYPT_MODE_CBC;

        //initialization vector adds more security
        //this will make the $iv different every time
        //Thus, the encrypted string will be different for the same original string.
        $iv_size = mcrypt_get_iv_size($cipher_type, $cipher_mode);
        $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $encrypted_string = mcrypt_encrypt($cipher_type, ENCRYPTION_KEY, $str, $cipher_mode, $iv);

        //return initialization vector + encrypted string
        //because it's needed when decrypting.
        return base64_encode($iv . $encrypted_string);
    }

    /**
     * Decrypted a string.
     *
     * @access public
     * @static static method
     * @param  string	$str
     * @return string
     */
    public static function decrypt($str){

        $str = base64_decode($str);

        //choose cipher type and mode, must match the encrypt() method
        $cipher_type = MCRYPT_RIJNDAEL_256;
        $cipher_mode = MCRYPT_MODE_CBC;

        //get the initialization vector from the encrypted string.
        //The $iv has fixed size according to the cipher and mode used.
        $iv_size = mcrypt_get_iv_size($cipher_type, $cipher_mode);
        $iv      = substr($str, 0, $iv_size);
        $encrypted_string = substr($str, $iv_size);

        $string = rtrim(mcrypt_decrypt($cipher_type, ENCRYPTION_KEY, $encrypted_string, $cipher_mode, $iv), "\0");
        return $string;
    }
} 
