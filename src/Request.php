<?php declare(strict_types=1);

namespace Hawk\Psr7;

use Hawk\Psr7\Factory\StreamFactory;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class Request
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class Request extends Message implements ServerRequestInterface
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $cookieParams;

    /**
     * @var array
     */
    protected $serverParams;

    /**
     * @var string
     */
    protected $requestTarget;

    /**
     * @var array
     */
    protected $queryParams;

    /**
     * @var null|array|object
     */
    protected $parsedBody;

    /**
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles;

    /**
     * @var array
     */
    private $validMethods = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'OPTIONS',
        'PATCH',
        'DELETE',
    ];

    /**
     * Request constructor.
     * @param string|UriInterface $uri
     * @param string|null $method
     * @param null|array|Headers $headers
     * @param null|string|StreamInterface $body
     * @param array $cookies
     */
    public function __construct($uri = '', $method = "GET", $headers = null, $body = null, $cookies = [])
    {
        if (is_array($headers)) {
            $headers = new Headers($headers);
        }

        if (is_string($body) && $body !== '') {
            $body = (new StreamFactory())->createStream($body);
        }

        if ((is_string($uri) && $uri !== '')) {
            $uri = new Uri($uri);
        }

        $this->attributes = [];
        $this->cookieParams = is_array($cookies) ? $cookies : [];
        $this->body = ($body instanceof Stream) ? $body : (new StreamFactory())->createStream('');
        $this->uri = ($uri instanceof Uri) ? $uri : new Uri('');
        $this->headers = ($headers instanceof Headers) ? $headers : new Headers();
        $this->method = $this->filterMethod($method);

        if (!$this->headers->hasHeader('Host') || $this->uri->getHost() !== '') {
            $this->headers->setHeader('Host', $this->uri->getHost());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        if ($this->uri === null) {
            return '/';
        }

        $target = $this->uri->getPath();
        $target = '/' . ltrim($target, '/');

        if ($query = $this->uri->getQuery()) {
            $target .= '?' . $query;
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }


    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $this->filterMethod($method);

        return $clone;
    }

    /**
     * @param string|null $method
     * @return string
     */
    protected function filterMethod($method)
    {
        if (!is_string($method) || $method === '') {
            throw new \InvalidArgumentException('Method must be a non-empty string.');
        }

        $method = strtoupper($method);

        if (!in_array($method, $this->validMethods, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported HTTP method "%s" provided', $method));
        }

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;
        $clone->headers = clone $this->headers;

        if (!$preserveHost && $uri->getHost() !== '') {
            $clone->headers->setHeader('Host', $uri->getHost());
            return $clone;
        }

        if (($uri->getHost() !== '' && !$this->hasHeader('Host') || $this->getHeaderLine('Host') === '')) {
            $clone->headers->setHeader('Host', $uri->getHost());
            return $clone;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException(
                'Parsed body parameter must be object, array or null'
            );
        }

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }
}
