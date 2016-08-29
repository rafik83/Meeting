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
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\CatalogSheetPreviewView;
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
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var Preview
     */
    private $preview;

    /**
     * @param SheetRepositoryInterface    $sheetRepository
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param Preview                     $preview
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetInfoGuesser $sheetInfoGuesser,
        Preview $preview
    ) {
        $this->sheetRepository    = $sheetRepository;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->sheetInfoGuesser   = $sheetInfoGuesser;
        $this->preview            = $preview;
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
            $query->orderBy,
            $query->page,
            $query->limit,
            $query->locale
        );

        $sheets->results = $this->sheetRepository->findSheets($sheets->results);

        $sheets->results = array_map(
            function (Sheet $sheet) use ($query) {
                return new CatalogSheetPreviewView(
                    $sheet->getId(),
                    $this->sheetInfoGuesser->guessSheetName($sheet, $query->locale),
                    $sheet->getType()->getTitle($query->locale),
                    $this->preview->getPreview($sheet, $query->locale)
                );
            },
            $sheets->results
        );

        return $sheets;
    }
}
