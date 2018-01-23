<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class SheetSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;
    
    /**
     * SheetSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(NavigationBuilderInterface $navigationBuilder)
    {
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param SheetSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(SheetSubmenuViewQuery $query)
    {
        $buttonViews = [];

        $buttonViews[] = new SubmenuButtonView(
            Category::SHEET_ICON,
            'sheet.title',
            $this->navigationBuilder->getRoute('event_sheet_default', ['sheet' => $query->sheet->getId()]),
            Route::isSheet($query->route),
            false,
            true
        );

        return $buttonViews;
    }
}
