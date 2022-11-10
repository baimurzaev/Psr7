<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7\Factory;

use Hawk\Psr7\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class ResponseFactoryTest
 * @package Hawk\Tests\Psr7\Factory
 */
class ResponseFactoryTest extends TestCase
{
    public function testCreateResponse()
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse(404, 'Page not found');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', $response->getReasonPhrase());
    }
}
