<?php
namespace PropertyAgent\Models\Http;

use InvalidArgumentException;
use PropertyAgent\Models\Http\Stream;
use PropertyAgent\Models\Http\Uri;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
* @link https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php
*/
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
    * Contains all the server parameters which is (probably) derived from 
    * the PHP $_SERVER super global.
    *
    * @var array
    */
    protected $serverParams;

    /**
    * All cookies passed from the client to the server which is (probably) 
    * fetched from the PHP $_COOKIE super global.
    *
    * @var array
    */
    protected $cookieParams;

    /**
    * Contains the parsed query parameters of the Uri if any is passed.
    *
    * @var array
    */
    protected $queryParams;

    /**
    * Contains an array of UploadedFile objects if any files are uploaded 
    * by the client.
    *
    * @var array
    */
    protected $uploadedFiles;

    /**
    * Contains an array of attributes which can be associated with the incoming 
    * request to the server. This could for example be parameters set from parsing 
    * a route.
    *
    * @var array
    */
    protected $attributes;

    /**
    * Contains the body parsed body of the incoming request. The body is only parsed 
    * upon calling the parseBody() method.
    *
    * @var array
    */
    protected $parsedBody;

    /**
    * A flag which is set after the body has been parsed to avoid repeatedly parsing the 
    * body if retrieved multiple times.
    *
    * @var boolean
    */
    protected $isBodyParsed;

    public function __construct(
        $method, $protocolVersion, UriInterface $uri, array $headers,
        StreamInterface $body, array $serverParams, array $cookieParams,
        array $queryParams, array $uploadedFiles, array $attributes
    )
    {
        parent::__construct($method, $protocolVersion, $uri, $headers, $body);

        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->attributes = $attributes;
    }

    /**
    * @inherit
    */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
    * @inherit
    */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
    * @inherit
    */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    /**
    * @inherit
    */
    public function getQueryParams()
    {
        if (is_array($this->queryParams)) {
            return $this->queryParams;
        }

        if ($this->uri === null) {
            return array();
        }

        parse_str($this->uri->getQuery(), $this->queryParams);

        return $this->queryParams;
    }

    /**
    * @inherit
    */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;
    }

    /**
    * @inherit
    */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
    * @inherit
    */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
    * @inherit
    */
    public function getParsedBody()
    {
        if ($this->isBodyParsed) {
            return $this->parsedBody;
        }

        if ($this->body === null) {
            return null;
        }

        $this->parsedBody = $this->parseBody();
        $this->isBodyParsed = true;

        return $this->parsedBody;
    }

    protected function parseBody()
    {
        $contents = (string) $this->body;
        $mediaType = $this->getHeader('content-type');

        if (empty($mediaType)) {
            return $contents;
        }

        if ($mediaType == 'application/x-www-form-urlencoded') {
            $parsed = array();
            parse_str($contents, $parsed);

            return $parsed;
        }

        if (strpos($mediaType, 'multipart/form-data') !== false) {
            return $_POST;
        }

        if ($mediaType == 'application/json' || $mediaType == 'text/json') {
            return json_decode($contents, true);
        }

        return $contents;
    }

    /**
    * @inherit
    */
    public function withParsedBody($data)
    {
        if ($data !== null && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException('Parsed body value must be an array, an object, or null');
        }

        $clone = clone $this;
        $clone->bodyParsed = $data;

        return $clone;
    }

    /**
    * @inherit
    */
    public function getAttributes()
    {
        return $this->$attributes;
    }

    /**
    * @inherit
    */
    public function getAttribute($name, $default = null)
    {
        $attribute = isset($this->attributes[$name]) ? $this->attributes[$name] : $default;

        return $attribute;
    }

    /**
    * @inherit
    */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes = array($name => $value) + $clone->attributes;

        return $clone;
    }

    /**
    * @inherit
    */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        
        if (isset($clone->attributes[$name])) {
            unset($clone->attributes[$name]);
        }

        return $clone;
    }
}
