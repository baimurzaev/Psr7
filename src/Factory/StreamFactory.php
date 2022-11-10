<?php declare(strict_types=1);

namespace Hawk\Psr7\Factory;

use Hawk\Psr7\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class StreamFactory
 * @package Hawk\Psr7\Factory
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $handle = fopen('php://temp', 'r+');

        if (!is_resource($handle)) {
            throw new RuntimeException('Unable to open temporary file stream');
        }

        fwrite($handle, $content);
        rewind($handle);

        return $this->createStreamFromResource($handle);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $handle = fopen($filename, $mode);

        if (!is_resource($handle)) {
            throw new RuntimeException(sprintf("Could not create resource from file %s", $filename));
        }

        return $this->createStreamFromResource($handle);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($handle): StreamInterface
    {
        if (!is_resource($handle)) {
            throw new InvalidArgumentException('Parameter 1 has no resource.');
        }

        return new Stream($handle);
    }
}
