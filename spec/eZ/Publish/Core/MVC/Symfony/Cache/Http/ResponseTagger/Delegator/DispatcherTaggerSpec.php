<?php

namespace spec\eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator\DispatcherTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class DispatcherTaggerSpec extends ObjectBehavior
{
    function let(ResponseTagger $taggerOne, ResponseTagger $taggerTwo)
    {
        $this->beConstructedWith([$taggerOne, $taggerTwo]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DispatcherTagger::class);
    }

    function it_calls_tag_on_every_tagger(
        ResponseTagger $taggerOne,
        ResponseTagger $taggerTwo,
        ResponseCacheConfigurator $configurator,
        Response $response,
        ValueObject $value
    )
    {
        $this->tag($configurator, $response, $value);

        $taggerOne->tag($configurator, $response, $value)->shouldHaveBeenCalled();
        $taggerTwo->tag($configurator, $response, $value)->shouldHaveBeenCalled();
    }
}
