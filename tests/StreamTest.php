<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamTest
 * @package Hawk\Tests\Psr7
 */
class StreamTest extends TestCase
{
    public function createBody($mode)
    {
        $handle = fopen('php://temp', $mode);
        fwrite($handle, 'body');

        return $handle;
    }

    public function testConstructor()
    {
        $stream = new Stream($this->createBody("r+"));
        $metadata = $stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertEquals(4, $stream->getSize());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->eof());

        $stream->close();
    }

    public function testStreamDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function testToString()
    {
        $stream = new Stream($this->createBody("w+"));
        $this->assertEquals('body', (string)$stream);
        $stream->close();
    }

    public function testGetsContents()
    {
        $stream = new Stream($this->createBody("w+"));
        $this->assertEquals('', $stream->getContents());
        $stream->seek(0);
        $this->assertEquals('body', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
    }

    public function testChecksEof()
    {
        $stream = new Stream($this->createBody("w+"));
        $this->assertFalse($stream->eof());
        $stream->read(4);
        $this->assertTrue($stream->eof());
        $stream->close();
    }

    public function testEnsuresRightSize()
    {
        $handle = fopen('php://temp', 'w+');
        $this->assertEquals(4, fwrite($handle, 'test'));

        $stream = new Stream($handle);
        $this->assertEquals(4, $stream->getSize());
        $this->assertEquals(5, $stream->write('test2'));
        $this->assertEquals(9, $stream->getSize());
        $stream->close();
    }

    public function testGetSize()
    {
        $file = __FILE__;
        $stream = new Stream(fopen($file, 'r'));
        $this->assertEquals(filesize($file), $stream->getSize());
        $stream->close();
    }

    public function testStreamPosition()
    {
        $handle = fopen('php://temp', 'w+');
        $stream = new Stream($handle);
        $this->assertEquals(0, $stream->tell());
        $stream->write('test');
        $this->assertEquals(4, $stream->tell());
        $stream->seek(2);
        $this->assertEquals(2, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());
        $stream->close();
    }

    public function testDetachStream()
    {
        $handle = fopen('php://temp', 'w+');
        $stream = new Stream($handle);
        $stream->write('foo');
        $this->assertTrue($stream->isReadable());
        $this->assertSame($handle, $stream->detach());
        $stream->detach();

        $this->assertSame('', (string)$stream);
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->exceptions($stream);
        $stream->close();
    }

    public function exceptions($stream)
    {
        $methods = [
            'read' => 4,
            'write' => 'test',
            'seek' => 4,
            'tell' => false,
            'eof' => false,
            'getSize' => false,
            'getContents' => false
        ];

        foreach ($methods as $method => $param) {
            try {
                ($param !== false) ? $stream->$method($param) : $stream->$method();
                $this->fail();
            } catch (\Exception $e) {
                // Nop
            }
        }
    }
}
