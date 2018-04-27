<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\MenuView;

class MenuViewQueryHandler
{
    /**
     * @var CategoryViewQueryHandler
     */
    private $categoryViewQueryHandler;

    /**
     * @param CategoryViewQueryHandler $categoryViewQueryHandler
     */
    public function __construct(CategoryViewQueryHandler $categoryViewQueryHandler)
    {
        $this->categoryViewQueryHandler = $categoryViewQueryHandler;
    }

    /**
     * @param MenuViewQuery $menuViewQuery
     *
     * @return MenuView
     */
    public function handle(MenuViewQuery $menuViewQuery)
    {
        if (null === $menuViewQuery->sheet || null === $menuViewQuery->user) {
            return new MenuView([]);
        }

        $categoryViews = [];

        foreach (Category::$categories as $category) {
            $categoryView = $this->categoryViewQueryHandler->handle(
                new CategoryViewQuery(
                    $menuViewQuery->sheet, $menuViewQuery->user, $category, $menuViewQuery->locale
                )
            );

            if (null !== $categoryView) {
                $categoryViews[] = $categoryView;
            }
        }

        return new MenuView($categoryViews);
    }
}
