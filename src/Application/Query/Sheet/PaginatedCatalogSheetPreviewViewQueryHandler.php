<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Nomenclature\NomenclatureItemsGetter;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;

class PaginatedCatalogSheetPreviewViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ViewedSheetListViewQueryHandler */
    private $viewedSheetListViewQueryHandler;

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var SheetPreviewViewQueryHandler */
    private $sheetPreviewViewQueryHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var NomenclatureItemsGetter */
    private $nomenclatureItemsGetter;

    /**
     * @param SheetRepositoryInterface             $sheetRepository
     * @param SheetSearchAdapterInterface          $sheetSearchAdapter
     * @param SheetPreviewViewQueryHandler         $sheetPreviewViewQueryHandler
     * @param ViewedSheetListViewQueryHandler      $viewedSheetListViewQueryHandler
     * @param TemplateDataFactory                  $templateDataFactory
     * @param MeetingRequestAccessChecker          $meetingRequestAccessChecker
     * @param AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
     * @param ValidationRequiredChecker            $validationRequiredChecker
     * @param NomenclatureItemsGetter              $nomenclatureItemsGetter
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler,
        ViewedSheetListViewQueryHandler $viewedSheetListViewQueryHandler,
        TemplateDataFactory $templateDataFactory,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        ValidationRequiredChecker $validationRequiredChecker,
        NomenclatureItemsGetter $nomenclatureItemsGetter
    ) {
        $this->sheetRepository                      = $sheetRepository;
        $this->sheetSearchAdapter                   = $sheetSearchAdapter;
        $this->sheetPreviewViewQueryHandler         = $sheetPreviewViewQueryHandler;
        $this->viewedSheetListViewQueryHandler      = $viewedSheetListViewQueryHandler;
        $this->templateDataFactory                  = $templateDataFactory;
        $this->meetingRequestAccessChecker          = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->validationRequiredChecker            = $validationRequiredChecker;
        $this->nomenclatureItemsGetter = $nomenclatureItemsGetter;
    }

    /**
     * @param PaginatedCatalogSheetPreviewViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedCatalogSheetPreviewViewQuery $query)
    {
        $paginatedResult = $this->sheetSearchAdapter->paginate(
            $query->event,
            $query->filters,
            $query->filters['orderBy'],
            $query->page,
            $query->limit,
            $query->locale,
            true,
            $this->nomenclatureItemsGetter->getNomenclatureItems(
                $query->viewer,
                $query->locale
            ),
            $query->availableSlotIds,
            $query->sheetsToExclude
        );

        $paginatedResult->results = $this->sheetRepository->findSheets($paginatedResult->results);
        $seenSheetIndexed         = $this->viewedSheetListViewQueryHandler->handle(
            new ViewedSheetListViewQuery($query->user, $paginatedResult->results)
        );

        $isMeetingRequestClosed          = !$this->meetingRequestAccessChecker->allowedToAccess($query->event);
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($query->event);

        $isPhoneValidationRequired = $this->isPhoneValidationRequiredForUser($query);

        $paginatedResult->results = array_map(
            function (Sheet $sheet) use (
                $query,
                $isMeetingRequestClosed,
                $isAnsweringMeetingRequestClosed,
                $seenSheetIndexed,
                $isPhoneValidationRequired
            ) {
                return $this
                    ->sheetPreviewViewQueryHandler
                    ->handle(
                        new SheetPreviewViewQuery(
                            $query->event,
                            $sheet,
                            $query->locale,
                            $query->viewer,
                            $query->user,
                            $isMeetingRequestClosed,
                            $isAnsweringMeetingRequestClosed,
                            isset($seenSheetIndexed[$sheet->getId()]),
                            $isPhoneValidationRequired
                        )
                    );
            },
            $paginatedResult->results
        );

        return $paginatedResult;
    }

    /**
     * @param PaginatedCatalogSheetPreviewViewQuery $query
     *
     * @return bool
     */
    private function isPhoneValidationRequiredForUser(PaginatedCatalogSheetPreviewViewQuery $query): bool
    {
        return $this->validationRequiredChecker->handle($query->viewer, $query->user);
    }
}
