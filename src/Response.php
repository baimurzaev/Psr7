<?php declare(strict_types=1);

namespace Hawk\Psr7;

use Hawk\Psr7\Factory\StreamFactory;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Response
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var int
     */
    private $statusCode = 200;

    /**
     * @var string
     */
    private $reasonPhrase = '';

    /**
     * Map of standard HTTP status code/reason phrases
     *
     * @var array
     */
    private $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * Response constructor.
     * @param int $code
     * @param string $reasonPhrase
     * @param Headers|array $headers
     * @param StreamInterface|null|string $body
     * @param string $version
     */
    public function __construct(int $code = 200, $reasonPhrase = '', $headers = null, $body = null, $version = '1.1')
    {
        if (is_array($headers)) {
            $headers = new Headers($headers);
        }

        if (is_string($body)) {
            $body = (new StreamFactory())->createStream($body);
        }

        $this->protocol = $version;
        $this->statusCode = $this->filterStatus($code);
        $this->reasonPhrase = $reasonPhrase;

        $this->headers = ($headers instanceof Headers) ? $headers : new Headers();
        $this->body = ($body instanceof StreamInterface) ? $body : (new StreamFactory())->createStream();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $statusCode = $this->filterStatus($code);

        if (!is_string($reasonPhrase)) {
            throw new InvalidArgumentException('Response reason phrase must be a string.');
        }

        $clone = clone $this;
        $clone->statusCode = $statusCode;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase !== '') {
            return $this->reasonPhrase;
        }

        if (isset($this->phrases[$this->statusCode])) {
            return $this->phrases[$this->statusCode];
        }

        return '';
    }

    /**
     * Filter HTTP status code
     *
     * @param integer $code
     * @return int
     */
    protected function filterStatus($code): int
    {
        if (!is_integer($code) || ($code < 100 || $code > 599)) {
            throw new InvalidArgumentException('Status code must be an integer value between 100 and 599');
        }

        return $code;
    }
}
