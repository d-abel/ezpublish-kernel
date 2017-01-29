<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\RelationView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response cache properties.
 */
class CacheViewResponseListener implements EventSubscriberInterface
{
    /**
     * True if view cache is enabled, false if it is not.
     *
     * @var bool
     */
    private $enableViewCache;

    /**
     * True if TTL cache is enabled, false if it is not.
     * @var bool
     */
    private $enableTtlCache;

    /**
     * Default ttl for ttl cache.
     *
     * @var int
     */
    private $defaultTtl;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger[]
     */
    private $taggers = [];

    /**
     * @var ResponseTagger
     */
    private $dispatcherTagger;

    /**
     * @var ResponseCacheConfigurator
     */
    private $responseConfigurator;

    public function __construct($enableViewCache, $enableTtlCache, $defaultTtl)
    {
        $this->enableViewCache = $enableViewCache;
        $this->enableTtlCache = $enableTtlCache;
        $this->defaultTtl = $defaultTtl;
    }

    public function setConfigurator(ResponseCacheConfigurator $configurator)
    {
        $this->responseConfigurator = $configurator;
    }

    public function setDispatcherTagger(ResponseTagger $dispatcherTagger)
    {
        $this->dispatcherTagger = $dispatcherTagger;
    }

    public function addTagger(ResponseTagger $tagger)
    {
        $this->taggers[] = $tagger;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'configureCache'];
    }

    public function configureCache(FilterResponseEvent $event)
    {
        if (!($view = $event->getRequest()->attributes->get('view')) instanceof CachableView) {
            return;
        }

        if (!$this->enableViewCache || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();
        $this->responseConfigurator->enableCache($response);

        // Tag response so it can be invalidated by tag/key.
        $this->dispatcherTagger->tag($this->responseConfigurator, $response, $view);

        if ($this->enableTtlCache) {
            $this->responseConfigurator->setSharedMaxAge($response, $this->defaultTtl);
        }
    }
}
