<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;

class MeetingRequestListViewQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $meetingRequestRepository;

    /** @var MeetingRequestViewQueryHandler */
    private $meetingRequestViewQueryHandler;

    /** @var ViewedSheetListViewQueryHandler */
    private $viewedSheetListViewQueryHandler;

    /** @var MeetingPublishedAccessChecker*/
    private $meetingPublishedAccessChecker;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /**
     * MeetingRequestListViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface           $meetingRequestRepository
     * @param MeetingRequestViewQueryHandler       $meetingRequestViewQueryHandler
     * @param ViewedSheetListViewQueryHandler      $viewedSheetListViewQueryHandler
     * @param MeetingPublishedAccessChecker        $meetingPublishedAccessChecker
     * @param MeetingRequestAccessChecker          $meetingRequestAccessChecker
     * @param AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
     * @param ValidationRequiredChecker            $validationRequiredChecker
     */
    public function __construct(
        RequestRepositoryInterface $meetingRequestRepository,
        MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler,
        ViewedSheetListViewQueryHandler $viewedSheetListViewQueryHandler,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        ValidationRequiredChecker $validationRequiredChecker
    ) {
        $this->meetingRequestRepository             = $meetingRequestRepository;
        $this->meetingRequestViewQueryHandler       = $meetingRequestViewQueryHandler;
        $this->viewedSheetListViewQueryHandler      = $viewedSheetListViewQueryHandler;
        $this->meetingPublishedAccessChecker        = $meetingPublishedAccessChecker;
        $this->meetingRequestAccessChecker          = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->validationRequiredChecker            = $validationRequiredChecker;
    }

    /**
     * @param MeetingRequestListViewQuery $query
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $query)
    {
        $meetingRequests = $this->meetingRequestRepository
            ->getAllRequestBySheet($query->sheet, $query->filters, $query->slotsToFilter);

        $sheets = [];
        foreach ($meetingRequests as $meetingRequest) {
            $sheets[] = $meetingRequest->getSheetMet($query->sheet);
        }

        $viewedSheetListView = $this->viewedSheetListViewQueryHandler->handle(
            new ViewedSheetListViewQuery($query->user, $sheets)
        );

        $meetingRequestListView = new MeetingRequestListView();
        $isMeetingPublished     = $this->meetingPublishedAccessChecker->allowedToAccess($query->event);

        $isMeetingRequestUpdateLocked    = $query->event->getConfiguration()->isMeetingRequestUpdateLocked();
        $isMeetingRequestClosed          = !$this->meetingRequestAccessChecker->allowedToAccess($query->event);
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
                    $isPhoneValidationRequired
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

    /**
     * @param MeetingRequestListViewQuery $query
     *
     * @return bool
     */
    private function isPhoneValidationRequiredForUser(MeetingRequestListViewQuery $query): bool
    {
        return $this->validationRequiredChecker->handle($query->sheet, $query->user);
    }
}
