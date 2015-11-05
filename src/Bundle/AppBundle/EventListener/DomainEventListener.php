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
        $event   = $this->eventRepository->getEventByDomain($request->getHost());

        if (!$event) {
            return;
        }

        if (!$event->hasLocale($request->getLocale())) {
            $path = $this->router->generate(
                $request->attributes->get('_route'),
                array_merge($request->attributes->get('_route_params', []), ['_locale' => $event->getFallback()])
            );
            $getResponseEvent->setResponse(new RedirectResponse($path));

            return;
        }

        if ($request->attributes->get('_route') === 'default_event') {
            $path = $this->router->generate(
                'event',
                array_merge($request->attributes->get('_route_params', []), ['_locale' => $event->getFallback()])
            );
            $getResponseEvent->setResponse(new RedirectResponse($path));

            return;
        }
    }
}
