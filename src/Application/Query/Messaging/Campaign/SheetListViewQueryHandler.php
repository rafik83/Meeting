<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

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

        return $this->sheetSearchAdapter->getSheetListView($query->event, $filters, $query->locale);
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
