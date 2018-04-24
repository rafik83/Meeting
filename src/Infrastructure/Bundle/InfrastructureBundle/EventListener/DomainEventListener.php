<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DomainEventListener extends AbstractRedirectToEventListener
{
    /**
     * @var array
     */
    private static $ignoredRoutes = [
        'liip_imagine_filter_runtime',
        'liip_imagine_filter',
        'payum_capture_do_session',
        'payum_capture_do',
        'payum_notify_do_unsafe',
        'payum_notify_do',
        'payum_authorize_do',
        '_wdt',
        '_profiler',
        '_profiler_home',
        '_profiler_search',
        '_profiler_search_bar',
        '_profiler_search_results',
        '_profiler_phpinfo',
        '_profiler_open_file',
        '_profiler_router',
        '_profiler_exception',
        '_profiler_exception_css',
        '_errors',
    ];

    /**
     * Redirect to event fallback locale.
     *
     * @param GetResponseEvent $getResponseEvent
     */
    public function onKernelRequest(GetResponseEvent $getResponseEvent)
    {
        $this->handleRedirect($getResponseEvent);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRedirect(GetResponseEvent $getResponseEvent, Request $request, Event $event, $locale, $route)
    {
        // If the locale is not in event locales, redirect to the fallback locale
        if (!$event->hasLocale($locale)) {
            if ($route === Route::DEFAULT_EVENT) {
                $route = Route::EVENT;
            }

            $getResponseEvent->setResponse($this->createRedirectResponse($request, $event, $route));

            return;
        }

        // If no locale in the url, redirect to the fallback locale
        if ($route === 'default_event') {
            $getResponseEvent->setResponse($this->createRedirectResponse($request, $event, 'event'));

            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function isIgnoredRoute($route)
    {
        return in_array($route, self::$ignoredRoutes);
    }
}
