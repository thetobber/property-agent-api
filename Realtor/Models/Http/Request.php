<?php
namespace Realtor\Models\Http;

use InvalidArgumentException;
use Realtor\Models\Http\MessageTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;

/**
* @link https://github.com/php-fig/http-message/blob/master/src/RequestInterface.php
*/
class Request extends MessageTrait implements RequestInterface
{
    /**
    * Array of all HTTP methods which is allowed for this 
    * implementation.
    *
    * @var array
    */
    const METHODS = array(
        'GET' => true,
        'POST' => true,
        /*
        I haven't written any parsers for these request methods
        
        'HEAD' => true,
        'PUT' => true,
        'PATCH' => true,
        'DELETE' => true,
        'PURGE' => true,
        'OPTIONS' => true,
        'TRACE' => true,
        'CONNECT' => true
        */
    );

    /**
    * Target of the requested resource. Would be the path of the Uri.
    *
    * @var string
    */
    protected $requestTarget;

    /**
    * The method used to request the resource e.g. GET or POST.
    *
    * @var string
    */
    protected $method;

    /**
    * The full Uri containing each individual part.
    *
    * @var UriInterface
    */
    protected $uri;

    public function __construct($method, $protocolVersion, UriInterface $uri, array $headers, StreamInterface $body)
    {
        $this->method = $this->checkMethod($method);
        $this->protocolVersion = $this->checkProtocolVersion($protocolVersion);
        $this->uri = $uri;
        $this->headerLines = $headers;
        $this->headers = array_change_key_case($headers);
        $this->body = $body;
    }

    /**
    * Ensures that all member variables containing an object is cloned with the 
    * instance of this request.
    */
    public function __clone()
    {
        $this->uri = clone $this->uri;
        $this->body = clone $this->body;
    }

    /**
    * @inherit
    */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $query = $this->uri->getQuery();

        $this->requestTarget = $this->uri->getPath().(empty($query) ?  '' : '?'.$query);

        return $this->requestTarget;
    }

    /**
    * @inherit
    */
    public function withRequestTarget($requestTarget)
    {
        $clone = clone $this;
        $clone->requestTarget = trim($requestTarget);

        return $clone;
    }

    /**
    * @inherit
    */
    public function getMethod()
    {
        return $this->method;
    }

    /**
    * @inherit
    */
    public function withMethod($method)
    {
        $method = $this->checkMethod($method);

        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /**
    * Ensures the used request method is supported by this implementation.
    *
    * @return string Returns the requested method.
    * @throws InvalidArgumentException in case of an unsupported request method.
    */
    protected function checkMethod($method)
    {
        $method = strtoupper($method);

        if (!isset(self::METHODS[$method])) {
            throw new InvalidArgumentException('Unsupported request method.');
        }

        return $method;
    }

    /**
    * @inherit
    */
    public function getUri()
    {
        return $this->uri;
    }

    /**
    * @inherit
    */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            $port = $uri->getPort();
            $host = $uri->getHost().($port === null ? '' : ':'.$port);

            if (!empty($host)) {
                $clone->header = array('host' => array($host)) + $clone->headers;
                $clone->headerLines = array('Host' => array($host)) + $clone->headersLines;
            }
        }

        return $clone;
    }
}
