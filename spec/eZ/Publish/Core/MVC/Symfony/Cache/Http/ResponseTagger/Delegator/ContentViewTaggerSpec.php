<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator\ContentViewTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class ContentViewTaggerSpec extends ObjectBehavior
{
    function let(
        ResponseTagger $contentInfoTagger,
        ResponseTagger $locationTagger,
        ResponseTagger $relationTagger,
        ContentView $contentView
    ) {
        $this->beConstructedWith($contentInfoTagger, $locationTagger, $relationTagger);

        $contentView->getContent()->willReturn(null);
        $contentView->getLocation()->willReturn(null);
        $contentView->getRelations()->willReturn([]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentViewTagger::class);
    }

    function it_delegates_tagging_of_the_content(
        ResponseCacheConfigurator $configurator,
        ResponseTagger $contentInfoTagger,
        Response $response,
        ContentView $contentView
    ) {
        $contentInfo = new ContentInfo();
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => $contentInfo])]);
        $contentView->getContent()->willReturn($content);

        $this->tag($configurator, $response, $contentView);

        $contentInfoTagger->tag($configurator, $response, $contentInfo)->shouldHaveBeenCalled();
    }

    function it_delegates_tagging_of_the_location(
        ResponseCacheConfigurator $configurator,
        ResponseTagger $locationTagger,
        Response $response,
        ContentView $contentView
    ) {
        $location = new Location();
        $contentView->getLocation()->willReturn($location);

        $this->tag($configurator, $response, $contentView);

        $locationTagger->tag($configurator, $response, $location)->shouldHaveBeenCalled();
    }

    function it_delegates_tagging_of_relations(
        ResponseCacheConfigurator $configurator,
        ResponseTagger $relationTagger,
        Response $response,
        ContentView $contentView
    ) {
        $relationOne = new Relation();
        $relationTwo = new Relation();
        $relations = [$relationOne, $relationTwo];
        $contentView->getRelations()->willReturn($relations);

        $this->tag($configurator, $response, $contentView);

        $relationTagger->tag($configurator, $response, $relationOne)->shouldHaveBeenCalled();
        $relationTagger->tag($configurator, $response, $relationTwo)->shouldHaveBeenCalled();
    }
}
