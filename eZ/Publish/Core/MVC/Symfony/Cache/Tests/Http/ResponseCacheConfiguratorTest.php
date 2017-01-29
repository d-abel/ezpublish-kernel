<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;

class ResponseCacheConfiguratorTest extends PHPUnit_Framework_TestCase
{
    public function testEnableCache()
    {
        $configurator = new ResponseCacheConfigurator();

        $response = new Response();
        $configurator->enableCache($response);

        self::assertEquals(
            'public',
            $response->headers->get('cache-control')
        );
    }

    public function testSetSharedMaxAge()
    {
        $configurator = new ResponseCacheConfigurator();

        $response = new Response();
        $configurator->setSharedMaxAge($response, 30);

        self::assertEquals(
            30,
            $response->headers->getCacheControlDirective('s-maxage')
        );
    }

    public function testSetSharedMaxAgeNoReplace()
    {
        $configurator = new ResponseCacheConfigurator();

        $response = new Response();
        $configurator->setSharedMaxAge($response, 30);
        $configurator->setSharedMaxAge($response, 60);

        self::assertEquals(
            30,
            $response->headers->getCacheControlDirective('s-maxage')
        );
    }

    public function testSetSharedMaxAgeReplace()
    {
        $configurator = new ResponseCacheConfigurator();

        $response = new Response();
        $configurator->setSharedMaxAge($response, 30);
        $configurator->setSharedMaxAge($response, 60, true);

        self::assertEquals(
            60,
            $response->headers->getCacheControlDirective('s-maxage')
        );
    }

    public function testAddCacheTags()
    {
        $configurator = new ResponseCacheConfigurator();

        $response = new Response();
    }
}
