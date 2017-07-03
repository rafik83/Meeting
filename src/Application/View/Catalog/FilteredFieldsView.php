<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;

class FilteredFieldsView
{
    /** @var TypeView[] */
    public $typeViews;

    /** @var OrganizationCategoryView[] */
    public $organizationCategoryViews;

    /** @var PositionView[] */
    public $positionViews;

    /**
     * @param TypeView[] $typeViews
     * @param OrganizationCategoryView[] $organizationCategoryViews
     * @param PositionView[] $positionViews
     */
    public function __construct(array $typeViews, array $organizationCategoryViews, array $positionViews)
    {
        $this->typeViews = $typeViews;
        $this->organizationCategoryViews = $organizationCategoryViews;
        $this->positionViews = $positionViews;
    }
}
