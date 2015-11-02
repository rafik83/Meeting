<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @param RouterInterface          $router
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(RouterInterface $router, EventRepositoryInterface $eventRepository)
    {
        $this->router          = $router;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Redirect to event fallback locale
     *
     * @param GetResponseEvent $getResponseEvent
     */
    public function onKernelRequest(GetResponseEvent $getResponseEvent)
    {
        if (!$getResponseEvent->isMasterRequest()) {
            return;
        }

        $request = $getResponseEvent->getRequest();
        $domain  = $request->getHost();
        $params  = $request->attributes->get('_route_params', []);
        $route   = $request->attributes->get('_route');
        $event   = $this->eventRepository->getEventByDomain($domain);

        if (!$event) {
            return;
        }

        // No locale
        if ($route === 'event_root') {
            $params['_locale'] = $event->getFallback();
            $path              = $this->router->generate('event', $params);
            $getResponseEvent->setResponse(new RedirectResponse($path));

            return;
        }

        // Unknow locale
        if (!$event->hasLocale($request->getLocale())) {
            $params['_locale'] = $event->getFallback();
            $route             = $request->attributes->get('_route');
            $path              = $this->router->generate($route, $params);
            $getResponseEvent->setResponse(new RedirectResponse($path));

            return;
        }
    }
}
