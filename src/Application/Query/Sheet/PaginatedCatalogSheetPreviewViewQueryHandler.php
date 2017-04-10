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
use Proximum\Vimeet\Domain\KeyDates\Checker\CloseAnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\CloseMeetingRequestAccessChecker;
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

    /** @var CloseMeetingRequestAccessChecker */
    private $closeMeetingRequestAccessChecker;

    /** @var CloseAnsweringMeetingRequestAccessChecker */
    private $closeAnsweringMeetingRequestAccessChecker;

    /**
     * @param SheetRepositoryInterface                  $sheetRepository
     * @param SheetSearchAdapterInterface               $sheetSearchAdapter
     * @param SheetPreviewViewQueryHandler              $sheetPreviewViewQueryHandler
     * @param TemplateDataFactory                       $templateDataFactory
     * @param CloseMeetingRequestAccessChecker          $closeMeetingRequestAccessChecker
     * @param CloseAnsweringMeetingRequestAccessChecker $closeAnsweringMeetingRequestAccessChecker
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler,
        TemplateDataFactory $templateDataFactory,
        CloseMeetingRequestAccessChecker $closeMeetingRequestAccessChecker,
        CloseAnsweringMeetingRequestAccessChecker $closeAnsweringMeetingRequestAccessChecker
    ) {
        $this->sheetRepository                           = $sheetRepository;
        $this->sheetSearchAdapter                        = $sheetSearchAdapter;
        $this->sheetPreviewViewQueryHandler              = $sheetPreviewViewQueryHandler;
        $this->templateDataFactory                       = $templateDataFactory;
        $this->closeMeetingRequestAccessChecker          = $closeMeetingRequestAccessChecker;
        $this->closeAnsweringMeetingRequestAccessChecker = $closeAnsweringMeetingRequestAccessChecker;
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

        $isMeetingRequestClosed          = !$this->closeMeetingRequestAccessChecker->allowedToAccess($query->event);
        $isAnsweringMeetingRequestClosed = !$this->closeAnsweringMeetingRequestAccessChecker->allowedToAccess($query->event);

        $paginatedResult->results = array_map(
            function (Sheet $sheet) use ($query, $isMeetingRequestClosed, $isAnsweringMeetingRequestClosed) {
                return $this
                    ->sheetPreviewViewQueryHandler
                    ->handle(
                        new SheetPreviewViewQuery(
                            $query->event,
                            $sheet,
                            $query->locale,
                            $query->viewer,
                            $isMeetingRequestClosed,
                            $isAnsweringMeetingRequestClosed
                        )
                    );
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
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

        $nomenclatureItems   = [];
        $nomenclatureObjects = $templateData->getNomenclatureObjects();

        foreach ($nomenclatureObjects as $nomenclatureObject) {
            $items = $nomenclatureObject->getData();
            if (isset($items['items'])) {
                if (!isset($nomenclatureItems[$nomenclatureObject->getObjective()])) {
                    $nomenclatureItems[$nomenclatureObject->getObjective()] = [];
                }

                $nomenclatureItems[$nomenclatureObject->getObjective()] = array_merge(
                    $nomenclatureItems[$nomenclatureObject->getObjective()],
                    $items['items']
                );
            }
        }

        return $nomenclatureItems;
    }
}
