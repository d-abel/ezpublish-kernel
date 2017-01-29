<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use Symfony\Component\HttpFoundation\Response;

/**
 * Configures caching options of an HTTP Response.
 */
class ResponseCacheConfigurator
{
    /**
     * Enables cache on a Response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return self
     */
    public function enableCache(Response $response)
    {
        $response->setPublic();

        return $this;
    }

    /**
     * Sets the shared-max-age property of a Response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param int $ttl
     * @param bool $replace If true, any existing value will be replaced
     *
     * @return self
     */
    public function setSharedMaxAge(Response $response, $ttl, $replace = false)
    {
        if (!$response->headers->hasCacheControlDirective('s-maxage') || $replace === true) {
            $response->setSharedMaxAge($ttl);
        }

        return $this;
    }

    /**
     * Adds $tags to the response's cache tags header.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string|array $tags Single tag, or array of tags
     *
     * @return self
     */
    public function addTags(Response $response, $tags)
    {
        $response->headers->set('xkey', $tags, false);

        return $this;
    }
}
