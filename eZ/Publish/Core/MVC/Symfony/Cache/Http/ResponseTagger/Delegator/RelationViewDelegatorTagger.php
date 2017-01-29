<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\RelationView;
use Symfony\Component\HttpFoundation\Response;

class RelationViewDelegatorTagger implements ResponseTagger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $relationTagger;

    public function __construct(ResponseTagger $relationTagger)
    {
        $this->relationTagger = $relationTagger;
    }

    public function tag(ResponseCacheConfigurator $configurator, Response $response, $view)
    {
        if (!$view instanceof RelationView) {
            return $this;
        }

        foreach ($view->getRelations() as $relation) {
            $this->relationTagger->tag($configurator, $response, $relation);
        }

        return $this;
    }
}
