<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator\ContentTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Response;

class ContentTaggerSpec extends ObjectBehavior
{
    function let(ResponseTagger $contentInfoTagger)
    {
        $this->beConstructedWith($contentInfoTagger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentTagger::class);
    }

    function it_ignores_non_content(
        ResponseCacheConfigurator $configurator,
        Response $response
    )
    {
        $this->tag($configurator, $response, null);
        $configurator->addTags()->shouldNotHaveBeenCalled();
    }

    function it_delegates_to_content_info_tagger(
        ResponseCacheConfigurator $configurator,
        Response $response,
        ResponseTagger $contentInfoTagger
    )
    {
        $contentInfo = new ContentInfo();
        $content = new Content(['versionInfo' => new VersionInfo(['contentInfo' => $contentInfo])]);
        $this->tag($configurator, $response, $content);

        $contentInfoTagger
            ->tag($configurator, $response, $contentInfo)
            ->shouldHaveBeenCalled();
    }
}
