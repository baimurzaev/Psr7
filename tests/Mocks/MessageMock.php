<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Mocks;

use Hawk\Psr7\Headers;
use Hawk\Psr7\Message;
use Psr\Http\Message\StreamInterface;

/**
 * Class MessageMock
 * @package Hawk\Tests\Psr7\Mocks
 */
class MessageMock extends Message
{
    /**
     * @var Headers
     */
    public $headers;

    /**
     * @var StreamInterface
     */
    public $body;

    /**
     * @var string
     */
    public $protocolVersion;

    /**
     * MessageMock constructor.
     */
    public function __construct()
    {
        $this->headers = new Headers();
    }
}
