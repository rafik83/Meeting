<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\View\Navigation\MenuHeaderView;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class HeaderViewQueryHandler
{
    /** @var NotificationRepositoryInterface */
    private $notificationRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RouterInterface */
    private $router;

    /**
     * @param NotificationRepositoryInterface $notificationRepository
     * @param SheetRepositoryInterface        $sheetRepository
     * @param RouterInterface                 $router
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        SheetRepositoryInterface $sheetRepository,
        RouterInterface $router
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->sheetRepository        = $sheetRepository;
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
                $hasMultipleSheets = 1 < $this->sheetRepository->countSheetsByUserAndEvent($query->user, $query->event);

                return new MenuHeaderView($query->event, $routes, $query->sheet, $hasNotification, $hasMultipleSheets);
            } catch (\Exception $exception) {
                return new MenuHeaderView($query->event, $routes);
            }
        }

        return new MenuHeaderView($query->event, $routes);
    }
}
