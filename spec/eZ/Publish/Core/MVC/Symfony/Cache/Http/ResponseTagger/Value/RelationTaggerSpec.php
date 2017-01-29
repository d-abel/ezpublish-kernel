<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value\RelationValueTagger;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class RelationValueTaggerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RelationValueTagger::class);
    }

    function it_tags_with_the_destination_relation_id(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new Relation([
            'sourceContentInfo' => new ContentInfo(['id' => 654]),
            'destinationContentInfo' => new ContentInfo(['id' => 123]),
        ]);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, 'relation-123')->shouldHaveBeenCalled();
    }
}
