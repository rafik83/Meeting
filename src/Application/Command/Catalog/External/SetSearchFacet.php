<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;

class SetSearchFacet
{
    /** @var SearchFacet[] */
    public $searchFacets;

    /**
     * SetSearchFacet constructor.
     *
     * @param SearchFacet[] $searchFacets
     */
    public function __construct(array $searchFacets)
    {
        $this->searchFacets = $searchFacets;
    }
}
