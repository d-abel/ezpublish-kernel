<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CacheViewResponseListenerSpec extends ObjectBehavior
{
    function let(
        FilterResponseEvent $event,
        Request $request,
        Response $response,
        ParameterBag $requestAttributes
    ) {
        $request->attributes = $requestAttributes;
        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($response);

        $this->beConstructedWith(true, false, 0);
    }

    function it_does_not_enable_cache_if_the_view_is_not_cachable(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        ParameterBag $requestAttributes,
        View $nonCachableView
    ) {
        $requestAttributes->get('view')->willReturn($nonCachableView);
        $configurator->enableCache()->shouldNotBecalled();

        $this->configureCache($event);
    }

    function it_does_not_enable_cache_if_view_cache_is_disabled(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes,
        Response $response
    ) {
        $this->beConstructedWith(true, false, false);
        $requestAttributes->get('view')->shouldBeCalled();
        $configurator->enableCache()->shouldNotBecalled();

        $this->configureCache($event);
    }

    function it_does_not_enable_cache_if_it_is_disabled_in_the_view(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes
    ) {
        $requestAttributes->get('view')->willReturn($view);
        $view->isCacheEnabled()->willReturn(false);
        $configurator->enableCache()->shouldNotBecalled();

        $this->configureCache($event);
    }

    function it_enables_cache_if_enabled(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes,
        ResponseTagger $dispatcherTagger
    ) {
        $this->setConfigurator($configurator);
        $this->setDispatcherTagger($dispatcherTagger);

        $requestAttributes->get('view')->willReturn($view);
        $view->isCacheEnabled()->willReturn(true);

        $this->configureCache($event);

        $configurator->enableCache(Argument::type(Response::class))->shouldHaveBeenCalled();
    }

    function it_sets_shared_maxage_if_enabled(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes,
        ResponseTagger $dispatcherTagger
    ) {
        $this->beConstructedWith(true, true, 30);
        $this->setConfigurator($configurator);
        $this->setDispatcherTagger($dispatcherTagger);

        $requestAttributes->get('view')->willReturn($view);
        $view->isCacheEnabled()->willReturn(true);

        $this->configureCache($event);
        $configurator->setSharedMaxAge(Argument::type(Response::class), 30)->shouldHaveBeenCalled();
    }

    function it_delegates_tagging_to_the_dispatcher_tagger(
        FilterResponseEvent $event,
        ResponseCacheConfigurator $configurator,
        CachableView $view,
        ParameterBag $requestAttributes,
        ResponseTagger $dispatcherTagger
    ) {
        $this->setConfigurator($configurator);
        $this->setDispatcherTagger($dispatcherTagger);

        $requestAttributes->get('view')->willReturn($view);
        $view->isCacheEnabled()->willReturn(true);

        $this->configureCache($event);
        $dispatcherTagger->tag($configurator, Argument::type(Response::class), $view)->shouldHaveBeenCalled();
    }
}
