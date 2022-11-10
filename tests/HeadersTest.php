<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Headers;
use PHPUnit\Framework\TestCase;

/**
 * Class HeadersTest
 * @package Hawk\Tests\Psr7
 */
class HeadersTest extends TestCase
{
    public function factory()
    {
        return new Headers();
    }

    public function testHeader()
    {
        $headers = new Headers(['Accept' => 'application/xml']);
        $this->assertEquals(['application/xml'], $headers->getHeader('Accept'));
    }

    public function testAddHeader()
    {
        $headers = new Headers();
        $headers->addHeader('Accept', 'text/html');
        $headers->addHeader('Accept', 'application/xml');
        $this->assertEquals(['text/html', 'application/xml'], $headers->getHeader('Accept'));
    }

    public function testGetHeader()
    {
        $headers = new Headers();
        $headers->addHeader('accept', 'text/html');
        $this->assertEquals(['text/html'], $headers->getHeader('Accept'));
        $this->assertEquals(['text/html'], $headers->getHeader('accept'));
    }

    public function testGetHeaders()
    {
        $headersLine = [
            'Accept' => ['application/xml', 'text/html'],
            'Connection' => ['keep-alive']
        ];

        $headers = new Headers(['Accept' => 'application/xml']);
        $headers->addHeader('Accept', 'text/html');
        $headers->addHeader('Connection', 'keep-alive');
        $this->assertEquals($headersLine, $headers->getHeaders());
    }

    public function testSetHeaders()
    {
        $headersLine = [
            'Accept' => ['application/xml', 'text/html'],
            'Connection' => ['keep-alive']
        ];

        $headers = new Headers();
        $headers->setHeaders($headersLine);

        $this->assertEquals($headersLine, $headers->getHeaders());
    }

    public function testRemoveHeader()
    {
        $headers = new Headers(['Accept' => 'application/xml']);
        $headers->removeHeader('Accept');

        $this->assertEquals([], $headers->getHeaders());
        $this->assertEquals([], $headers->getHeader('Accept'));
    }

    public function testHasHeader()
    {
        $headers = new Headers(['Accept' => 'text/html']);
        $this->assertIsBool($headers->hasHeader('Accept'));
        $this->assertEquals(true, $headers->hasHeader('Accept'));
        $this->assertEquals(false, $headers->hasHeader('Connection'));
    }
}
