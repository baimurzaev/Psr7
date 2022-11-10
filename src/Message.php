<?php declare(strict_types=1);

namespace Hawk\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Stream
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 *
 */
abstract class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $protocol = '1.1';

    /**
     * @var array
     */
    protected static $protocolVersions = [
        '1.0',
        '1.1',
        '2.0',
        '2',
    ];

    /**
     * @var Headers
     */
    protected $headers;

    /**
     * @var StreamInterface
     */
    protected $body;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        if (!in_array($version, self::$protocolVersions)) {
            throw new InvalidArgumentException('Invalid HTTP protocol version');
        }

        $clone = clone $this;
        $clone->headers = clone $this->headers;
        $clone->protocol = $version;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        return $this->headers->getHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->headers->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return $this->headers->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->headers = clone $this->headers;
        $clone->headers->setHeader($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        $clone->headers = clone $this->headers;
        $clone->headers->addHeader($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        $clone->headers = clone $this->headers;
        $clone->headers->removeHeader($name);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->headers = clone $this->headers;
        $clone->body = $body;

        return $clone;
    }
}
