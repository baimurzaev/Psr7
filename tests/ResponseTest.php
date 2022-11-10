<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Factory\StreamFactory;
use Hawk\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Class ResponseTest
 * @package Hawk\Tests\Psr7
 */
class ResponseTest extends TestCase
{
    public function testDefaultProperties()
    {
        $response = new Response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.1', $response->getProtocolVersion());

        $this->assertSame($response->getHeaders(), []);
        $this->assertSame('', (string)$response->getBody());

        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
    }

    public function testConstructStatusCode()
    {
        $response = new Response(403);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Forbidden', $response->getReasonPhrase());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructInvalidStatusCode()
    {
        $response = new Response(88);
        $this->assertSame(88, $response->getStatusCode());
        $this->assertSame('', $response->getReasonPhrase());
    }

    public function testConstructStatusCodeAndDefaultReason()
    {
        $response = new Response(404);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStatusNumString()
    {
        $response = new Response(404);
        $response2 = $response->withStatus('201');
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
        $this->assertSame(201, $response2->getStatusCode());
        $this->assertSame('Created', $response2->getReasonPhrase());
    }

    public function testConstructWithHeaders()
    {
        $response = new Response(200, '', ['Accept' => ['application/xml']]);
        $this->assertSame(['Accept' => ['application/xml']], $response->getHeaders());
        $this->assertSame('application/xml', $response->getHeaderLine('Accept'));
        $this->assertSame(['application/xml'], $response->getHeader('Accept'));
    }

    public function testConstructWithHeadersAsArray()
    {
        $response = new Response(200, '', ['Accept' => ['application/xml', 'application/json']]);
        $this->assertSame(['Accept' => ['application/xml', 'application/json']], $response->getHeaders());
        $this->assertSame('application/xml,application/json', $response->getHeaderLine('Accept'));
        $this->assertSame(['application/xml', 'application/json'], $response->getHeader('Accept'));
    }

    public function testConstructWithBody()
    {
        $response = new Response(200, '', null, 'test');
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('test', (string)$response->getBody());
    }

    public function testConstructNullBodyStream()
    {
        $response = new Response(200, '', null, '');
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('', (string)$response->getBody());
    }

    public function testWithStatusCode()
    {
        $response = (new Response())->withStatus(301);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('Moved Permanently', $response->getReasonPhrase());
    }

    public function testWithStatusAndReason()
    {
        $response = new Response(200);
        $response = $response->withStatus(404, 'Oh my god! Page Not Fount');
        $this->assertSame('Oh my god! Page Not Fount', $response->getReasonPhrase());
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testConstructWithProtocolVersion()
    {
        $response = new Response(200, '', null, null, '4.1');
        $this->assertSame('4.1', $response->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $response = (new Response())->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidWithProtocolVersion()
    {
        $response = (new Response())->withProtocolVersion('4.1');
        $this->assertSame('4.1', $response->getProtocolVersion());
    }

    public function testWithBody()
    {
        $response = (new Response())->withBody((new StreamFactory())->createStream('test'));
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('test', (string)$response->getBody());
    }

    public function testWithHeader()
    {
        $response = new Response(200, '', ['Accept' => ['application/xml']]);
        $response2 = $response->withHeader('Accept', 'application/json');
        $this->assertSame(['Accept' => ['application/xml']], $response->getHeaders());
        $this->assertSame(['Accept' => ['application/json']], $response2->getHeaders());
        $this->assertSame('application/xml', $response->getHeaderLine('Accept'));
        $this->assertSame(['application/json'], $response2->getHeader('Accept'));
    }

    public function testWithHeaderArray()
    {
        $response = new Response(200, '', ['Accept' => ['application/xml']]);
        $response2 = $response->withHeader('Content-Language', ['ru', 'en']);

        $this->assertSame(['Accept' => ['application/xml']], $response->getHeaders());
        $this->assertSame(
            ['Accept' => ['application/xml'], 'Content-Language' => ['ru', 'en']],
            $response2->getHeaders()
        );
        $this->assertSame('ru,en', $response2->getHeaderLine('Content-Language'));
        $this->assertSame(['ru', 'en'], $response2->getHeader('Content-Language'));
    }

    public function testWithAddedHeader()
    {
        $response = new Response(200, '', ['Accept' => ['application/xml']]);
        $response2 = $response->withAddedHeader('Accept', 'application/json');
        $this->assertSame(['Accept' => ['application/xml']], $response->getHeaders());
        $this->assertSame(['Accept' => ['application/xml', 'application/json']], $response2->getHeaders());
        $this->assertSame('application/xml,application/json', $response2->getHeaderLine('Accept'));
        $this->assertSame(['application/xml', 'application/json'], $response2->getHeader('Accept'));
    }

    public function testWithAddedHeaderArray()
    {
        $response = new Response(200, '', ['Accept' => ['application/xml']]);
        $response2 = $response->withAddedHeader('Accept', ['application/json']);
        $this->assertSame(['Accept' => ['application/xml']], $response->getHeaders());
        $this->assertSame(['Accept' => ['application/xml', 'application/json']], $response2->getHeaders());
        $this->assertSame('application/xml,application/json', $response2->getHeaderLine('Accept'));
        $this->assertSame(['application/xml', 'application/json'], $response2->getHeader('Accept'));
    }

    public function testWithoutHeaderThatExists()
    {
        $response = new Response(200, '', ['Accept' => 'application/xml', 'Age' => '1']);
        $response2 = $response->withoutHeader('Age');
        $this->assertFalse($response2->hasHeader('Age'));
        $this->assertTrue($response->hasHeader('Accept'));
        $this->assertSame(['Accept' => ['application/xml'], 'Age' => ['1']], $response->getHeaders());
        $this->assertSame(['Accept' => ['application/xml']], $response2->getHeaders());
    }

    public function testWithoutHeaderThatDoesNotExist()
    {
        $response = new Response(200, '', ['Accept' => 'application/xml']);
        $response2 = $response->withoutHeader('accept');
        $this->assertFalse($response2->hasHeader('Accept'));
    }
}
