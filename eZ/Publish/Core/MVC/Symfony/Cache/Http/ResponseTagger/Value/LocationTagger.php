<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\Repository\Values\Content\Location;

class LocationTagger implements ResponseTagger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $contentInfoTagger;

    public function __construct(ResponseTagger $contentInfoTagger)
    {
        $this->contentInfoTagger = $contentInfoTagger;
    }

    public function tag(ResponseCacheConfigurator $configurator, Response $response, $value)
    {
        if (!$value instanceof Location) {
            return $this;
        }

        $this->contentInfoTagger->tag($configurator, $response, $value->getContentInfo());

        $configurator->addTags($response, ['parent-' . $value->parentLocationId]);
        $configurator->addTags(
            $response,
            array_map(
                function ($pathItem) {
                    return 'path-' . $pathItem;
                },
                $value->path
            )
        );

        return $this;
    }
}
