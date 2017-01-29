<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use Symfony\Component\HttpFoundation\Response;

class LocationValueResponseTagger implements ResponseTagger
{
    public function tag(ResponseCacheConfigurator $configurator, Response $response, $view)
    {
        if (!$view instanceof LocationValueView || !($location = $view->getLocation()) instanceof Location) {
            return $this;
        }

        $contentInfo = $location->getContentInfo();
        $tags = [
            'content-' . $location->contentId,
            'location-' . $location->id,
            'parent-' . $location->parentLocationId,
            'content-type-' . $contentInfo->contentTypeId,
        ] + array_map(
            function ($pathItem) {
                return 'path-' . $pathItem;
            },
            $location->path
        );

        if ($location->id != $contentInfo->mainLocationId) {
            $tags[] = 'location-' . $contentInfo->mainLocationId;
        }

        $configurator->addTags($response, $tags);

        return $this;
    }
}
