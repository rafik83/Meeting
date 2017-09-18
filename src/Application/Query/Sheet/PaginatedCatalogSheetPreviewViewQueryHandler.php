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
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Application\Security\ValidateMobileProcessAccessChecker;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

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

    /** @var ValidateMobileProcessAccessChecker */
    private $validateMobileProcessAccessChecker;

    /** @var DDayGuesser */
    private $dDayGuesser;

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var bool */
    private $isMobileValidationRequired = false;

    /**
     * @param SheetRepositoryInterface             $sheetRepository
     * @param SheetSearchAdapterInterface          $sheetSearchAdapter
     * @param SheetPreviewViewQueryHandler         $sheetPreviewViewQueryHandler
     * @param ViewedSheetListViewQueryHandler      $viewedSheetListViewQueryHandler
     * @param TemplateDataFactory                  $templateDataFactory
     * @param MeetingRequestAccessChecker          $meetingRequestAccessChecker
     * @param AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
     * @param ValidateMobileProcessAccessChecker   $validateMobileProcessAccessChecker
     * @param DDayGuesser                          $dDayGuesser
     * @param UserEventPhoneRepositoryInterface    $userEventPhoneRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewViewQueryHandler $sheetPreviewViewQueryHandler,
        ViewedSheetListViewQueryHandler $viewedSheetListViewQueryHandler,
        TemplateDataFactory $templateDataFactory,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker,
        DDayGuesser $dDayGuesser,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository
    ) {
        $this->sheetRepository                      = $sheetRepository;
        $this->sheetSearchAdapter                   = $sheetSearchAdapter;
        $this->sheetPreviewViewQueryHandler         = $sheetPreviewViewQueryHandler;
        $this->viewedSheetListViewQueryHandler      = $viewedSheetListViewQueryHandler;
        $this->templateDataFactory                  = $templateDataFactory;
        $this->meetingRequestAccessChecker          = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->validateMobileProcessAccessChecker   = $validateMobileProcessAccessChecker;
        $this->dDayGuesser                          = $dDayGuesser;
        $this->userEventPhoneRepository             = $userEventPhoneRepository;
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
            $query->filters,
            $query->filters['orderBy'],
            $query->page,
            $query->limit,
            $query->locale,
            true,
            $this->getNomenclatureItems($query->viewer, $query->locale)
        );

        $paginatedResult->results = $this->sheetRepository->findSheets($paginatedResult->results);
        $seenSheetIndexed         = $this->viewedSheetListViewQueryHandler->handle(
            new ViewedSheetListViewQuery($query->user, $paginatedResult->results)
        );

        $isMeetingRequestClosed          = !$this->meetingRequestAccessChecker->allowedToAccess($query->event);
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($query->event);

        $this->isMobileValidationRequired = $this->isMobileValidationRequiredForUser($query);

        $paginatedResult->results = array_map(
            function (Sheet $sheet) use (
                $query,
                $isMeetingRequestClosed,
                $isAnsweringMeetingRequestClosed,
                $seenSheetIndexed
            ) {
                return $this
                    ->sheetPreviewViewQueryHandler
                    ->handle(
                        new SheetPreviewViewQuery(
                            $query->event,
                            $sheet,
                            $query->locale,
                            $query->viewer,
                            $isMeetingRequestClosed,
                            $isAnsweringMeetingRequestClosed,
                            isset($seenSheetIndexed[$sheet->getId()]),
                            $this->isMobileValidationRequired
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

    /**
     * @param PaginatedCatalogSheetPreviewViewQuery $query
     *
     * @return bool
     */
    private function isMobileValidationRequiredForUser(PaginatedCatalogSheetPreviewViewQuery $query): bool
    {
        if ($this->dDayGuesser->isItDDay($query->event)) {
            $allowedToValidateMobile = $this->validateMobileProcessAccessChecker->allowToAccess(
                $query->event,
                $query->user,
                $query->locale
            );

            if ($allowedToValidateMobile === true) {
                $userEventPhone = $this->userEventPhoneRepository->findValidated($query->user, $query->event);

                return $userEventPhone === null;
            }
        }

        return false;
    }
}
