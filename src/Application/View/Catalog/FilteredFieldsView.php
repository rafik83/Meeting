<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;

class FilteredFieldsView
{
    /** @var CatalogFilterViewsResult */
    public $catalogFilterViewsResult;

    /**
     * @param CatalogFilterViewsResult $catalogFilterViewsResult
     */
    public function __construct(
        CatalogFilterViewsResult $catalogFilterViewsResult
    ) {
        $this->catalogFilterViewsResult = $catalogFilterViewsResult;
    }
}
