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
     * @var CatalogSheetPreviewViewQueryHandler
     */
    private $catalogSheetPreviewViewQueryHandler;

    /**
     * @param SheetRepositoryInterface            $sheetRepository
     * @param SheetSearchAdapterInterface         $sheetSearchAdapter
     * @param CatalogSheetPreviewViewQueryHandler $catalogSheetPreviewViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        CatalogSheetPreviewViewQueryHandler $catalogSheetPreviewViewQueryHandler
    ) {
        $this->sheetRepository                     = $sheetRepository;
        $this->sheetSearchAdapter                  = $sheetSearchAdapter;
        $this->catalogSheetPreviewViewQueryHandler = $catalogSheetPreviewViewQueryHandler;
    }

    /**
     * @param PaginatedCatalogSheetPreviewViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedCatalogSheetPreviewViewQuery $query)
    {
        $sheets = $this->sheetSearchAdapter->find(
            $query->event,
            array_merge(['inCatalog' => true], $query->filters),
            $query->filters['orderBy'],
            $query->page,
            $query->limit,
            $query->locale
        );

        $sheets->results = $this->sheetRepository->findSheets($sheets->results);

        $sheets->results = array_map(
            function (Sheet $sheet) use ($query) {
                return $this
                    ->catalogSheetPreviewViewQueryHandler
                    ->handle(new CatalogSheetPreviewViewQuery($sheet, $query->locale, $query->viewer));
            },
            $sheets->results
        );

        return $sheets;
    }
}
