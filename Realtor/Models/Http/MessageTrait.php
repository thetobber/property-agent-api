<?php
namespace Realtor\Models\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
* @link https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php
*/
abstract class MessageTrait implements MessageInterface
{
    /**
    * Array of accepted HTTP versions for this message
    * implementation.
    *
    * @var array
    */
    const PROTOCOL_VERSIONS = array(
        '1.0' => true,
        '1.1' => true,
        '2.0' => true
    );

    /**
    * HTTP version of the message.
    *
    * @var string
    */
    protected $protocolVersion = '1.1';

    /**
    * All HTTP headers of the message with lowercased keys.
    *
    * @var array
    */
    protected $headers; //Contains headers as lowercase

    /**
    * All HTTP headers of the message with their original casing.
    * Notice that headers fetched from the $_SERVER super global
    * will be formatted accordingly to the HTTP standard. This is
    * because headers from PHP is formatted to comply with the CGI
    * standards.
    *
    * @var array
    */
    protected $headerLines; //Headers with their original case

    /**
    * Body of the message as a stream.
    *
    * @var StreamInterface
    */
    protected $body;

    /**
    * @inherit
    */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
    * @inherit
    */
    public function withProtocolVersion($version)
    {
        $version = $this->checkProtocolVersion($version);

        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
    * Checkes whether the given protocol version is valid or not 
    * for use in this implementation.
    *
    * @param string $version The protocol version to check e.g. 1.0.
    * @return string Returns the $version if it was valid.
    * @throws InvalidArgumentException if it was not valid.
    */
    protected function checkProtocolVersion($version)
    {
        if (!isset(self::PROTOCOL_VERSIONS[$version])) {
            throw new InvalidArgumentException('Argument $version must be HTTP 1.0, 1.1 or 2.');
        }

        return $version;
    }

    /**
    * @inherit
    */
    public function getHeaders()
    {
        return $this->headerLines;
    }

    /**
    * @inherit
    */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
    * @inherit
    */
    public function getHeader($name)
    {
        $name = strtolower($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : array();
    }

    /**
    * @inherit
    */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);
        return empty($header) ? '' : implode(', ', $header);
    }

    /**
    * @inherit
    */
    public function withHeader($name, $value)
    {
        /*
        Matches one or more characters with subsequent groups of 
        characters prefixed with a hyphen. The whole string is 
        matched from start to end and is matched case insensitive.
        */
        if (preg_match('/^[a-z]+(?>-[a-z]+)*$/i', $name) === 0) {
            throw new InvalidArgumentException('Argument $name must be a valid HTTP header name.');
        }

        $clone = clone $this;
        $header = strtolower($name);

        if (is_string($value)) {
            $clone->headers[$header] = array($value);
        } elseif (is_array($value)) {
            $clone->headers[$header] = $value;

            /*foreach ($clone->headers[$header] as &$headerValue) {
                $headerValue = $value;
            }*/
        } else {
            throw new InvalidArgumentException('Argument $value must an array or string.');
        }

        foreach (array_keys($clone->headerLines) as $key) {
            if (strtolower($key) === $header) {
                unset($clone->headerLines[$key]);
            }
        }

        $clone->headerLines[$name] = $clone->headers[$header];

        return $clone;
    }

    /**
    * @inherit
    */
    public function withAddedHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $clone = clone $this;
        $clone->headers[strtolower($name)][] = $value;
        $clone->headerLines[$name][] = $value;

        return $clone;
    }

    /**
    * @inherit
    */
    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }

        $clone = clone $this;
        $header = strtolower($name);

        unset($clone->headers[$header]);

        foreach (array_keys($clone->headerLines) as $key) {
            if (strtolower($key) === $header) {
                unset($clone->headerLines[$key]);
            }
        }

        return $clone;
    }

    /**
    * @inherit
    */
    public function getBody()
    {
        return $this->body;
    }

    /**
    * @inherit
    */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
