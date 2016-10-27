<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Application\View\Navigation\SubmenuView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class SubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    /**
     * SubmenuViewQueryHandler constructor.
     *
     * @param SheetGuesser               $sheetGuesser
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(
        SheetGuesser $sheetGuesser,
        NavigationBuilderInterface $navigationBuilder
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->sheetGuesser      = $sheetGuesser;
    }

    /**
     * @param SubmenuViewQuery $query
     *
     * @return SubmenuView
     */
    public function handle(SubmenuViewQuery $query)
    {
        $sheet = $this->sheetGuesser->getUserSheet(
            $query->user,
            $query->event,
            $query->locale
        );

        $buttonViews = [];

        if (Route::isCatalog($query->route) === true || Route::isMeetingRequest($query->route) === true) {
            $buttonViews[] = new SubmenuButtonView(
                Category::CATALOG_ICON,
                'navigation.category.catalog',
                $this->navigationBuilder->getRoute('event_catalog_index'),
                Route::isCatalog($query->route)
            );

            $buttonViews[] = new SubmenuButtonView(
                Category::MEETING_ICON,
                'navigation.category.meeting',
                $this->navigationBuilder->getRoute('event_meeting_list_request', [
                    'sheet' => $sheet->getId(),
                ]),
                Route::isMeetingRequest($query->route)
            );
        } else {
            $buttonViews[] = new SubmenuButtonView(
                Category::SHEET_ICON,
                'sheet.title',
                $this->navigationBuilder->getRoute('event_sheet'),
                !Route::isPackage($query->route)
            );

            if ($sheet->getPackage()->isPassable() === true) {
                $buttonViews[] = new SubmenuButtonView(
                    Category::PACKAGE_ICON,
                    'package.title',
                    $this->navigationBuilder->getRoute('event_package'),
                    Route::isPackage($query->route)
                );
            }
        }

        return new SubmenuView($buttonViews);
    }
}
