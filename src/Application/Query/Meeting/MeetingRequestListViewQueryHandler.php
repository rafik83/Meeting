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
     * MeetingRequestListViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface     $meetingRequestRepository
     * @param MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler
     */
    public function __construct(
        RequestRepositoryInterface $meetingRequestRepository,
        MeetingRequestViewQueryHandler $meetingRequestViewQueryHandler
    ) {
        $this->meetingRequestRepository       = $meetingRequestRepository;
        $this->meetingRequestViewQueryHandler = $meetingRequestViewQueryHandler;
    }

    /**
     * @param MeetingRequestListViewQuery $meetingRequestListViewQuery
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $meetingRequestListViewQuery)
    {
        $meetingRequests = $this->meetingRequestRepository
            ->getAllRequestBySheet($meetingRequestListViewQuery->sheet, $meetingRequestListViewQuery->filters);

        $meetingRequestListView = new MeetingRequestListView();

        foreach ($meetingRequests as $meetingRequest) {
            $meetingRequestView = $this->meetingRequestViewQueryHandler->handle(
                new MeetingRequestViewQuery(
                    $meetingRequest,
                    $meetingRequestListViewQuery->sheet,
                    $meetingRequestListViewQuery->locale
                )
            );

            $meetingRequestListView->addRequestView($meetingRequestView);
        }

        if (!empty($meetingRequestListViewQuery->filters['orderBy'])) {
            $order = $meetingRequestListViewQuery->filters['orderBy'];
            $meetingRequestListView->sortBy($order);
        }

        return $meetingRequestListView;
    }
}
