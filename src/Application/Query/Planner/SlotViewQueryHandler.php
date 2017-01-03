<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $slotRepository;

    /**
     * @param MeetingSlotRepositoryInterface $slotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return SlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $slotViews = [];

        $slots = $this->slotRepository->findByEvent($query->event);

        foreach ($slots as $slot) {
            $day = $this->getCorrespondingDay($query, $slot);

            if (null !== $day) {
                $slotViews[] = new SlotView(
                    $slot->getId(),
                    'index' . $slot->getId(),
                    $slot->getBegin()->format('H'),
                    $slot->getBegin()->format('i'),
                    $day
                );
            }
        }

        return $slotViews;
    }

    /**
     * @param SlotViewQuery $query
     * @param MeetingSlot   $slot
     *
     * @return null|Day
     */
    public function getCorrespondingDay(SlotViewQuery $query, MeetingSlot $slot)
    {
        foreach ($query->days as $day) {
            if (intval($slot->getBegin()->format('d')) === $day->day
                && intval($slot->getBegin()->format('m')) === $day->month
                && intval($slot->getBegin()->format('Y')) === $day->year
            ) {
                return $day;
            }
        }

        return null;
    }
}
