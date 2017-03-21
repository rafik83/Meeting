<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
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
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /**
     * MeetingRequestListViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface     $meetingRequestRepository
     * @param MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler
     * @param MeetingPublishedAccessChecker  $meetingPublishedAccessChecker
     */
    public function __construct(
        RequestRepositoryInterface $meetingRequestRepository,
        MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker
    ) {
        $this->meetingRequestRepository       = $meetingRequestRepository;
        $this->meetingRequestViewQueryHandler = $meetingRequestViewQueryHandler;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
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

        $meetingRequestListView = new MeetingRequestListView();
        $isMeetingPublished     = $this->meetingPublishedAccessChecker->allowedToAccess($query->event);

        $isMeetingRequestUpdateLocked = $query->event->getConfiguration()->isMeetingRequestUpdateLocked();

        foreach ($meetingRequests as $meetingRequest) {
            $meetingRequestView = $this->meetingRequestViewQueryHandler->handle(
                new MeetingRequestViewQuery(
                    $meetingRequest,
                    $query->sheet,
                    $query->locale,
                    $isMeetingPublished,
                    $isMeetingRequestUpdateLocked
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
