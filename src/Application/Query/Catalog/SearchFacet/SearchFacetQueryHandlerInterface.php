<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\SearchFacet;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;

interface SearchFacetQueryHandlerInterface
{
    /**
     * @param AbstractSearchFacetViewQuery $abstractSearchFacetViewQuery
     *
     * @return SearchFacetsView
     */
    public function handle(AbstractSearchFacetViewQuery $abstractSearchFacetViewQuery): SearchFacetsView;
}
