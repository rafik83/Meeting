<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\View\Navigation\MenuHeaderView;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class HeaderViewQueryHandler
{
    /** @var NotificationRepositoryInterface */
    private $notificationRepository;

    /** @var RouterInterface */
    private $router;

    /**
     * @param NotificationRepositoryInterface $notificationRepository
     * @param RouterInterface                 $router
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        RouterInterface $router
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->router                 = $router;
    }

    /**
     * @param HeaderViewQuery $query
     *
     * @return MenuHeaderView
     */
    public function handle(HeaderViewQuery $query)
    {
        $routes = [];

        foreach ($query->event->getLocales() as $locale) {
            if ($locale !== $query->locale) {
                $routes[$locale] = $this->router->generate($query->route, array_merge(
                    $query->routeParameters,
                    ['_locale' => $locale]
                ));
            }
        }

        // use dynamic header menu if user is logged in and not in registration funnel
        if (null !== $query->user && null !== $query->sheet && false === $query->registration) {
            try {
                $hasNotification = $this->notificationRepository->sheetHasNotification($query->sheet);

                return new MenuHeaderView($query->event, $routes, $query->sheet, $hasNotification);
            } catch (\Exception $exception) {
                return new MenuHeaderView($query->event, $routes);
            }
        }

        return new MenuHeaderView($query->event, $routes);
    }
}
