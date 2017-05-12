<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\Exception\MultipleSheets\Request\NoResultException;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetListView;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var SheetViewQueryHandler */
    private $sheetViewQueryHandler;

    /** @var RequestViewQueryHandler */
    private $requestViewQueryHandler;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /**
     * @param SheetRepositoryInterface             $sheetRepository
     * @param RequestRepositoryInterface           $requestRepository
     * @param SheetViewQueryHandler                $sheetViewQueryHandler
     * @param RequestViewQueryHandler              $requestViewQueryHandler
     * @param MeetingRequestAccessChecker          $meetingRequestAccessChecker
     * @param AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        SheetViewQueryHandler $sheetViewQueryHandler,
        RequestViewQueryHandler $requestViewQueryHandler,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
    ) {
        $this->sheetRepository  = $sheetRepository;
        $this->requestRepository = $requestRepository;
        $this->sheetViewQueryHandler = $sheetViewQueryHandler;
        $this->requestViewQueryHandler = $requestViewQueryHandler;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
    }

    /**
     * @param SheetListViewQuery $query
     *
     * @return SheetListView
     *
     * @throws NoResultException
     */
    public function handle(SheetListViewQuery $query)
    {
        $multipleSheets = $query->sheets;

        if ($query->filterRequestView->sheetConcerned !== null) {
            $multipleSheets = [$query->filterRequestView->sheetConcerned];
        }

        $firstSheet = reset($query->sheets);

        if (false === $firstSheet) {
            throw new NoResultException('At least one sheet must be provided in SheetListViewQuery::sheets');
        }

        /** @var Event $event */
        $event = $firstSheet->getEvent();

        $isMeetingRequestUpdateLocked = $event->getConfiguration()->isMeetingRequestUpdateLocked();
        $isMeetingRequestClosed = !$this->meetingRequestAccessChecker->allowedToAccess($event);
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($event);

        if ($query->filterRequestView->otherSheet === null) {
            // sheets met by the group sheets
            $sheets = $this->sheetRepository->getSheetsMetBySheetsPaginated(
                $event,
                $multipleSheets,
                $query->page,
                $query->limit,
                $query->filterRequestView->state,
                $query->filterRequestView->type
            );
        } else {
            $sheets = new PaginatedResult([$query->filterRequestView->otherSheet], $query->page, $query->limit, 1, []);
        }

        if ($sheets->total === 0 || $sheets->count() === 0) {
            if ($query->page === 1) {
                return new SheetListView(
                    [],
                    $query->page,
                    0,
                    $isMeetingRequestUpdateLocked,
                    $isMeetingRequestClosed,
                    $isAnsweringMeetingRequestClosed
                );
            }

            throw new NoResultException();
        }

        $sheetViews = $this->getSheetViewsWithRequest(
            $event,
            $multipleSheets,
            $sheets->results,
            $query->locale,
            $query->filterRequestView->state,
            $query->filterRequestView->type
        );

        return new SheetListView(
            array_filter($sheetViews, function (SheetView $sheetView) {
                return $sheetView->numberOfRequest() > 0;
            }),
            $query->page, // current page
            $sheets->pages, // total page
            $isMeetingRequestUpdateLocked,
            $isMeetingRequestClosed,
            $isAnsweringMeetingRequestClosed
        );
    }

    /**
     * Creates the SheetViews and returns them with there requests
     *
     * @param Event       $event
     * @param Sheet[]     $multipleSheets
     * @param Sheet[]     $sheetsMet
     * @param string      $locale
     * @param string|null $state
     * @param string|null $type
     *
     * @return SheetView[]
     */
    private function getSheetViewsWithRequest(
        Event $event,
        array &$multipleSheets,
        array &$sheetsMet,
        $locale,
        $state,
        $type
    ) {
        /** @var SheetView[] $sheetViews */
        $sheetViews = [];

        foreach ($sheetsMet as $sheetMet) {
            $sheetViews[$sheetMet->getId()] = $this->sheetViewQueryHandler->handle(
                new SheetViewQuery($sheetMet, $locale)
            );
        }

        $requests = $this->requestRepository->getRequestsOfSheetsWithSheets(
            $event,
            $multipleSheets,
            $sheetsMet,
            $state,
            $type
        );

        $this->addRequestToSheetViews($requests, $multipleSheets, $sheetViews, $locale);

        return $sheetViews;
    }

    /**
     * Add RequestView to SheetView
     * @param Request[]   $requests
     * @param Sheet[]     $multipleSheets
     * @param SheetView[] $sheetViews
     * @param string      $locale
     */
    private function addRequestToSheetViews(array &$requests, array &$multipleSheets, array &$sheetViews, $locale)
    {
        foreach ($requests as $request) {
            $sheetMet = $this->getSheetMet($request, $multipleSheets);

            $requestView = $this->requestViewQueryHandler->handle(
                new RequestViewQuery($sheetMet, $request, $locale)
            );

            if (isset($sheetViews[$sheetMet->getId()])) {
                $sheetViews[$sheetMet->getId()]->addRequest($requestView);
            }
        }
    }

    /**
     * @param Request $request
     * @param Sheet[] $multipleSheets
     *
     * @return Sheet
     */
    private function getSheetMet(Request $request, array &$multipleSheets)
    {
        // If the from sheet is not in the group sheet, then the sheet met is the from sheet
        if (!isset($multipleSheets[$request->getFromSheet()->getId()])) {
            return $request->getFromSheet();
        }

        // If the to sheet is not in the group sheet, then the sheet met is the to sheet
        if (!isset($multipleSheets[$request->getToSheet()->getId()])) {
            return $request->getToSheet();
        }

        // Otherwise, it means that it is a sheet from the group that meet another sheet from the group
        return $request->getFromSheet();
    }
}
