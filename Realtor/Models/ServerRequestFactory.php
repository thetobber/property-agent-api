<?php
namespace Realtor\Models;

use InvalidArgumentException;
use Realtor\Models\Http\ServerRequest;
use Realtor\Models\Http\Uri;
use Realtor\Models\Http\UploadedFile;
use Realtor\Models\Http\Stream;

/**
* @todo
*/
class ServerRequestFactory
{
    /**
    * A few headers passed int the $_SERVER super global which is not prfiex with 
    * HTTP_
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
    * @todo
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
    * @todo
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
    * @todo
    */
    public static function getProtocolVersion()
    {
        return str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']);
    }

    /**
    * @todo
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
    * @todo
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
    * @todo
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
    * @todo
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
    * @todo
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