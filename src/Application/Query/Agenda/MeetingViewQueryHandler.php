<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * MeetingViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository
    ) {
        $this->requestRepository = $requestRepository;
    }
    /**
     * @param MeetingViewQuery $query
     *
     * @return MassUnavailabilityView
     */
    public function handle(MeetingViewQuery $query)
    {
        return new MeetingView(
            $query->meeting->getSpot(),
            $query->meeting->getId(),
            $query->meeting->getOtherSheet(),
            $query->meeting->hasNoPreference()
        );
    }
}
