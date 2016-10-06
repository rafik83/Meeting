<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\SearchFacet;

interface SearchFacetRepositoryInterface
{
    /**
     * @param SearchFacet $searchFacet
     */
    public function set(SearchFacet $searchFacet);

    /**
     * @param SearchFacet $searchFacet
     */
    public function add(SearchFacet $searchFacet);

}
