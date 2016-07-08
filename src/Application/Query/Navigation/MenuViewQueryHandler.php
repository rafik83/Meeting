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
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\View\Navigation\MenuView;

class MenuViewQueryHandler
{
    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    /**
     * @var CategoryViewQueryHandler
     */
    private $categoryViewQueryHandler;

    /**
     * MenuViewQueryHandler constructor.
     *
     * @param SheetGuesser             $sheetGuesser
     * @param CategoryViewQueryHandler $categoryViewQueryHandler
     */
    public function __construct(SheetGuesser $sheetGuesser, CategoryViewQueryHandler $categoryViewQueryHandler)
    {
        $this->sheetGuesser             = $sheetGuesser;
        $this->categoryViewQueryHandler = $categoryViewQueryHandler;
    }

    /**
     * @param MenuViewQuery $menuViewQuery
     *
     * @return MenuView
     */
    public function handle(MenuViewQuery $menuViewQuery)
    {
        $sheet = $this->sheetGuesser->getUserSheet(
            $menuViewQuery->user,
            $menuViewQuery->event,
            $menuViewQuery->locale
        );

        $categoryView = [];

        foreach (Category::$categories as $category) {
            $categoryView[] = $this->categoryViewQueryHandler->handle(new CategoryViewQuery(
                $sheet, $menuViewQuery->user, $category, $menuViewQuery->locale
            ));
        }

        return new MenuView($categoryView);
    }
}
