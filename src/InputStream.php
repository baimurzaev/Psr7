<?php declare(strict_types=1);

namespace Hawk\Psr7;

/**
 * Class InputStream
 * @package Hawk\Psr7
 */
class InputStream extends Stream
{
    /**
     * @var string
     */
    private $cache = '';

    /**
     * @var bool
     */
    private $reachedEof = false;

    /**
     * InputStream constructor.
     * @param string|resource $stream
     */
    public function __construct($stream = 'php://input')
    {
        $resource = fopen($stream, 'r');

        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if ($this->reachedEof) {
            return $this->cache;
        }
        $this->getContents();
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $content = parent::read($length);
        if (!$this->reachedEof) {
            $this->cache .= $content;
        }
        if ($this->eof()) {
            $this->reachedEof = true;
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($maxLength = -1): string
    {
        if ($this->reachedEof) {
            return $this->cache;
        }
        $contents = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;
        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }
        return $contents;
    }
}