<?php

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
