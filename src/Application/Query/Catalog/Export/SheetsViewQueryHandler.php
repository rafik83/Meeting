<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Elastica\Result;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Nomenclature\NomenclatureItemsGetter;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Sheet\SheetIdView;

class SheetsViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var NomenclatureItemsGetter */
    private $nomenclatureItemsGetter;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     * @param NomenclatureItemsGetter     $nomenclatureItemsGetter
     * @param SheetRepositoryInterface    $sheetRepository
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        NomenclatureItemsGetter $nomenclatureItemsGetter,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->nomenclatureItemsGetter = $nomenclatureItemsGetter;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param SheetsViewQuery $query
     */
    public function handle(SheetsViewQuery $query)
    {
        $results = $this->sheetSearchAdapter->find(
            $query->event,
            $query->filters,
            $query->filters['orderBy'],
            $query->locale,
            $this->nomenclatureItemsGetter->getNomenclatureItems(
                $query->sheet,
                $query->locale
            ),
            $query->availableSlotIds,
            $query->sheetsToExclude
        );

        $sheetIds = array_map(function (Result $result) {
            return new SheetIdView($result->getId());
        }, $results);

        $sheets = $this->sheetRepository->findSheets($sheetIds);
    }
}
