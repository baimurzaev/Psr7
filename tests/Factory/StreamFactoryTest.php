<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Factory;

use Hawk\Psr7\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamFactoryTest
 * @package Hawk\Tests\Psr7\Factory
 */
class StreamFactoryTest extends TestCase
{
    public function testCreateStreamFactory()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('test');
        $this->assertEquals('test', (string)$stream);
    }
}
