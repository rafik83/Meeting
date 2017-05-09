<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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

class MeetingRequestListViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $meetingRequestRepository;

    /**
     * @var MeetingRequestViewQueryHandler
     */
    private $meetingRequestViewQueryHandler;

    /**
     * @var ViewedSheetListViewQueryHandler
     */
    private $viewedSheetListViewQueryHandler;

    /**
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    /**
     * MeetingRequestListViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface           $meetingRequestRepository
     * @param MeetingRequestViewQueryHandler       $meetingRequestViewQueryHandler
     * @param ViewedSheetListViewQueryHandler      $viewedSheetListViewQueryHandler
     * @param MeetingPublishedAccessChecker        $meetingPublishedAccessChecker
     * @param MeetingRequestAccessChecker          $meetingRequestAccessChecker
     * @param AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
     */
    public function __construct(
        RequestRepositoryInterface $meetingRequestRepository,
        MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler,
        ViewedSheetListViewQueryHandler $viewedSheetListViewQueryHandler,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker
    ) {
        $this->meetingRequestRepository             = $meetingRequestRepository;
        $this->meetingRequestViewQueryHandler       = $meetingRequestViewQueryHandler;
        $this->viewedSheetListViewQueryHandler      = $viewedSheetListViewQueryHandler;
        $this->meetingPublishedAccessChecker        = $meetingPublishedAccessChecker;
        $this->meetingRequestAccessChecker          = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
    }

    /**
     * @param MeetingRequestListViewQuery $query
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $query)
    {
        $meetingRequests = $this->meetingRequestRepository
            ->getAllRequestBySheet($query->sheet, $query->filters);

        $sheetsIds = [];
        foreach ($meetingRequests as $meetingRequest) {
            $sheetsIds[] = $meetingRequest->getToSheet()->getId();
        }

        $viewedSheetListView = $this->viewedSheetListViewQueryHandler->handle(
            new ViewedSheetListViewQuery($query->user, $sheetsIds)
        );

        $meetingRequestListView = new MeetingRequestListView();
        $isMeetingPublished     = $this->meetingPublishedAccessChecker->allowedToAccess($query->event);

        $isMeetingRequestUpdateLocked    = $query->event->getConfiguration()->isMeetingRequestUpdateLocked();
        $isMeetingrequestClosed          = !$this->meetingRequestAccessChecker->allowedToAccess($query->event);
        $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($query->event);

        foreach ($meetingRequests as $meetingRequest) {
            $meetingRequestView = $this->meetingRequestViewQueryHandler->handle(
                new MeetingRequestViewQuery(
                    $meetingRequest,
                    $query->sheet,
                    $query->user,
                    $query->locale,
                    $isMeetingPublished,
                    $isMeetingRequestUpdateLocked,
                    $isMeetingrequestClosed,
                    $isAnsweringMeetingRequestClosed,
                    isset($viewedSheetListView[$meetingRequest->getToSheet()->getId()])
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
}
