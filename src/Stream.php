<?php declare(strict_types=1);

namespace Hawk\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class Stream
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 *
 */
class Stream implements StreamInterface
{
    /**
     * @var resource|null
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $size;

    /**
     * @var array|null
     */
    protected $meta;

    /**
     * @var bool
     */
    protected $writable = false;

    /**
     * @var bool
     */
    protected $readable = false;

    /**
     * @var bool
     */
    protected $seekable = false;

    /**
     * @var array|mixed|null
     */
    protected $uri;

    /**
     * Stream constructor.
     * @param resource|null $resource
     */
    public function __construct($resource = null)
    {
        if ($resource === null) {
            return;
        }

        $this->create($resource);
    }

    /**
     * Create stream
     *
     * @param resource $resource
     */
    public function create($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        $this->meta = stream_get_meta_data($resource);
        $this->resource = $resource;
        $mode = $this->meta['mode'];

        $this->writable = (bool)preg_match('/a|w|r\+|rb\+|rw|x|c/', $mode);
        $this->readable = (bool)preg_match('/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/', $mode);
        $this->seekable = fseek($this->resource, 0, SEEK_CUR) === 0 && $this->meta['seekable'];
        $this->uri = $this->getMetadata('uri');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (isset($this->resource)) {
            if (is_resource($this->resource)) {
                fclose($this->resource);
            }
            $this->detach();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
        $this->meta = null;
        $this->size = null;
        $this->uri = null;

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        if (is_null($this->resource)) {
            return null;
        }

        $stats = fstat($this->resource);

        if ($stats !== false) {
            return (int)$stats['size'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        $result = ftell($this->resource);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->resource ? feof($this->resource) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable() || $this->resource && fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek in stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        if (!$this->isSeekable() || $this->resource && rewind($this->resource) === false) {
            throw new RuntimeException('Could not rewind stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $written = false;

        if ($this->resource) {
            $written = fwrite($this->resource, $string);
        }

        if ($written === false) {
            throw new RuntimeException('Could not write to stream.');
        }

        $this->size = null;

        return $written;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $data = false;

        if ($this->resource) {
            $data = fread($this->resource, $length);
        }

        if (!is_string($data)) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        $contents = stream_get_contents($this->resource);

        if ($contents === false) {
            throw new RuntimeException('Failed to get contents of stream');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->resource)) {
            return is_null($key) ? null : [];
        }

        $meta = stream_get_meta_data($this->resource);

        if (!$key) {
            return $meta;
        }

        return isset($meta[$key]) ? $meta[$key] : null;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}
