<?php declare(strict_types=1);

namespace Hawk\Psr7;

use InvalidArgumentException;

/**
 * Class Headers
 * @package Hawk\Psr7
 *
 * @link https://github.com/baimurzaev/Psr7 GitHub main page
 */
class Headers
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    private $headerNames = [];

    /**
     * Headers constructor.
     * @param array $headers
     */
    public function __construct($headers = [])
    {
        if (is_array($headers)) {
            $this->setHeaders($headers);
        }
    }

    /**
     * @param array $headers
     * @return Headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = [];

        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Headers
     */
    public function addHeader(string $name, $value): self
    {
        $this->validateHeaderName($name);
        $normalized = strtolower($name);
        $value = $this->validateAndTrimHeader($name, $value);

        if (isset($this->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $this->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $this->headerNames[$normalized] = $name;
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return Headers
     */
    public function removeHeader(string $name): self
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];
        unset($this->headers[$header], $this->headerNames[$normalized]);

        return $this;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getHeader(string $name): array
    {
        $normalized = strtolower($name);

        if (!array_key_exists($normalized, $this->headerNames)) {
            return [];
        }

        $normalized = $this->headerNames[$normalized];

        return $this->headers[$normalized];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Headers
     */
    public function setHeader(string $name, $value): self
    {
        $value = $this->validateAndTrimHeader($name, $value);
        $normalized = strtolower($name);

        if ($this->hasHeader($normalized)) {
            unset($this->headers[$this->headerNames[$normalized]]);
        }

        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->headerNames);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @param mixed $values
     * @return array
     */
    private function validateAndTrimHeader(string $name, $values): array
    {
        $headerValues = [];
        $this->validateHeaderName($name);
        $values = (is_array($values)) ? $values : [$values];

        foreach ($values as $value) {
            $this->validateHeaderValue($value);
            $headerValues[] = trim((string)$value, " \t");
        }

        return $headerValues;
    }

    /**
     * Make sure the header complies with RFC 7230.
     *
     * Header names must be a non-empty string consisting of token characters.
     *
     * Header values must be strings consisting of visible characters with all optional
     * leading and trailing whitespace stripped. This method will always strip such
     * optional whitespace. Note that the method does not allow folding whitespace within
     * the values as this was deprecated for almost all instances by the RFC.
     *
     * header-field = field-name ":" OWS field-value OWS
     * field-name   = 1*( "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." / "^"
     *              / "_" / "`" / "|" / "~" / %x30-39 / ( %x41-5A / %x61-7A ) )
     * OWS          = *( SP / HTAB )
     * field-value  = *( ( %x21-7E / %x80-FF ) [ 1*( SP / HTAB ) ( %x21-7E / %x80-FF ) ] )
     *
     * https://tools.ietf.org/html/rfc7230#section-3.2.6
     *
     * @param string $name
     */
    private function validateHeaderName($name)
    {
        if (!is_string($name) || $name === '' || preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $name) !== 1) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string');
        }
    }

    /**
     * @param mixed $value
     */
    private function validateHeaderValue($value)
    {
        if (is_array($value) && empty($value)) {
            throw new InvalidArgumentException(
                'Header values must be a string or an array of strings, empty array given.'
            );
        }

        if (!is_array($value) && ((!is_numeric($value) && !is_string($value))
                || preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string)$value) !== 1)) {
            throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
        }
    }
}
