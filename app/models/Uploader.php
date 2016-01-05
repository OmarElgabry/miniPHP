<?php

 /**
  * Uploader Class
  *
  * Main class for uploading, deleting files & directories
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class Uploader{

    /**
     * The mime types allowed for upload
     *
     * @var array
     */
    private static $allowedMIME = [
        "image" => array('image/jpeg', 'image/png', 'image/gif'),
        "csv"   => array('text/csv', 'application/vnd.ms-excel', 'text/plain'),
        "file"  => array('application/msword',
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                         'application/pdf',
                         'application/zip',
                         'application/vnd.ms-powerpoint')
    ];

    /**
     * The min and max image size allowed for upload (in bytes)
     * 1 KB = 1024 bytes, and 1 MB = 1048,576 bytes.
     *
     * @var array
     */
    private static $fileSize = [100, 5242880];

    /**
     * The max height and width image allowed for image
     *
     * @var array
     */
    private static $dimensions = [2000, 2000];

    /**
     * Array of validation errors
     *
     * @var array
     */
    private static $errors = [];

    /***
     * @access private
     */
    private function __construct() {}

    /**
     * upload profile picture
     *
     * @param  array   $file
     * @param  mixed   $id random id used in creating filename
     * @return mixed   false in case of failure, otherwise array of file created
     *
     */
    public static function uploadPicture($file, $id){
        self::$fileSize = [100, 2097152];
        return self::upload($file, IMAGES . "profile_pictures/", $id, "image");
    }

    /**
     * upload a file - default
     *
     * @param  array    $file
     * @param  mixed    $id random id used for creating filename
     * @return mixed    false in case of failure, array otherwise
     *
     */
    public static function uploadFile($file, $id = null){
        return self::upload($file, APP . "uploads/" , $id);
    }

    /**
     * upload a CSV File
     *
     * @param  array    $file
     * @return mixed    false in case of failure, array otherwise
     *
     */
    public static function uploadCSV($file){
        return self::upload($file, APP . "uploads/", null, "csv");
    }

    /**
     * upload & validate file
     *
     * @param  array    $file
     * @param  string   $dir directory where we will upload the file
     * @param  mixed    $id random id used for creating filename
     * @param  string   $type it tells whether the file is image, csv, or normal file(default).
     * @return mixed    false in case of failure, array otherwise
     * @throws Exception If file couldn't be uploaded
     *
     */
    private static function upload($file, $dir, $id, $type = "file"){

        $mimeTypes  = self::getAllowedMime($type);

        $validation = new Validation();
        $rules = "required|fileErrors|fileUploaded|mimeType(".Utility::commas($mimeTypes).")|fileSize(".Utility::commas(self::$fileSize).")";
        $rules = ($type === "image")? $rules . "|imageSize(".Utility::commas(self::$dimensions).")": $rules;

        if(!$validation->validate([
            "File" => [$file, $rules]], true)){
            self::$errors = $validation->errors();
            return false;
        }

        if($type === "csv"){

            // you need to add the extension in case of csv files,
            // because mime() will return text/plain.
            $basename = "grades" . "." . "csv";
            $path = $dir . $basename;

            $data = ["basename" => $basename, "extension" => "csv"];

        } else {

            if(!empty($id)){

                // get safe filename
                $filename = self::getFileName($file);

                // mime mapping to extension
                $ext = self::MimeToExtension(self::mime($file));

                // get hashed version using the given $id
                // the $id is used to have a unique file name
                // so, for example you would use it for profile picture,
                // because every user can have only one picture
                $hashedFileName = self::getHashedName($id);

                $basename = $hashedFileName . "." . $ext;
                $path = $dir . $basename;

                // delete all files with the same name, but with different formats.
                // not needed, but i like to clear unnecessary files
                self::deleteFiles($dir . $hashedFileName, $mimeTypes);

                $data = ["filename" => $filename, "basename" => $basename, "hashed_filename" => $hashedFileName, "extension" => $ext];

            } else {

                $filename = self::getFileName($file);
                $ext = self::MimeToExtension(self::mime($file));

                // hashed file name is created from the original filename and extension
                // so uploading test.pdf & test.doc won't conflict,
                // but, uploading file with test.pdf will return "file already exists"
                $hashedFileName = self::getHashedName(strtolower($filename . $ext));

                $basename = $hashedFileName . "." . $ext;
                $path = $dir . $basename;

                if(!$validation->validate(["File" => [$path, "fileUnique"]])) {
                    self::$errors = $validation->errors();
                    return false;
                }

                $data = ["filename" => $filename, "basename" => $basename, "hashed_filename" => $hashedFileName, "extension" => $ext];
            }
        }

        // upload the file.
        if(!move_uploaded_file($file['tmp_name'], $path)){
            throw new Exception("File couldn't be uploaded");
        }

        // set 644 permission to avoid any executable files
        if(!chmod($path, 0644)) {
            throw new Exception("File permissions couldn't be changed");
        }

        return $data;
    }

    /**
     * get mime type allowed from $allowedMIME
     *
     * @param string $key
     * @return array
     */
    private static function getAllowedMime($key){
        return isset(self::$allowedMIME[$key])? self::$allowedMIME[$key]: [];
    }

    /**
     * If you can have only one file name based on each user, Then:
     * Before uploading every new file, Delete all files with the same name and different extensions
     *
     * @param  string   $filePathWithoutExtension
     * @param  array    $allowedMIME
     *
     */
    private static function deleteFiles($filePathWithoutExtension, $allowedMIME){

        foreach($allowedMIME as $mime){
            $ext = self::MimeToExtension($mime);
            $path = $filePathWithoutExtension . "." . $ext;

            if(file_exists($path)){
                unlink($path);
            }
        }
    }

    /**
     * Deletes a file
     *
     * @param  string   $path
     * @throws Exception    File couldn't be deleted
     *
     */
    public static function deleteFile($path){
        if(file_exists($path)){
            if(!unlink($path)){
                throw new Exception("File ". $path ." couldn't be deleted");
            }
        } else {
            throw new Exception("File ". $path ." doesn't exist!");
        }
    }

    /**
     * create a directory with random hashed name
     *
     * @param  string       $dir
     * @return string
     * @throws Exception    If directory couldn't be created or If directory already exists!
     */
    public static function createDirectory($dir){

        $hashedDirName = self::getHashedName();
        $newDir = $dir . $hashedDirName;

        // create a directory if not exists
        if(!file_exists($newDir) && !is_dir($newDir)){
            if(mkdir($newDir, 0755) === false){
                throw new Exception("directory couldn't be created");
            }
        } else {
            throw new Exception("Directory: " .$hashedDirName. "already exists or directory given is invalid");
        }

        return $hashedDirName;
    }

    /**
     * Deletes a directory.
     *
     * @param  string     $dir
     * @throws Exception  If directory couldn't be deleted
     */
    public static function deleteDir($dir){
        if(!self::delTree($dir)){
            throw new Exception("Directory: " . $dir ." couldn't be deleted");
        }
    }

    /**
     * checks if file exists in the File System or not
     *
     * @param  string   $path
     * @return boolean
     *
     */
    public static function isFileExists($path){
       return file_exists($path) && is_file($path);
    }

    /**
     * deletes a directory recursively
     *
     * @param  string  $dir
     * @return boolean
     *
     */
    private static function delTree($dir) {

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != ".."){
                (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
            }
        }
        return rmdir($dir);
    }

    /**
     * get mime type of file
     *
     * Don't use either $_FILES["file"]["type"], or pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION,
     * Because their values aren't secure and can be easily be spoofed.
     *
     * @param   array  $file
     * @return  mixed  false if failed, string otherwise
     * @throws Exception if finfo_open() method doesn't exists
     *
     */
    private static function mime($file){

        if(!file_exists($file["tmp_name"])){
            return false;
        }
        if(!function_exists('finfo_open')) {
            throw new Exception("Function finfo_open() doesn't exist");
        }

        $finfo_open = finfo_open(FILEINFO_MIME_TYPE);
        $finfo_file = finfo_file($finfo_open, $file["tmp_name"]);
        finfo_close($finfo_open);

        list($mime) = explode(';', $finfo_file);
        return $mime;
    }

    /**
     * get hashed file name, and Optionally provided by an id
     *
     * @access private
     * @param   string  $id random id
     * @return  string  hashed file name
     *
     */
    private static function getHashedName($id = null){

        if($id === null) $id = time();
        return substr(hash('sha256', $id), 0, 40);
    }

    /**
     * Convert/Map the MIME of a file to extension
     *
     * @param   string  $mime
     * @return  string  extension
     *
     */
    private static function MimeToExtension($mime){
        $arr = array(
            'image/jpeg' => 'jpeg', // for both jpeg & jpg.
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/vnd.ms-powerpoint' => 'ppt'
        );
        return isset($arr[$mime])? $arr[$mime]: null;
    }

    /**
     * get file name
     * This ensures file name will be safe
     *
     * @param   array  $file
     * @return  string
     *
     */
    private static function getFileName($file){

        $filename = pathinfo($file['name'], PATHINFO_FILENAME);
        $filename = preg_replace("/([^A-Za-z0-9_\-\.]|[\.]{2})/", "", $filename);
        $filename = basename($filename);
        return $filename;
    }

    /**
     * get errors
     *
     * @return array errors
     */
    public static function errors(){
        return self::$errors;
    }

}
