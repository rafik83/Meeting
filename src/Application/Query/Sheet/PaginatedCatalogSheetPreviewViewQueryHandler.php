<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewViewQueryHandler;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class PaginatedCatalogSheetPreviewViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * @var SheetPreviewViewQueryHandler
     */
    private $sheetPreviewViewQueryHandler;

    /**
     * @param SheetRepositoryInterface     $sheetRepository
     * @param SheetSearchAdapterInterface  $sheetSearchAdapter
     * @param SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler
    ) {
        $this->sheetRepository              = $sheetRepository;
        $this->sheetSearchAdapter           = $sheetSearchAdapter;
        $this->sheetPreviewViewQueryHandler = $sheetPreviewViewQueryHandler;
    }

    /**
     * @param PaginatedCatalogSheetPreviewViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedCatalogSheetPreviewViewQuery $query)
    {
        $paginatedResult = $this->sheetSearchAdapter->find(
            $query->event,
            array_merge([SheetSearchAdapterInterface::ES_FIELD_IN_CATALOG => true], $query->filters),
            $query->filters['orderBy'],
            $query->page,
            $query->limit,
            $query->locale,
            true
        );

        $paginatedResult->results = $this->sheetRepository->findSheets($paginatedResult->results);

        $paginatedResult->results = array_map(
            function (Sheet $sheet) use ($query) {
                return $this
                    ->sheetPreviewViewQueryHandler
                    ->handle(new SheetPreviewViewQuery($sheet, $query->locale, $query->viewer));
            },
            $paginatedResult->results
        );

        return $paginatedResult;
    }
}
