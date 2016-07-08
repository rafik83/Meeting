<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;

class CatalogViewQueryHandler
{
    public function handle(CatalogViewQuery $catalogViewQuery)
    {
        return new CategoryView(
            Category::BILLING,
            '',
            []
        );
    }
}
