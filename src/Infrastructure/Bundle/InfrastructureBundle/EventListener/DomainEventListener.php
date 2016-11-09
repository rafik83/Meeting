<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

class DomainEventListener
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var array
     */
    private $ignoredRoutes = [
        'liip_imagine_filter_runtime',
        'liip_imagine_filter',
        'payum_capture_do_session',
        'payum_capture_do',
        'payum_notify_do_unsafe',
        'payum_notify_do',
        'payum_authorize_do',
        '_wdt',
        '_profiler',
        '_errors',
    ];

    /**
     * @param RouterInterface          $router
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(RouterInterface $router, EventRepositoryInterface $eventRepository)
    {
        $this->router          = $router;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Redirect to event fallback locale.
     *
     * @param GetResponseEvent $getResponseEvent
     */
    public function onKernelRequest(GetResponseEvent $getResponseEvent)
    {
        if (!$getResponseEvent->isMasterRequest()) {
            return;
        }

        $request = $getResponseEvent->getRequest();
        $route   = $request->attributes->get('_route');

        if (in_array($route, $this->ignoredRoutes)) {
            return;
        }

        $event = $this->eventRepository->getEventByDomain($request->getHost());

        if (!$event) {
            return;
        }

        // If the locale is not in event locales, redirect to the fallback locale
        if (!$event->hasLocale($request->getLocale())) {
            if ($route === 'default_event') {
                $route = 'event';
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
     * @param Request $request
     * @param Event   $event
     * @param string  $route
     *
     * @return RedirectResponse
     */
    private function createRedirectResponse(Request $request, Event $event, $route)
    {
        $path = $this->router->generate(
            $route,
            array_merge($request->attributes->get('_route_params', []), ['_locale' => $event->getFallback()])
        );

        return new RedirectResponse($path, 301);
    }
}
