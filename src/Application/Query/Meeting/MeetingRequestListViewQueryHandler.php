<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;

class MeetingRequestListViewQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $meetingRequestRepository;

    /** @var MeetingRequestViewQueryHandler */
    private $meetingRequestViewQueryHandler;

    /** @var ViewedSheetListViewQueryHandler */
    private $viewedSheetListViewQueryHandler;

    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var SheetViewedRepositoryInterface */
    private $sheetViewedRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(
        RequestRepositoryInterface $meetingRequestRepository,
        MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler,
        ViewedSheetListViewQueryHandler $viewedSheetListViewQueryHandler,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        ValidationRequiredChecker $validationRequiredChecker,
        SheetViewedRepositoryInterface $sheetViewedRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->meetingRequestViewQueryHandler = $meetingRequestViewQueryHandler;
        $this->viewedSheetListViewQueryHandler = $viewedSheetListViewQueryHandler;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->sheetViewedRepository = $sheetViewedRepository;
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(MeetingRequestListViewQuery $query): MeetingRequestListView
    {
        $meetingRequests = $this->getMeetingRequest(
            $query->sheet,
            $query->user,
            $query->event,
            $query->filters,
            $query->slotsToFilter
        );

        $sheets = [];
        foreach ($meetingRequests as $meetingRequest) {
            $sheets[] = $meetingRequest->getSheetMet($query->sheet);
        }

        $viewedSheetListView = $this->viewedSheetListViewQueryHandler->handle(
            new ViewedSheetListViewQuery($query->user, $sheets)
        );

        $meetingRequestListView = new MeetingRequestListView();
        $isMeetingPublished = $this->meetingPublishedAccessChecker->allowedToAccess($query->event);

        $isMeetingRequestUpdateLocked = $query->event->getConfiguration()->isMeetingRequestUpdateLocked();
        $isMeetingRequestClosed = !$this->meetingRequestAccessChecker->allowedToAccess($query->event);
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($query->event);

        $isPhoneValidationRequired = $this->isPhoneValidationRequiredForUser($query);

        foreach ($meetingRequests as $meetingRequest) {
            $meetingRequestView = $this->meetingRequestViewQueryHandler->handle(
                new MeetingRequestViewQuery(
                    $meetingRequest,
                    $query->sheet,
                    $query->user,
                    $query->locale,
                    $isMeetingPublished,
                    $isMeetingRequestUpdateLocked,
                    $isMeetingRequestClosed,
                    $isAnsweringMeetingRequestClosed,
                    isset($viewedSheetListView[$meetingRequest->getSheetMet($query->sheet)->getId()]),
                    $isPhoneValidationRequired,
                    $query->showCategory,
                    $this->isPriorityRequest($query, $meetingRequest)
                )
            );

            $meetingRequestListView->addRequestView($meetingRequestView);
        }

        if (!empty($query->filters['orderBy'])) {
            $order = $query->filters['orderBy'];
            $meetingRequestListView->sortBy($order);
        }

        return $meetingRequestListView;
    }

    private function getMeetingRequest(
        Sheet $sheet,
        User $user,
        Event $event,
        array $filters = [],
        array $slotsToFilter = []
    ): array {
        if (isset($filters['sheetVisit'])
            && in_array(
                $filters['sheetVisit'],
                [Constant::FILTER_SHEET_VISIT_SAW, Constant::FILTER_SHEET_VISIT_VIEWED_BY],
                true
            )
        ) {
            $sheets = [];
            if ($filters['sheetVisit'] === Constant::FILTER_SHEET_VISIT_SAW) {
                $sheets = $this->sheetViewedRepository->getSheetsSeenByUserAndEvent($user, $event);
            }

            if ($filters['sheetVisit'] === Constant::FILTER_SHEET_VISIT_VIEWED_BY) {
                $users = $this->sheetViewedRepository->getUsersWhoViewedSheet($sheet);
                $sheets = $this->sheetRepository->getSheetsByUsersAndEvent($users, $event);
            }

            return $this
                ->meetingRequestRepository
                ->getAllRequestBySheetAndSheets($sheet, $sheets, $filters, $slotsToFilter)
                ;
        }

        return $this
            ->meetingRequestRepository
            ->getAllRequestBySheet($sheet, $filters, $slotsToFilter)
            ;
    }

    private function isPhoneValidationRequiredForUser(MeetingRequestListViewQuery $query): bool
    {
        return $this->validationRequiredChecker->handle($query->sheet, $query->user);
    }

    private function isPriorityRequest(MeetingRequestListViewQuery $query, Request $meetingRequest): bool
    {
        return ($meetingRequest->isFromPriority() && $query->sheet === $meetingRequest->getFromSheet())
            || ($meetingRequest->isToPriority() && $query->sheet === $meetingRequest->getToSheet());
    }
}
