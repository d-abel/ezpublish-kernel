<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use Symfony\Component\HttpFoundation\Response;

class RelationValueTagger implements ResponseTagger
{
    public function tag(ResponseCacheConfigurator $configurator, Response $response, $relation)
    {
        if (!$relation instanceof Relation) {
            return $this;
        }

        $configurator->addTags(
            $response,
            'relation-' . $relation->getDestinationContentInfo()->id
        );
    }
}
