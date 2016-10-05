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
use Proximum\Vimeet\Domain\View\TypeView;

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
     * @param MeetingRequestListViewQuery $query
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $query)
    {
        if (!empty($query->filters['type'])) {
            $query->filters['type'] = $this->transformTypeFilter($query->filters['type']);
        }

        $meetingRequests = $this->meetingRequestRepository
            ->getAllRequestBySheet($query->sheet, $query->filters);

        $meetingRequestListView = new MeetingRequestListView();

        foreach ($meetingRequests as $meetingRequest) {
            $meetingRequestView = $this->meetingRequestViewQueryHandler->handle(
                new MeetingRequestViewQuery(
                    $meetingRequest,
                    $query->sheet,
                    $query->locale
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
     * Transform TypeView[] to array of IDs
     *
     * @param $typeViews
     *
     * @return array
     */
    public function transformTypeFilter($typeViews)
    {
        $types = [];

        foreach($typeViews as $typeView) {
            $types[] = $typeView->id;
        }

        return $types;
    }
}
