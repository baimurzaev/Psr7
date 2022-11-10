<?php declare(strict_types=1);

namespace Hawk\Psr7;

use Hawk\Psr7\Factory\StreamFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UploadedFile
 *
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 *
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int|null
     */
    private $size;

    /**
     * @var bool
     */
    private $moved = false;

    /**
     * @var int
     */
    private $error = UPLOAD_ERR_OK;

    /**
     * @var bool
     */
    private $needIsMove = false;

    /**
     * @var string|null
     */
    private $clientFilename;

    /**
     * @var string|null
     */
    private $clientMediaType;

    /**
     * UploadedFile constructor.
     * @param StreamInterface|string $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ) {
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ($stream instanceof StreamInterface) {
            $file = $stream->getMetadata('uri');
            if (!is_string($file)) {
                throw new \InvalidArgumentException('The path associated with the stream was not found');
            }
            $this->file = $file;
            $this->stream = $stream;
        } elseif (is_string($stream)) {
            $this->file = $stream;
        } else {
            throw new \InvalidArgumentException('Specify the path to the file');
        }

        $this->size = $size;
    }

    /**
     * @return bool
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * @return bool
     */
    public function isUploaded(): bool
    {
        return UPLOAD_ERR_OK === $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->isMoved()) {
            throw new \RuntimeException(
                sprintf('Unable to get stream, file %s has already been moved', $this->clientFilename)
            );
        }

        if (!$this->stream instanceof StreamInterface) {
            $this->stream = (new StreamFactory())->createStreamFromFile($this->file);
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath): UploadedFileInterface
    {
        if ($this->moved) {
            throw new \RuntimeException('The uploaded file has already been moved');
        }

        $this->moveActions($targetPath);

        $this->moved = true;

        return $this;
    }

    /**
     * @param string $targetPath
     */
    private function moveActions($targetPath): void
    {
        $isUrlStream = stripos($targetPath, '://');
        $isUrlStream = ($isUrlStream !== false && $isUrlStream > 0) ? true : false;

        if (!$isUrlStream && !is_writable(dirname($targetPath))) {
            throw new \InvalidArgumentException('Upload target path is not writable');
        }

        if ($isUrlStream) {
            $this->copyStream($targetPath);
        } elseif ($this->needIsMove !== false) {
            $this->moveFile($targetPath);
        } else {
            $this->renameFile($targetPath);
        }
    }

    /**
     * @param string $targetPath
     */
    private function renameFile($targetPath): void
    {
        if (!rename($this->file, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Error moving uploaded file %s to %s', $this->clientFilename, $targetPath)
            );
        }
    }

    /**
     * @param string $targetPath
     */
    private function copyStream($targetPath): void
    {
        if (!copy($this->file, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Error moving uploaded file %s to %s', $this->clientFilename, $targetPath)
            );
        }

        if (!unlink($this->file)) {
            throw new \RuntimeException(
                sprintf('Error removing uploaded file %s', $this->clientFilename)
            );
        }
    }

    /**
     * @param string $targetPath
     */
    private function moveFile($targetPath): void
    {
        if (!is_uploaded_file($this->file)) {
            throw new \RuntimeException(
                sprintf('%s is not a valid uploaded file', $this->file)
            );
        }

        if (!move_uploaded_file($this->file, $targetPath)) {
            throw new \RuntimeException(
                sprintf('Error moving uploaded file %s to %s', $this->clientFilename, $targetPath)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }
}
