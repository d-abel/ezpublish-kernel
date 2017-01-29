<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\Repository\Values\Content\Content;

/**
 * Delegates tagging of a Content to the ContentInfo tagger.
 */
class ContentTagger implements ResponseTagger
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
        if (!$value instanceof Content) {
            return $this;
        }

        $this->contentInfoTagger->tag($configurator, $response, $value->contentInfo);

        return $this;
    }
}
