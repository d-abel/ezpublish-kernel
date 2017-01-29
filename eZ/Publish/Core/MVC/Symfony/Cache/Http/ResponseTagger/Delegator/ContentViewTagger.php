<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\View\RelationView;

class ContentViewTagger
{
    /**
     * @var ResponseTagger
     */
    private $contentInfoTagger;

    /**
     * @var ResponseTagger
     */
    private $locationTagger;

    /**
     * @var ResponseTagger
     */
    private $relationTagger;

    public function __construct(
        ResponseTagger $contentInfoTagger,
        ResponseTagger $locationTagger,
        ResponseTagger $relationTagger
    )
    {
        $this->contentInfoTagger = $contentInfoTagger;
        $this->locationTagger = $locationTagger;
        $this->relationTagger = $relationTagger;
    }

    public function tag( ResponseCacheConfigurator $configurator, Response $response, $value)
    {
        if (!$value instanceof ContentView) {
            return $this;
        }

        if ($value instanceof ContentValueView && ($content = $value->getContent()) instanceof Content) {
            $this->contentInfoTagger->tag($configurator, $response, $content->contentInfo);
        }

        if ($value instanceof LocationValueView && ($location = $value->getLocation()) instanceof Location) {
            $this->locationTagger->tag($configurator, $response, $location);
        }

        if ($value instanceof RelationView) {
            foreach ($value->getRelations() as $relation) {
                $this->relationTagger->tag($configurator, $response, $relation);
            }
        }

        return $this;
    }
}
