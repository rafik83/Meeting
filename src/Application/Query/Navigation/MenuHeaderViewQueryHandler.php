<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\View\Navigation\MenuHeaderView;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;

class MenuHeaderViewQueryHandler
{
    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * MenuHeaderViewQueryHandler constructor.
     *
     * @param SheetGuesser                    $sheetGuesser
     * @param NotificationRepositoryInterface $notificationRepository
     * @param RouterInterface                 $router
     */
    public function __construct(
        SheetGuesser $sheetGuesser,
        NotificationRepositoryInterface $notificationRepository,
        RouterInterface $router
    ) {
        $this->sheetGuesser           = $sheetGuesser;
        $this->notificationRepository = $notificationRepository;
        $this->router                 = $router;
    }

    /**
     * @param MenuHeaderViewQuery $query
     *
     * @return MenuHeaderView
     * @throws \Exception
     */
    public function handle(MenuHeaderViewQuery $query)
    {
        $routes          = [];
        $hasNotification = false;

        foreach ($query->event->getLocales() as $locale) {
            if ($locale !== $query->locale) {
                $routes[$locale] = $this->router->generate('event_sheet_locale', ['locale' => $locale]);
            }
        }

        if ($query->user !== null) {
            $sheet           = $this->sheetGuesser->getUserSheet($query->user, $query->event, $query->locale);
            $hasNotification = $this->notificationRepository->sheetHasNotification($sheet);
        }

        return new MenuHeaderView($query->event, $routes, $hasNotification);
    }
}
