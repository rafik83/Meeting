<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSpotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SpotView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class MeetingUpdateSpotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /**
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param MeetingUpdateSpotViewQuery $query
     *
     * @return MeetingUpdateSpotView
     */
    public function handle(MeetingUpdateSpotViewQuery $query)
    {
        return new MeetingUpdateSpotView(
            $query->meeting->getId(),
            $query->meeting->getSpot()->getId(),
            $query->meeting->isBlockedSlot(),
            $query->meeting->isBlockedSpot(),
            array_map(function (Spot $spot) {
                return new SpotView($spot->getId(), $spot->getReference());
            }, $this->spotRepository->getSpotsForMeeting($query->meeting))
        );
    }
}
