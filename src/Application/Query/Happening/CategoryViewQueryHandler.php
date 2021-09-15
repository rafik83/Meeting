<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;

class CategoryViewQueryHandler
{
    public function handle(CategoryViewQuery $query): HappeningCategoryView
    {
        return new HappeningCategoryView(
            $query->happening->getCategory()->getTitle($query->locale),
            $query->happening->getCategory()->getPicto(),
            $query->happening->getCategory()->getLeftColor(),
            $query->happening->getCategory()->getRightColor()
        );
    }
}
