<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;

class BadgeScanSubmenuViewQueryHandler
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param BadgeScanSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(BadgeScanSubmenuViewQuery $query): array
    {
        $buttonViews = [];

        $badgeScanTitle = Category::BADGE_SCAN;

        if (null !== $query->staticFormulation) {
            $badgeScanTitle = $query->staticFormulation->getTitle($query->locale);
        }

        $buttonViews[] =  new SubmenuButtonView(
            Category::BADGE_SCAN_ICON,
            $badgeScanTitle,
            $this->router->generate(
                'event_sheet_user_badge_scan',
                [
                    'sheet' => $query->sheet->getId(),
                ]
            ),
            Route::isBadge($query->route),
            false,
            true
        );

        return $buttonViews;
    }
}
