<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

use Elastica\Result;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;

class SheetListViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /**
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param SheetListViewQuery $query
     *
     * @return SheetListView[]
     */
    public function handle(SheetListViewQuery $query)
    {
        $filters = array_merge($query->filters, $this->getDefaultFilters());
        $results = $this->sheetSearchAdapter->findUnpaginated($query->event, $filters, $query->locale, 'sheetName');

        return array_map(function (Result $result) {
            return new SheetListView($result->id, $result->sheetName);
        }, $results);
    }

    /**
     * @return array
     */
    private function getDefaultFilters()
    {
        return [
            'enabled' => true,
        ];
    }
}
