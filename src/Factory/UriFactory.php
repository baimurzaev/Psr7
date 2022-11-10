<?php declare(strict_types=1);

namespace Hawk\Psr7\Factory;

use Hawk\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class UriFactory
 * @package Hawk\Psr7\Factory
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        if (!is_string($uri) || $uri === '') {
            throw new InvalidArgumentException('Parameter uri must be a string and not must be empty');
        }

        return new Uri($uri);
    }
}
