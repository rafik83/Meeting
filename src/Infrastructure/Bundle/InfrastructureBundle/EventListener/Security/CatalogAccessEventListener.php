<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\AbstractRedirectToEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CatalogAccessEventListener extends AbstractRedirectToEventListener
{
    const ROUTES = [
        'event_catalog_redirect',
        'event_catalog_index',
        'event_catalog_external_index',
        'event_catalog_search_localization',
        'event_catalog_search_keywords',
    ];

    const EXTERNAL_ROUTES = [
        'event_catalog_external_index',
    ];

    /**
     * @var CatalogAccessChecker
     */
    private $catalogAccessChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * CatalogAccessEventListener constructor.
     *
     * @param CatalogAccessChecker     $catalogAccessChecker
     * @param EventRepositoryInterface $eventRepository
     * @param TokenStorageInterface    $tokenStorage
     * @param RouterInterface          $router
     */
    public function __construct(
        CatalogAccessChecker $catalogAccessChecker,
        EventRepositoryInterface $eventRepository,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router
    ) {
        parent::__construct($router, $eventRepository);

        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * @param GetResponseEvent $getResponseEvent
     */
    public function accessChecker(GetResponseEvent $getResponseEvent)
    {
        $this->handleRedirect($getResponseEvent);
    }

    /**
     * {@inheritdoc}
     */
    protected function isIgnoredRoute($route)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRedirect(GetResponseEvent $getResponseEvent, Request $request, Event $event, $locale, $route)
    {
        // check if catalog opened
        if (in_array($route, self::ROUTES)) {
            if (!$this->catalogAccessChecker->allowedToAccess($event)) {
                throw new NotFoundHttpException();
            }
        }

        // redirect for catalog external routes
        if (in_array($route, self::EXTERNAL_ROUTES)) {
            if ($this->tokenStorage->getToken()->getUser() instanceof User
                && $this->catalogAccessChecker->allowedToAccess($event)
            ) {
                $getResponseEvent->setResponse(
                    $this->createRedirectResponse($request, $event, 'event_catalog_redirect', $locale)
                );
            }
        }
    }
}
