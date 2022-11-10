<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Factory;

use Hawk\Psr7\Factory\UriFactory;
use Interop\Http\Factory\UriFactoryTestCase;

/**
 * Class UriFactoryTest
 * @package Hawk\Tests\Psr7\Factory
 */
class UriFactoryTest extends UriFactoryTestCase
{
    /**
     * @return UriFactory|\Psr\Http\Message\UriFactoryInterface
     */
    protected function createUriFactory()
    {
        return new UriFactory();
    }

    public function testGetAuthorityHost()
    {
        $uri = $this->createUriFactory()->createUri('https://example.com/foo');
        $this->assertEquals('example.com', $uri->getAuthority());
    }

    public function testAuthorityPassword()
    {
        $uri = $this->factory->createUri('https://user:pass@example.com/foo');
        $this->assertEquals('user:pass@example.com', $uri->getAuthority());
    }

    public function testAuthorityHostAndPort()
    {
        $uri = $this->factory->createUri('https://example.com:81/foo');
        $this->assertEquals('example.com:81', $uri->getAuthority());
    }

    public function testUserInfoUser()
    {
        $uri = $this->factory->createUri('https://user:pass@example.com/foo');
        $this->assertEquals('user:pass', $uri->getUserInfo());
    }

    public function testUserInfoUserAndPasswordEncodes()
    {
        $uri = $this->factory->createUri('https://user%40:pass%3A@example.com:81/foo');
        $this->assertEquals('user%40:pass%3A', $uri->getUserInfo());
    }

    public function testGetUserInfo()
    {
        $uri = $this->factory->createUri('https://user@example.com/foo');
        $this->assertEquals('user', $uri->getUserInfo());
    }

    public function testGetUserInfoWithoutUsername()
    {
        $uri = $this->factory->createUri('https://example.com/foo');
        $this->assertEquals('', $uri->getUserInfo());
    }

    public function testConstructCreateUri()
    {
        $uri = $this->factory->createUri('http://example.com:81/foo/bar?baz=xyz#fragment');
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('81', $uri->getPort());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('baz=xyz', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
    }
}
