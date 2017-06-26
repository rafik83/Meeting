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
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQueryHandler;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

class PaginatedSheetExternalViewQueryHandler
{
    const defaultFilters = [
        'validationState' => Sheet::STATE_VALIDATION_VALIDATED,
        'state'           => Sheet::STATE_ACCEPTED,
    ];

    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * @var SheetPreviewExternalViewQueryHandler
     */
    private $sheetPreviewLogoutViewQueryHandler;

    /**
     * PaginatedSheetExternalViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface          $sheetSearchAdapter
     * @param SheetPreviewExternalViewQueryHandler $sheetPreviewLogoutViewQueryHandler
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewExternalViewQueryHandler $sheetPreviewLogoutViewQueryHandler
    ) {
        $this->sheetSearchAdapter                 = $sheetSearchAdapter;
        $this->sheetPreviewLogoutViewQueryHandler = $sheetPreviewLogoutViewQueryHandler;
    }

    /**
     * @param PaginatedSheetExternalViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetExternalViewQuery $query)
    {
        $paginatedResult = $this->sheetSearchAdapter->find(
            $query->event,
            array_merge($query->filters, self::defaultFilters),
            Sheet\Constant::ORDER_BY_ALPHABETICAL,
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
     * @param PaginatedSheetExternalViewQuery $query
     *
     * @return \Closure
     */
    private function getSheetPreview(PaginatedSheetExternalViewQuery $query): \Closure
    {
        return function (Sheet $sheet) use ($query) {
            return $this->sheetPreviewLogoutViewQueryHandler->handle(
                new SheetPreviewExternalViewQuery($sheet, $query->locale, $query->event)
            );
        };
    }
}
