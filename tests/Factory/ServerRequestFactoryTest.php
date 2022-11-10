<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Factory;

use Hawk\Psr7\Factory\ServerRequestFactory;
use Hawk\Psr7\Factory\StreamFactory;
use Hawk\Psr7\Factory\UriFactory;
use Hawk\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Class ServerRequestFactoryTest
 * @package Hawk\Tests\Psr7\Factory
 */
class ServerRequestFactoryTest extends TestCase
{
    public function testConstructor()
    {
        $serverRequest = new ServerRequestFactory(new StreamFactory(), new UriFactory);
        $request = $serverRequest->createServerRequest("GET", new Uri("https://example.com/foo/bar"));

        $this->assertEquals("GET", $request->getMethod());
        $this->assertEquals("https://example.com/foo/bar", (string)$request->getUri());
    }
}
