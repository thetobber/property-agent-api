<?php
namespace PropertyAgent\Models\Http;

use InvalidArgumentException;
use RuntimeException;
use PropertyAgent\Models\Http\Stream;
use Psr\Http\Message\UploadedFileInterface;

/**
* @link https://github.com/php-fig/http-message/blob/master/src/UploadedFileInterface.php
*/
class UploadedFile implements UploadedFileInterface
{
    /**
    * PHP error codes returned with the $_FILES array.
    *
    * @var array Array of PHP error codes.
    */
    protected static $errors = array(
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION
    );

    /**
    * Stream wrapper object.
    *
    * @var StreamInterface
    */
    protected $stream;

    /**
    * The underlying stream resource.
    *
    * @var resource
    */
    protected $file;

    /**
    * The file size in bytes.
    *
    * @var int|null
    */
    protected $size;

    /**
    * One of PHP's UPLOAD_ERR constants. UPLOAD_ERR_OK if upload was successful.
    *
    * @var int
    */
    protected $error;

    /**
    * Name of the file sent from the client.
    *
    * @var string|null
    */
    protected $clientFilename;

    /**
    * Media type of the file sent from the client.
    *
    * @var string|null
    */
    protected $clientMediaType;

    /**
    * True if file was moved.
    *
    * @var bool
    */
    protected $moved;

    public function __construct($target, $size = null, $error, $clientFilename = null, $clientMediaType = null)
    {
        if (is_string($target)) {
            $this->file = $target;
            $this->stream = new Stream(fopen($this->file, 'r'));
        }

        if (is_resource($target)) {
            $this->stream = new Stream($target);
        }

        if ($this->file === null && $this->stream === null) {
            if ($target instanceof StreamInterface) {
                $this->stream = $target;
            } else {
                throw new RuntimeException('Invalid stream or resource passed with $target.');
            }
        }

        if (!is_int($size) || $size === null) {
            throw new InvalidArgumentException('Argument $size must be an integer or null.');
        }
        $this->size = $size;

        //Notice that UPLOAD_ERR_OK is passed on success
        if (!isset(self::$errors[$error])) {
            throw new InvalidArgumentException('Argument $error must correspond to one of the UPLOAD_ERROR constants.');
        }
        $this->error = $error;

        if (!is_string($clientFilename) || $clientFilename === null) {
            throw new InvalidArgumentException('Argument $clientFilename must be an integer or null.');
        }
        $this->clientFilename = $clientFilename;

        if (!is_string($clientMediaType) || $clientMediaType === null) {
            throw new InvalidArgumentException('Argument $clientMediaType must be an integer or null.');
        }
        $this->clientMediaType = $clientMediaType;
    }

    /**
    * @inherit
    */
    public function getStream()
    {
        if ($this->stream === null) {
            throw new RuntimeException('No stream available.');
        }

        return $this->stream;
    }

    /**
    * @inherit
    */
    public function moveTo($targetPath)
    {
        if ($this->moved === true) {
            throw new RuntimeException('File has already been moved.');
        }

        if (!is_string($targetPath) || empty($targetPath)) {
            throw new InvalidArgumentException("Argument must be a non-empty string.");
        }

        if (!is_writable(dirname($targetPath))) {
            throw new RuntimeException("Cannot write to directory $targetPath.");
        }

        $handle = fopen($targetPath, 'wb+');

        if ($handle === false) {
            throw new RuntimeException('Failed writing to $targetPath.');
        }

        $this->stream->rewind();

        while (!$this->stream->eof()) {
            fwrite($handle, $this->stream->read(Stream::CHUNK_SIZE));
        }

        fclose($handle);
        $this->moved = true;
    }

    /**
    * @inherit
    */
    public function getSize()
    {
        return $this->size;
    }

    /**
    * @inherit
    */
    public function getError()
    {
        return $this->error;
    }

    /**
    * @inherit
    */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
    * @inherit
    */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}
