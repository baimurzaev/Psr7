<?php declare(strict_types=1);

namespace Hawk\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class Uri implements UriInterface
{
    private const FILTER_PATTERN_USER_INFO = '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=]+|%(?![A-Fa-f0-9]{2}))/u';
    private const FILTER_PATTERN = '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/';

    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     */
    private $user = '';

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var null|int
     */
    private $port;

    /**
     * @var array schemes
     */
    protected $schemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * @var string
     */
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * Parse a URL
     *
     * Uri constructor.
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            if (!$parts = parse_url($uri)) {
                throw new InvalidArgumentException(sprintf("Unable to parse URI: %s", $uri));
            }

            $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
            $this->host = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
            $this->port = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
            $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
            $this->query = isset($parts['query']) ? $this->filterQuery($parts['query']) : '';
            $this->fragment = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';

            $this->user = (isset($parts['pass']) && $parts['pass'] !== '')
                ? $parts['user'] . ':' . $parts['pass']
                : $parts['user'] ?? '';
        }
    }

    /**
     * @param string $scheme
     * @return string
     */
    private function filterScheme($scheme): string
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('Uri scheme must be a string');
        }

        $scheme = strtolower(str_replace('://', '', $scheme));

        if ($scheme !== '' && !array_key_exists($scheme, $this->schemes)) {
            throw new InvalidArgumentException(
                sprintf('Scheme must be one of [%s] or empty string', join(",", array_keys($this->schemes)))
            );
        }

        return $scheme;
    }

    /**
     * @param string $host
     * @return string
     */
    private function filterHost($host): string
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException('Host must be a string');
        }

        return strtolower($host);
    }

    /**
     * @param int|null $port
     * @return int|null
     */
    private function filterPort($port): ?int
    {
        if (is_null($port)) {
            return null;
        }

        $port = (int)$port;

        if (0 > $port || 65535 < $port) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid port: %d. Port must be between 1 and 65535 or null', $port)
            );
        }
        $scheme = $this->scheme;

        return (!isset($this->schemes[$scheme]) || $port !== $this->schemes[$scheme]) ? $port : null;
    }

    /**
     * Filters the path of a URI
     *
     * @param string $path The raw uri path.
     * @return string Percent-encoded uri path
     */
    private function filterPath($path): string
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }

        $match = $this->pregReplace(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            $path
        );

        return is_string($match) ? $match : '';
    }

    /**
     * Filters the query string of a URI.
     *
     * @param mixed $query
     *
     * @return string
     */
    private function filterQuery($query): string
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException('Query must be a string.');
        }

        $match = $this->pregReplace(self::FILTER_PATTERN, $query);

        return is_string($match) ? $match : '';
    }

    /**
     * @param string $pattern
     * @param string $query
     * @return string|string[]|null
     */
    private function pregReplace($pattern, $query)
    {
        return preg_replace_callback($pattern, function ($match) {
            return rawurlencode($match[0]);
        }, $query);
    }

    /**
     * Filters fragment of a URI.
     *
     * @param mixed $fragment
     *
     * @return string
     */
    private function filterFragment($fragment): string
    {
        if (!is_string($fragment)) {
            throw new InvalidArgumentException('Fragment must be a string.');
        }

        $match = $this->pregReplace(self::FILTER_PATTERN, ltrim($fragment, '#'));

        return is_string($match) ? $match : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        $host = $this->getHost();
        $port = $this->getPort();
        $userInfo = $this->getUserInfo();

        $user = ($userInfo !== '') ? $userInfo . '@' : '';
        $port = ($port !== null) ? ':' . $port : '';

        return $user . $host . $port;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return ($this->password !== '') ? $this->user . ':' . $this->password : $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $this->filterUserInfo($user);

        if ($clone->user !== '') {
            $clone->password = $this->filterUserInfo($password);
        } else {
            $clone->password = '';
        }

        return $clone;
    }

    /**
     * Filters the user info string.
     *
     * @param string|null $info
     * @return string
     */
    protected function filterUserInfo(?string $info = null): string
    {
        if (!is_string($info)) {
            return '';
        }

        $match = $this->pregReplace(self::FILTER_PATTERN_USER_INFO, $info);

        return is_string($match) ? $match : '';
    }

    /**
     * Join components in one string
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     * @return string
     */
    public function joinComponents($scheme, $authority, $path, $query, $fragment)
    {
        return (($scheme !== '') ? $scheme . ':' : '')
            . (($authority !== '') ? '//' . $authority : '')
            . '/' . ltrim($path, '/')
            . (($query !== '') ? '?' . $query : '')
            . (($fragment !== '') ? '#' . $fragment : '');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->joinComponents(
            $this->getScheme(),
            $this->getAuthority(),
            $this->getPath(),
            $this->getQuery(),
            $this->getFragment()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $this->filterHost($host);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path must be a string');
        }

        $clone = clone $this;
        $clone->path = $this->filterPath($path);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $query = ltrim($this->filterQuery($query), '?');
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $fragment = $this->filterFragment($fragment);
        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }
}
