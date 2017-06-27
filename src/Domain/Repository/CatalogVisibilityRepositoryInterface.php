<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;

interface CatalogVisibilityRepositoryInterface
{
    /**
     * @param CatalogVisibility $catalogVisibility
     */
    public function add(CatalogVisibility $catalogVisibility);
}
