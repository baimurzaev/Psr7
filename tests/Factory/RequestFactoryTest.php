<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Factory;

use Hawk\Psr7\Factory\RequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestFactoryTest
 * @package Hawk\Tests\Psr7\Factory
 */
class RequestFactoryTest extends TestCase
{
    public function testRequestFactory()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'https://example.com/foo/bar');

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/foo/bar', $request->getUri()->getPath());
        $this->assertEquals('https://example.com/foo/bar', (string)$request->getUri());
    }
}
