<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;

class CategoryViewQueryHandler
{
    /**
     * @param CategoryViewQuery $query
     *
     * @return HappeningCategoryView
     */
    public function handle(CategoryViewQuery $query)
    {
        $happeningCategoryView = new HappeningCategoryView(
            $query->happening->getCategory()->getTitle($query->locale),
            $query->happening->getCategory()->getPicto(),
            $query->happening->getCategory()->getLeftColor(),
            $query->happening->getCategory()->getRightColor()
        );

        return $happeningCategoryView;
    }
}
