<?php
namespace PropertyAgent\Models\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
* @link https://github.com/php-fig/http-message/blob/master/src/UriInterface.php
*/
class Uri implements UriInterface
{
    // Uri structure
    // <scheme>://<authority>[/<path>][?<query string>]

    /**
    * The accepted Uri schemes.
    *
    * @var array
    */
    protected static $schemes = array(
        '' => true,
        'http' => true,
        'https' => true
    );

    /**
    * Scheme part of the Uri <scheme>:// which would be empty, HTTP or 
    * HTTPS. There is a lot of different schemes but onlye these will be 
    * used for implementation.
    *
    * @var string
    */
    protected $scheme;
    
    /**
    * The host name is part of the authority which can also contain
    * an username and password for authentication plus the port number.
    * The <authority> is composed of [user[:password]@]host[:port] and 
    * the host could also be an IP address.
    *
    * @var string
    */
    protected $host;
    
    /**
    * The port is part of the authority. If no port is supplied it can 
    * be seen as using the default port :80 or :443 for a HTTPS request.
    *
    * @see $host
    * @var int|null
    */
    protected $port;
    
    /**
    * Optional username part of the Uri for authntication. This can be 
    * combined with a password user[:pass] though it's not recommended.
    *
    * @var string
    */
    protected $user;
    
    /**
    * Optional password part of the Uri which would require an username 
    * being supplied. It's strongly recommended not including the password 
    * in the Uri.
    *
    * @var string
    */
    protected $password;
    
    /**
    * The request path of the Uri is composed of values separated by slashes 
    * [/<path>] which is usually used by the server to navigate directories.
    * This can however be overidden and handle by the application instead to 
    * server content.
    *
    * @var string
    */
    protected $path;
    
    /**
    * Optional query parameters of the Uri which is passed together with the 
    * request. These parameters are usually parsed and can have different 
    * compositions depending on the backend language. Some languages support 
    * arrays or associative arrays to be passed in the query.
    *
    * PHP would parse ?array[]=a&array[]=b&array[subarray]=c as such:
    *
    * array(
    *     'array' => array(
    *         'a',
    *         'b',
    *         'subarray' => array(
    *             'c'
    *         )
    *     )
    * )
    *
    * @var string
    */
    protected $query;
    
    /**
    * The fragment part the Uri is usually not sent to the server but rather 
    * used by the browser to link to elements within the same page.
    *
    * @var string
    */
    protected $fragment;

    public function __construct($scheme, $host, $port = null, $user = '', $password = '', $path = '/', $query = '', $fragment = '')
    {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->path = empty($path) ? '/' : $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterQuery($fragment);
    }

    /**
    * Creates the Uri from a string.
    *
    * @param string $uri The Uri string.
    * @return UriInterface Returns a new instance of UriInterface.
    * @throws \InvalidArgumentException if the Uri cannot be parsed.
    */
    public static function fromString($uri)
    {
        if ($uri !== null && ($components = parse_url($uri)) !== false) {
            $scheme = isset($components['scheme']) ? $this->filterScheme($components['scheme']) : '';
            $host = isset($components['host']) ? $components['host'] : '';
            $port = isset($components['port']) ? $components['port'] : null;
            $user = isset($components['user']) ? $components['user'] : '';
            $password = isset($components['pass']) ? $components['pass'] : '';
            $path = empty($components['path']) ? '/' : $this->filterPath($components['path']);
            $query = isset($components['query']) ? $this->filterQuery($components['query']) : '';
            $fragment = isset($components['fragment']) ? $this->filterQuery($components['fragment']) : '';

            return new Uri($scheme, $host, $port, $user, $password, $path, $query, $fragment);
        } else {
            throw new InvalidArgumentException('Invalid argument or malformed Uri string.');
        }
    }

    /**
    * @inherit
    */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
    * @inherit
    */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();

        return (empty($userInfo) ? '' : '@'.$userInfo).$this->host.($this->port === null ? '' : ':'.$this->port);
    }

    /**
    * @inherit
    */
    public function getUserInfo()
    {
        return $this->user.(empty($this->password) ? '' : ':'.$this->password);
    }

    /**
    * @inherit
    */
    public function getHost()
    {
        return $this->host;
    }

    /**
    * @inherit
    */
    public function getPort()
    {
        return $this->port;
    }

    /**
    * @inherit
    */
    public function getPath()
    {
        return $this->path;
    }

    /**
    * @inherit
    */
    public function getQuery()
    {
        return $this->query;
    }

    /**
    * @inherit
    */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
    * @inherit
    */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);

        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
    * Filter Uri scheme.
    *
    * @param  string $scheme Raw Uri scheme.
    * @return string
    * @throws \InvalidArgumentException If the Uri scheme is not a string.
    * @throws \InvalidArgumentException If Uri scheme is not "", "https", or "http".
    */
    protected function filterScheme($scheme)
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Argument must be a string.');
        }
        
        $scheme = str_replace('://', '', strtolower($scheme));

        if (!isset(self::$schemes[$scheme])) {
            throw new InvalidArgumentException('Unsupported Uri scheme.');
        }
        
        return $scheme;
    }

    /**
    * @inherit
    */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password === null ? '' : $password;

        return $clone;
    }

    /**
    * @inherit
    */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
    * @inherit
    */
    public function withPort($port)
    {
        $port = $this->filterPort($port);

        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
    * Filter Uri port.
    *
    * @param  null|int $port The Uri port number.
    * @return null|int
    * @throws InvalidArgumentException If the port is invalid.
    */
    protected function filterPort($port)
    {
        if ($port === null || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            return $port;
        }

        throw new InvalidArgumentException('Argument must be null or an integer between 1 and 65535.');
    }

    /**
    * @inherit
    */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Argument must be a string.');
        }

        $path = $this->filterPath($path);

        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    /**
    * Filter Uri path. This regular expression basically checks whether the path 
    * is Uri encoded and encodes that parts that are not. A slash is also added 
    * to the end of the path if it does not exist to keep the paths consistent.
    *
    * @param  string $path The raw uri path.
    * @return string
    */
    protected function filterPath($path)
    {
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        /*
        Matches between one and unlimited characters which is not present in the list:
        a-z A-Z 0-9 _ - . ~ : @ & = + $ , %

        Afterwards assert that the comination of % a-z A-Z 0-9 in groups of 2 
        does not match. This is to check whether the url encoding of characters 
        is a valid character. When a space is url encoded it is %20 which 
        matches the second pattern, but we had %?a it would not be a valid url 
        encoded character and therefore it would be url encoded.
        */
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    /**
    * @inherit
    */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException('Argument must be a string.');
        }

        $query = $this->filterQuery($query);

        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
    * Filters the query string or fragment of a URI.
    *
    * @param string $query The raw uri query string.
    * @return string The percent-encoded query string.
    */
    protected function filterQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    /**
    * @inherit
    */
    public function withFragment($fragment)
    {
        if (!is_string($fragment)) {
            throw new InvalidArgumentException('Argument must be a string.');
        }

        $fragment = ltrim($fragment, '#');

        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
    * @inherit
    */
    public function __toString()
    {
        $uri = '';

        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        if (!empty($scheme)) {
            $uri .= $scheme.'://';
        }

        if (!empty($authority)) {
            $uri .= $authority;
        }

        if (!empty($uri) && substr($path, 0, 1) !== '/') {
            $uri .= '/';
        }

        $uri .= $path;

        if (!empty($query)) {
            $uri .= '?'.$query;
        }

        if (!empty($fragment)) {
            $uri .= '#'.$fragment;
        }

        return $uri;
    }
}
