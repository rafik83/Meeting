<?php

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
use Proximum\Vimeet\Domain\Model\User;
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
     * @throws NoResultException
     *
     * @return SheetListView
     */
    public function handle(SheetListViewQuery $query)
    {
        $multipleSheets = $query->sheets;

        if (null !== $query->filterRequestView->sheetConcerned) {
            $sheetConcerned = $query->filterRequestView->sheetConcerned;
            $multipleSheets = [
                $sheetConcerned->getId() => $sheetConcerned,
            ];
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

        $allSheetMet = null;

        if (null === $query->filterRequestView->otherSheet) {
            // sheets met by the group sheets
            $sheets = $this->sheetRepository->getSheetsMetBySheetsPaginated(
                $event,
                $multipleSheets,
                $query->page,
                $query->limit,
                $query->filterRequestView->state,
                $query->filterRequestView->type,
                $query->filterRequestView->user
            );

            $allSheetMet = $this->sheetRepository->getSheetsMetBySheets(
                $event,
                $multipleSheets,
                $query->filterRequestView->state,
                $query->filterRequestView->type,
                $query->filterRequestView->user
            );
        } else {
            $otherSheet = $query->filterRequestView->otherSheet;

            $sheets = new PaginatedResult(
                [$otherSheet->getId() => $otherSheet],
                $query->page,
                $query->limit,
                1,
                []
            );

            $allSheetMet = [$otherSheet];
        }

        if (0 === $sheets->total || 0 === $sheets->count()) {
            if (1 === $query->page) {
                return new SheetListView(
                    [],
                    $query->page,
                    0,
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
            $query->user,
            $multipleSheets,
            $sheets->results,
            $query->locale,
            $query->filterRequestView->state,
            $query->filterRequestView->type,
            $query->filterRequestView->user
        );

        // Total of requests
        $numberOfRequests = $this->requestRepository->countRequestOfSheetsWithSheets(
            $event,
            $multipleSheets,
            $allSheetMet,
            $query->filterRequestView->state,
            $query->filterRequestView->type,
            $query->filterRequestView->user
        );

        return new SheetListView(
            array_filter($sheetViews, function (SheetView $sheetView) {
                return $sheetView->numberOfRequest() > 0;
            }),
            $query->page, // current page
            $sheets->pages, // total page
            $numberOfRequests, // total of requests
            $isMeetingRequestUpdateLocked,
            $isMeetingRequestClosed,
            $isAnsweringMeetingRequestClosed
        );
    }

    /**
     * Creates the SheetViews and returns them with there requests
     *
     * @param Event            $event
     * @param User             $loggedUser
     * @param Sheet[]          $multipleSheets
     * @param Sheet[]          $sheetsMet
     * @param string           $locale
     * @param string|null      $state
     * @param string|null      $type
     * @param User|string|null $user
     *
     * @return SheetView[]
     */
    private function getSheetViewsWithRequest(
        Event $event,
        User $loggedUser,
        array &$multipleSheets,
        array &$sheetsMet,
        string $locale,
        ?string $state,
        ?string $type,
        $user = null
    ) {
        /** @var SheetView[] $sheetViews */
        $sheetViews = [];

        foreach ($sheetsMet as $sheetMet) {
            $sheetViews[$sheetMet->getId()] = $this->sheetViewQueryHandler->handle(
                new SheetViewQuery($sheetMet, $loggedUser, $locale)
            );
        }

        $requests = $this->requestRepository->getRequestsOfSheetsWithSheets(
            $event,
            $multipleSheets,
            $sheetsMet,
            $state,
            $type,
            $user
        );

        $this->addRequestToSheetViews($requests, $multipleSheets, $sheetViews, $locale);

        return $sheetViews;
    }

    /**
     * Add RequestView to SheetView
     *
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
