<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class PaginatedSheetListViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * @var SheetListViewQueryHandler
     */
    private $sheetListViewQueryHandler;

    /**
     * @param PaginatedSheetListViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetListViewQuery $query)
    {
        $sheets = $this->sheetSearchAdapter->find($query->event, $query->filters, $query->page, $query->limit, $query->locale);

        $sheets->results = array_map(function (Sheet $sheet) use ($query) {
            return $this->sheetListViewQueryHandler->handle(new SheetListViewQuery($sheet, $query->locale, $query->admin));
        }, $sheets->results);

        return $sheets;
    }
}
