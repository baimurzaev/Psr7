<?php declare(strict_types=1);

namespace Hawk\Psr7;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class ServerRequest
 * @package Hawk\Psr7
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * ServerRequest constructor.
     * @param array $serverParams
     * @param array $uploadedFiles
     * @param null $uri
     * @param string|null $method
     * @param string $body
     * @param array $headers
     * @param array $cookies
     * @param array $queryParams
     * @param null $parsedBody
     * @param string $protocol
     */
    public function __construct(
        array $serverParams = [],
        array $uploadedFiles = [],
        $uri = null,
        string $method = null,
        $body = 'php://input',
        array $headers = [],
        array $cookies = [],
        array $queryParams = [],
        $parsedBody = null,
        string $protocol = '1.1'
    ) {

        if ($body === 'php://input') {
            $body = new InputStream();
        }

        $this->initialize($uri, $method, $body, $headers);
        $this->serverParams = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->cookieParams = $cookies;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
        $this->protocol = $protocol;
    }

    /**
     * @param null $uri
     * @param string|null $method
     * @param string $body
     * @param array $headers
     */
    private function initialize(
        $uri = null,
        string $method = null,
        $body = 'php://memory',
        array $headers = []
    ): void {
        if ($method !== null) {
            $this->setMethod($method);
        }

        $this->uri = ($uri instanceof UriInterface) ? $uri : $this->createUri($uri);
        $this->stream = new Stream(fopen($body, 'wb+'));
        $this->setHeaders($headers);
        // per PSR-7: attempt to set the Host header from a provided URI if no
        // Host header is provided
        if (!$this->hasHeader('Host') && $this->uri->getHost()) {
            $this->headerNames['host'] = 'Host';
            $this->headers['Host'] = [$this->getHostFromUri()];
        }
    }

    /**
     * @param $uri
     * @return UriInterface
     */
    private function createUri($uri): UriInterface
    {
        if (is_string($uri)) {
            return new Uri($uri);
        }
        if ($uri === null) {
            return new Uri('');
        }

        throw new \InvalidArgumentException(
            'Invalid URI provided. Must be null or string'
        );
    }

    /**
     * @param $method
     */
    private function setMethod($method): void
    {
        if (!is_string($method)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                is_object($method) ? get_class($method) : gettype($method)
            ));
        }
        if (!preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }
        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($attribute, $default = null)
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$attribute];
    }
}
