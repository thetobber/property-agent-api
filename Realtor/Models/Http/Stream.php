<?php
namespace Realtor\Models\Http;

use InvalidArgumentException;
use RuntimeException;
use Psr\Http\Message\StreamInterface;

/**
* @link https://github.com/php-fig/http-message/blob/master/src/StreamInterface.php
*/
class Stream implements StreamInterface
{
    const CHUNK_SIZE = 4096;

    /**
    * Possible modes which specifies the type of access to the stream.
    *
    * @link http://php.net/manual/en/function.fopen.php
    * @var array
    */
    protected static $modes = array(
        'readable' => array('r', 'r+', 'w+', 'a+', 'x+', 'c+'),
        'writable' => array('r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+')
    );

    /**
    * The stream resource.
    *
    * @var resource
    */
    protected $stream;

    /**
    * The metadata which describes the stream.
    *
    * @var array
    */
    protected $metadata;

    /**
    * Is the stream readable?
    *
    * @var bool
    */
    protected $readable;
    
    /**
    * Is the stream writable?
    *
    * @var bool
    */
    protected $writable;

    /**
    * Is the stream seekable?
    *
    * @var bool
    */
    protected $seekable;

    /**
    * Size of the stream in bytes.
    *
    * @var int|null
    */
    protected $size;

    /**
    * @param resource $resource The PHP resource handle.
    * @throws \InvalidArgumentException if argument is not a valid PHP resource.
    */
    public function __construct($resource)
    {
        if (is_resource($resource) === false) {
            throw new InvalidArgumentException('Argument must be a valid PHP resource.');
        }

        $this->stream = $resource;
    }

    /**
    * @inherit
    */
    public function __toString()
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $exception) {
            return '';
        }
    }

    /**
    * @inherit
    */
    public function close()
    {
        fclose($this->stream);

        $this->stream = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
        $this->size = null;
    }

    /**
    * @inherit
    */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
        $this->size = null;

        return $stream;
    }

    /**
    * @inherit
    */
    public function getSize()
    {
        if ($this->size === null) {
            $stat = fstat($this->stream);
            return isset($stat['size']) ? $stat['size'] : null;
        }

        return $this->size;
    }

    /**
    * @inherit
    */
    public function tell()
    {
        if (($position = ftell($this->stream)) === false) {
            throw new RuntimeException('Failed to get pointer position.');
        }

        return $position;
    }

    /**
    * @inherit
    */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
    * @inherit
    */
    public function isSeekable()
    {
        if ($this->seekable === null) {
            $this->seekable = false;
            $metadata = $this->getMetadata();

            $this->seekable = isset($metadata['seekable']);
        }

        return $this->seekable;
    }

    /**
    * @inherit
    */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Failed to seek in stream.');
        }
    }

    /**
    * @inherit
    */
    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new RuntimeException('Failed to rewind stream.');
        }
    }

    /**
    * @inherit
    */
    public function isWritable()
    {
        if ($this->writable === null) {
            $this->writable = false;
            $metadata = $this->getMetadata();

            foreach (self::$modes['writable'] as $mode) {
                if (strpos($metadata['mode'], $mode) === 0) {
                    $this->writable = true;
                    break;
                }
            }
        }

        return $this->writable;
    }

    /**
    * @inherit
    */
    public function write($string)
    {
        if (!$this->isWritable() || ($bytes = fwrite($this->stream, $string)) === false) {
            throw new RuntimeException('Failed writing to stream.');
        }

        return $bytes;
    }

    /**
    * @inherit
    */
    public function isReadable()
    {
        if ($this->readable === null) {
            $this->readable = false;
            $metadata = $this->getMetadata();

            foreach (self::$modes['readable'] as $mode) {
                if (strpos($metadata['mode'], $mode) === 0) {
                    $this->readable = true;
                    break;
                }
            }
        }

        return $this->readable;
    }

    /**
    * @inherit
    */
    public function read($length)
    {
        if (!$this->isReadable() || ($data = fread($this->stream, $length)) === false) {
            throw new RuntimeException('Failed to read from stream.');
        }

        return $data;
    }

    /**
    * @inherit
    */
    public function getContents()
    {
        if (!$this->isReadable() || ($contents = stream_get_contents($this->stream)) === false) {
            throw new RuntimeException('Failed to retrieve contents from stream.');
        }
        
        return $contents;
    }

    /**
    * @inherit
    */
    public function getMetadata($key = null)
    {
        $this->metadata = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $this->metadata;
        }

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }
}
