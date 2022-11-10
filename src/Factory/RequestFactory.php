<?php declare(strict_types=1);

namespace Hawk\Psr7\Factory;

use Hawk\Psr7\Headers;
use Hawk\Psr7\Request;
use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class RequestFactory
 * @package Hawk\Psr7\Factory
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * @var UriFactory|UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * @var StreamFactory|StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * RequestFactory constructor.
     * @param StreamFactoryInterface|null $streamFactory
     * @param UriFactoryInterface|null $uriFactory
     */
    public function __construct(StreamFactoryInterface $streamFactory = null, UriFactoryInterface $uriFactory = null)
    {
        $this->uriFactory = (!$uriFactory) ? new UriFactory() : $uriFactory;
        $this->streamFactory = (!$streamFactory) ? new StreamFactory() : $streamFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException('$uri must be a string or has state UriInterface');
        }

        return new Request($uri, $method, new Headers());
    }
}
