<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;

class CatalogVisibilityView
{
    /** @var CatalogVisibility */
    public $catalogVisibility;

    /**
     * CatalogVisibilityView constructor.
     *
     * @param CatalogVisibility $catalogVisibility
     */
    public function __construct(CatalogVisibility $catalogVisibility)
    {
        $this->catalogVisibility = $catalogVisibility;
    }
}
