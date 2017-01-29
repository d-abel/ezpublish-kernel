<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Value\LocationTagger;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class LocationTaggerSpec extends ObjectBehavior
{
    function let(ResponseTagger $contentInfoTagger)
    {
        $this->beConstructedWith($contentInfoTagger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocationTagger::class);
    }

    function it_ignores_non_location(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $this->tag($configurator, $response, null);

        $configurator->addTags()->shouldNotHaveBeenCalled();
    }

    function it_delegates_tagging_the_content_info(
        ResponseCacheConfigurator $configurator,
        ResponseTagger $contentInfoTagger,
        Response $response)
    {
        $contentInfo = new ContentInfo();
        $value = new Location(['contentInfo' => $contentInfo]);

        $this->tag($configurator, $response, $value);

        $contentInfoTagger->tag($configurator, $response, $contentInfo)->shouldHaveBeenCalled();
    }

    function it_tags_with_parent_location_id(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new Location(['parentLocationId' => 123]);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['parent-123'])->shouldHaveBeenCalled();
    }

    function it_tags_with_path_items(
        ResponseCacheConfigurator $configurator,
        Response $response)
    {
        $value = new Location(['pathString' => '/1/2/123']);

        $this->tag($configurator, $response, $value);

        $configurator->addTags($response, ['path-1', 'path-2', 'path-123'])->shouldHaveBeenCalled();
    }
}
