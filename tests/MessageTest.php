<?php

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Stream;
use Hawk\Tests\Psr7\Mocks\MessageMock;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageTest
 * @package Hawk\Tests\Psr7
 */
class MessageTest extends TestCase
{
    public function testDefaultProtocolVersion()
    {
        $message = new MessageMock();
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $message = new MessageMock();
        $clone = $message->withProtocolVersion('1.0');
        $this->assertEquals('1.0', $clone->getProtocolVersion());
    }

    public function testGetProtocolVersion()
    {
        $message = new MessageMock();
        $this->assertEquals("1.1", $message->getProtocolVersion());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWithProtocolVersion()
    {
        $message = new MessageMock();
        $message->withProtocolVersion('2.2');
    }

    public function testGetHeaders()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');
        $message->headers->addHeader('Foo', 'Baz');
        $message->headers->addHeader('Foo', 'Xyz');
        $this->assertEquals(['Foo' => ['Bar', 'Baz', 'Xyz']], $message->getHeaders());
    }

    public function testHasHeader()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');
        $this->assertTrue($message->hasHeader('Foo'));
        $this->assertFalse($message->hasHeader('Bar'));
    }

    public function testGetHeaderLine()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');
        $message->headers->addHeader('Foo', 'Baz');
        $message->headers->addHeader('Foo', 'Xyz');

        $this->assertEquals('Bar,Baz,Xyz', $message->getHeaderLine('Foo'));
        $this->assertEquals('', $message->getHeaderLine('Bar'));
    }

    public function testGetHeader()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');
        $message->headers->addHeader('Foo', 'Baz');
        $message->headers->addHeader('Foo', 'Xyz');

        $this->assertEquals(['Bar', 'Baz', 'Xyz'], $message->getHeader('Foo'));
        $this->assertEquals([], $message->getHeader('Bar'));
    }

    public function testWithHeader()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');

        $clone = $message->withHeader('Foo', 'Xyz');
        $this->assertEquals('Xyz', $clone->getHeaderLine('Foo'));
    }

    public function testWithAddedHeader()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');
        $clone = $message->withAddedHeader('Foo', 'Xyz');
        $this->assertEquals('Bar,Xyz', $clone->getHeaderLine('Foo'));
    }

    public function testWithoutHeader()
    {
        $message = new MessageMock();
        $message->headers->addHeader('Foo', 'Bar');
        $message->headers->addHeader('Baz', 'Xyz');
        $clone = $message->withoutHeader('Baz');
        $this->assertEquals(['Foo' => ['Bar']], $clone->getHeaders());
    }

    public function testGetBody()
    {
        $body = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMock();

        $message = new MessageMock();
        $message->body = $body;
        $this->assertSame($body, $message->getBody());
    }

    public function testWithBody()
    {
        $body = $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body2 = clone $body;
        $message = new MessageMock();
        $message->body = $body;
        $clone = $message->withBody($body2);
        $this->assertSame($body, $message->body);
        $this->assertSame($body2, $clone->body);
    }
}
