<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class UriTest
 * @package Hawk\Tests\Psr7
 */
class UriTest extends TestCase
{
    const URL = "https://user:pass@example.com:81/foo/bar/path.html?a=b&c=d#fragment";

    /**
     * @return Uri
     */
    public function factory()
    {
        return new Uri(self::URL);
    }

    public function testScheme()
    {
        // https
        $this->assertEquals('https', $this->factory()->getScheme());

        // http
        $uri = $this->factory()->withScheme('http');
        $this->assertEquals('http', $uri->getScheme());

        // empty
        $uri = $this->factory()->withScheme('');
        $this->assertEquals('', $uri->getScheme());

        // removes suffix
        $uri = $this->factory()->withScheme('http://');
        $this->assertEquals('http', $uri->getScheme());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Scheme must be one of [http,https] or empty string
     */
    public function testWithSchemeInvalid()
    {
        $this->factory()->withScheme('torrent');

        // invalid type
        $this->factory()->withScheme(true);
    }

    public function testGetAuthority()
    {
        $this->assertEquals('user:pass@example.com:81', $this->factory()->getAuthority());
    }

    public function testWithUserInfoAndGetUserInfo()
    {
        $uri = $this->factory()->withUserInfo('hawk', '123456');
        $this->assertEquals('hawk:123456', $uri->getUserInfo());
    }

    public function testWithUserInfoClear()
    {
        $uri = $this->factory()->withUserInfo('hawk', '123456');
        $uri = $uri->withUserInfo('');
        $this->assertEquals('', $uri->getUserInfo());
    }

    public function testGetHost()
    {
        $this->assertEquals('example.com', $this->factory()->getHost());
    }

    public function testWithHost()
    {
        $uri = $this->factory()->withHost('example.com');
        $this->assertEquals('example.com', $uri->getHost());
    }

    public function testGetPort()
    {
        $uri = new Uri('https://www.example.com:8080');
        $this->assertEquals(8080, $uri->getPort());
    }

    public function testGetPortWithoutPort()
    {
        $uri = new Uri('example.com');
        $this->assertNull($uri->getPort());
    }

    public function testWithPort()
    {
        $uri = $this->factory()->withPort(8080);
        $this->assertEquals(8080, $uri->getPort());
    }

    public function testWithPortNull()
    {
        $uri = $this->factory()->withPort(null);
        $this->assertEquals(null, $uri->getPort());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithPortInvalid()
    {
        $this->factory()->withPort(65555);
    }

    public function testGetPath()
    {
        $uri = new Uri('http://example.com/about');
        $this->assertEquals('/about', $uri->getPath());
    }

    public function testWithPath()
    {
        $uri = $this->factory()->withPath('/about');
        $this->assertEquals('/about', $uri->getPath());

        // without prefix
        $uri = $this->factory()->withPath('path');
        $this->assertEquals('path', $uri->getPath());

        // path empty
        $uri = $this->factory()->withPath('');
        $this->assertEquals('', $uri->getPath());
    }

    public function testToString()
    {
        $uri = $this->factory();
        $this->assertEquals("https://user:pass@example.com:81/foo/bar/path.html?a=b&c=d#fragment", (string)$uri);

        $uri = $uri->withPath('/bar/foo/path.html');
        $this->assertEquals("https://user:pass@example.com:81/bar/foo/path.html?a=b&c=d#fragment", (string)$uri);

        $uri = $uri->withPath('/path/');
        $this->assertEquals("https://user:pass@example.com:81/path/?a=b&c=d#fragment", (string)$uri);
    }

    public function testGetQuery()
    {
        $this->assertEquals('a=b&c=d', $this->factory()->getQuery());
    }

    public function testWithQuery()
    {
        $uri = $this->factory()->withQuery('c=d');
        $this->assertEquals('c=d', $uri->getQuery());
    }

    public function testWithQueryPrefixRemoves()
    {
        $uri = $this->factory()->withQuery('?test=prefix');
        $this->assertEquals('test=prefix', $uri->getQuery());
    }

    public function testGetFragment()
    {
        $this->assertEquals('fragment', $this->factory()->getFragment());
    }

    public function testWithFragment()
    {
        $uri = $this->factory()->withFragment('fragment-with');
        $this->assertEquals('fragment-with', $uri->getFragment());
    }

    public function testWithFragmentPrefixRemoves()
    {
        $uri = $this->factory()->withFragment('#other-fragment');
        $this->assertEquals('other-fragment', $uri->getFragment());
    }


    public function testWithFragmentEmpty()
    {
        $uri = $this->factory()->withFragment('');
        $this->assertEquals('', $uri->getFragment());
    }
}
