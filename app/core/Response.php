<?php

/**
 * Response class.
 *
 * handles the response text, status and headers of a HTTP response.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Response {

    /**
     * @var array
     */
    public $headers;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $version;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $statusText;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $file = null;

    /**
     * @var array
     */
    private $csv = null;

    /**
     * Holds HTTP response statuses
     *
     * @var array
     */
    private $statusTexts = [
        200 => 'OK',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error'
    ];

    /**
     * Holds type key to mime type mappings for known mime types.
     *
     * @var array
     */
    private $mimeTypes = [
        'csv'  => ['text/csv', 'application/vnd.ms-excel'],
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  => 'application/pdf',
        'zip'  => 'application/zip',
        'ppt'  => 'application/vnd.ms-powerpoint'
    ];

    /**
     * Constructor.
     *
     * @param string $content The response content
     * @param int    $status  The response status code
     * @param array  $headers An array of response headers
     *
     */
    public function __construct($content = '', $status = 200, $headers = array()){

        $this->content = $content;
        $this->statusCode = $status;
        $this->headers = $headers;
        $this->statusText = $this->statusTexts[$status];
        $this->version = '1.0';
        $this->charset = 'UTF-8';
    }

    /**
     * Sends HTTP headers and content.
     *
     */
    public function send(){

        $this->sendHeaders();

        if ($this->file) {
            $this->readFile();
        } else if ($this->csv) {
            $this->writeCSV();
        } else {
            $this->sendContent();
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            $this->flushBuffer();
        }

        return $this;
    }

    /**
     * Flushes output buffers.
     *
     */
    private function flushBuffer(){
        // ob_flush();
        flush();
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    private function sendHeaders(){

        // check headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        // Content-Type
        // if Content-Type is already exists in headers, then don't send it
        if(!array_key_exists('Content-Type', $this->headers)){
            header('Content-Type: ' . 'text/html; charset=' . $this->charset);
        }

        // headers
        foreach ($this->headers as $name => $value) {
            header($name .': '. $value, true, $this->statusCode);
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    private function sendContent(){
        echo $this->content;
        return $this;
    }

    /**
     * Sets content for the current web response.
     * 
     * @param string $content The response content
     * @return Response
     */
    public function setContent($content = ""){
        $this->content = $content;
        return $this;
    }

    /**
     * Sets content for the current web response.
     * 
     * @param string|null $contentType The response content
     * @return Response
     */
    public function type($contentType = null){

        if($contentType === null){
            unset($this->headers['Content-Type']);
        }else{
            $this->headers['Content-Type'] = $contentType;
        }

        return $this;
    }

    /**
    * Stop execution of the current script.
    *
    * @param int|string $status
    * @return void
    * @see http://php.net/exit
    */
    public function stop($status = 0){
        exit($status);
    }

    /**
     * read file
     *
     * @return Response
     */
    private function readFile(){
        readfile($this->file);
        return $this;
    }

    /**
     * write to CSV file
     *
     * @return Response
     */
    private function writeCSV(){

        $cols = $this->csv["cols"];
        $rows = $this->csv["rows"];

        $out = fopen("php://output", 'w');

        fputcsv($out, $cols, ',', '"');
        foreach($rows as $row) {
            fputcsv($out, array_values($row), ',', '"');
        }

        fclose($out);
        return $this;
    }

    /**
     * Sets the response status code & it's relevant text.
     *
     * @param int $code HTTP status code
     * @return Response
     */
    public function setStatusCode($code){

        $this->statusCode = (int) $code;
        $this->statusText = isset($this->statusTexts[$code]) ? $this->statusTexts[$code] : '';

        return $this;
    }

    /**
     * Returns the mime type definition for an alias
     *
     * @param string $key
     * @return mixed
     */
    private function getMimeType($key){

        if (isset($this->mimeTypes[$key])) {
            $mime = $this->mimeTypes[$key];
            return  is_array($mime) ? current($mime) : $mime;
        }
        return false;
    }

    /**
     * Clean (erase) the output buffer
     *
     * @return void
     */
    public function clearBuffer(){
	
		// check if output_buffering is active
		if(ob_get_level() > 0){
			return ob_clean();
		}
    }

    /**
     * download a file
     *
     * @param  string $path
     * @param  array  $file
     * @param  array  $headers
     * @return Response
     */
    public function download($path, array $file, array $headers = []){

        $this->file = $path;
        $this->setStatusCode(200);

        if(empty($headers)){

            $mime = $this->getMimeType($file["extension"]);
            if(!$mime){
                $mime = "application/octet-stream";
            }

            $headers = [
                'Content-Description' => 'File Transfer',
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="'. $file["basename"].'"',
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public',
                'Content-Transfer-Encoding' => 'binary',
                'Content-Length' => filesize($path)
            ];
        }

        $this->headers = $headers;
        $this->clearBuffer();
        return $this;
    }

    /**
     * download CSV file
     * This will create csv file using $csvData
     *
     * @param  array  $csvData
     * @param  array  $file
     * @return Response
     * @see    core/Response/writeCSV()
     */
    public function csv(array $csvData, array $file){

        $this->csv = $csvData;
        $this->setStatusCode(200);

        $basename = $file["filename"] . ".csv";
        $headers  = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'. $basename.'"'
        ];

        $this->headers = $headers;
        $this->clearBuffer();
        return $this;
    }
} 
