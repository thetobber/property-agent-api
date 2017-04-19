<?php
namespace PropertyAgent\Models;

use InvalidArgumentException;
use PropertyAgent\Models\Http\ServerRequest;
use PropertyAgent\Models\Http\Uri;
use PropertyAgent\Models\Http\UploadedFile;
use PropertyAgent\Models\Http\Stream;

/**
* Defines a factory class which create an instance of server request 
* from the PHP super globals.
*/
class ServerRequestFactory
{
    /**
    * A few headers passed int the $_SERVER super global which is not 
    * prefixed with "HTTP_".
    *
    * @var array
    */
    const WEIRD_HEADERS = array(
        'CONTENT_TYPE' => 'Content-Type',
        'CONTENT_LENGTH' => 'Content-Length'
    );

    /**
    * This class is not supposed to be instantiated so therefore the constructor
    * is set to private preventing this.
    */
    private function __construct() {}

    /**
    * Return a new instance of serverrequest based on the PHP super 
    * globals.
    *
    * @return ServerRequest
    */
    public static function getServerRequest($serverRequestAttributes = array())
    {
        $serverRequestUri = self::getUri();
        $serverRequestQuery = array();

        parse_str(
            $serverRequestUri->getQuery(),
            $serverRequestQuery
        );

        return new ServerRequest(
            $_SERVER['REQUEST_METHOD'],
            self::getProtocolVersion(),
            $serverRequestUri,
            self::getHeaders($_SERVER),
            self::getBody(),
            self::getParams(),
            $_COOKIE,
            $serverRequestQuery,
            self::getUploadedFiles(),
            $serverRequestAttributes
        );
    }

    /**
    * Get all the variables in the $_SERVER super global which is not 
    * HTTP headers.
    *
    * @return array Returns an array of variables with information about 
    *   the server and request.
    */
    public static function getParams()
    {
        return array_filter(
            $_SERVER,
            function ($key) {
                return strpos($key, 'HTTP_') === false && !isset(self::WEIRD_HEADERS[$key]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
    * Get the HTTP protocol version of the request.
    *
    * @return string The procotol version.
    */
    public static function getProtocolVersion()
    {
        return str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']);
    }

    /**
    * Get all the upladed files (if any exist) and instantiate a new 
    * UploadedFile object for each file.
    *
    * @return array Returns an array of UploadedFile instances.
    */
    public static function getUploadedFiles()
    {
        $files = array();

        foreach ($_FILES as $file) {
            if (empty($file['tmp_name'])) {
                continue;
            }

            $files[] = new UploadedFile(
                $file['tmp_name'],
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type']
            );
        }

        return $files;
    }

    /**
    * Constructs and return the Uri of the request based on the PHP 
    * super globals.
    *
    * @return Uri Return the Uri to the request resource.
    */
    public static function getUri()
    {
        return new Uri(
            empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https',
            !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'],
            isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null,
            isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
            isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '',
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/',
            isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''
        );
    }

    /**
    * Creates a stream and copies the input stream (e.g. body of the request) 
    * to a Stream object and returns it.
    *
    * @return Stream Return the body of the incoming request.
    */
    public static function getBody()
    {
        $temp = fopen('php://temp', 'w+');

        stream_copy_to_stream(
            fopen('php://input', 'r'),
            $temp
        );

        rewind($temp);

        return new Stream($temp);
    }

    /**
    * Get all the headers which is contained in the $_SERVER super global.
    * These headers are then normalized to adhere with the HTTP standards 
    * over from the CGI standards.
    *
    * @return array Returns an array of all HTTP headers.
    */
    public static function getHeaders(array $server)
    {
        if (is_callable('apache_request_headers')) {
            return apache_request_headers();
        }

        $headers = array();

        foreach ($server as $key => $value) {
            if (isset(self::WEIRD_HEADERS[$key])) {
                $headers[self::WEIRD_HEADERS[$key]] = $value;
                continue;
            }

            if (($normalizedKey = self::normalizeKey($key, 'HTTP_')) !== null) {
                $headers[$normalizedKey] = $value;
            }
        }

        return $headers;
    }

    /**
    * Used for normalizing a HTTP header contained in the $_SERVER super global 
    * from the CGI standard to the HTTP standard.
    *
    * @param string $key The key to normalize.
    * @param string $prefix Prefix in the key used to identify if it should be normalized.
    * @return string|null Returns if the $prefix is not in the string.
    */
    public static function normalizeKey($key, $prefix = '')
    {
        if ($prefix !== '' && strpos($key, $prefix) !== 0) {
            return null;
        }

        return str_replace(
            ' ',
            '-',
            ucwords(
                str_replace('_', ' ',
                    substr(
                        strtolower($key),
                        strlen(strtolower($prefix))
                    )
                )
            )
        );
    }
}