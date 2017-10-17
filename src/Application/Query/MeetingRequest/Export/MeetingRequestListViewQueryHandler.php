<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MeetingRequest\Export;

use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestListView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingRequestListViewQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingRequestViewQueryHandler */
    private $requestViewQueryHandler;

    /**
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingRequestViewQueryHandler $requestViewQueryHandler
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MeetingRequestViewQueryHandler $requestViewQueryHandler
    ) {
        $this->requestRepository = $requestRepository;
        $this->requestViewQueryHandler = $requestViewQueryHandler;
    }

    /**
     * @param MeetingRequestListViewQuery $query
     *
     * @return MeetingRequestListView
     */
    public function handle(MeetingRequestListViewQuery $query): MeetingRequestListView
    {
        $locale   = $query->event->getFallback();
        $requests = $this->requestRepository->findByEvent($query->event);

        $requestViews = [];

        foreach ($requests as $request) {
            $requestViews[] = $this->requestViewQueryHandler->handle(new MeetingRequestViewQuery($request, $locale));
        }

        return new MeetingRequestListView(
            $requestViews
        );
    }
}
