<?php declare(strict_types=1);

namespace Hawk\Psr7\Factory;

use Hawk\Psr7\UploadedFile;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UploadedFileFactory
 * @package Hawk\Psr7\Factory
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        $file = $stream->getMetadata('uri');

        if (!is_string($file) || !is_readable($file)) {
            throw new InvalidArgumentException('File is not readable.');
        }

        if (is_null($size)) {
            $size = $stream->getSize();
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
