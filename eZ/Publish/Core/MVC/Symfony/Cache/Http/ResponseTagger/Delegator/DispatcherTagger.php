<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Dispatches a value to all registered ResponseTaggers.
 */
class DispatcherTagger implements ResponseTagger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $taggers = [];

    public function __construct(array $taggers = [])
    {
        $this->taggers = $taggers;
    }

    public function tag( ResponseCacheConfigurator $configurator, Response $response, $value)
    {
        foreach ($this->taggers as $tagger) {
            $tagger->tag($configurator, $response, $value);
        }
    }
}
