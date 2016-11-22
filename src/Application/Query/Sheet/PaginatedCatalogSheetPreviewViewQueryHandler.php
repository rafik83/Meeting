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
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

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
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param SheetRepositoryInterface     $sheetRepository
     * @param SheetSearchAdapterInterface  $sheetSearchAdapter
     * @param SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler
     * @param TemplateDataFactory          $templateDataFactory
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->sheetRepository              = $sheetRepository;
        $this->sheetSearchAdapter           = $sheetSearchAdapter;
        $this->sheetPreviewViewQueryHandler = $sheetPreviewViewQueryHandler;
        $this->templateDataFactory          = $templateDataFactory;
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
            true,
            $this->getNomenclatureItems($query->viewer, $query->locale)
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

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    private function getNomenclatureItems(Sheet $sheet, $locale)
    {
        $nomenclatureItems = [];
        $templateData      = $this->templateDataFactory->createFromSheet($sheet, $locale);

        foreach ($templateData->getNomenclatureObjects() as $nomenclatureObject) {
            $items = $nomenclatureObject->getData();
            if (isset($items['items'])) {
                $nomenclatureItems = array_merge($nomenclatureItems, $items['items']);
            }
        }

        return $nomenclatureItems;
    }
}
