<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewLogoutViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewLogoutViewQueryHandler;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

class PaginatedSheetLogoutViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;
    /**
     * @var SheetPreviewLogoutViewQueryHandler
     */
    private $sheetPreviewLogoutViewQueryHandler;

    /**
     * PaginatedSheetLogoutViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface        $sheetSearchAdapter
     * @param SheetPreviewLogoutViewQueryHandler $sheetPreviewLogoutViewQueryHandler
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewLogoutViewQueryHandler $sheetPreviewLogoutViewQueryHandler
    ) {
        $this->sheetSearchAdapter                 = $sheetSearchAdapter;
        $this->sheetPreviewLogoutViewQueryHandler = $sheetPreviewLogoutViewQueryHandler;
    }

    /**
     * @param PaginatedSheetLogoutViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetLogoutViewQuery $query)
    {
        $paginatedResult = $this->sheetSearchAdapter->find(
            $query->event,
            $query->filters,
            null,
            $query->page,
            $query->limit,
            $query->locale,
            true,
            []
        );

        $paginatedResult->results = array_map(
            $this->getSheetPreview($query),
            $paginatedResult->results
        );

        return $paginatedResult;
    }

    /**
     * @param PaginatedSheetLogoutViewQuery $query
     *
     * @return \Closure
     */
    private function getSheetPreview(PaginatedSheetLogoutViewQuery $query): \Closure
    {
        return function (Sheet $sheet) use ($query) {
            return $this->sheetPreviewLogoutViewQueryHandler->handle(
                new SheetPreviewLogoutViewQuery($sheet, $query->locale, $query->event)
            );
        };
    }
}
