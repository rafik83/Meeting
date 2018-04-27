<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotManager
{
    const INTERVAL = 5;
    const DURATION = 10;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SlotGenerator */
    private $slotGenerator;

    /**
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param SlotGenerator                  $slotGenerator
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository, SlotGenerator $slotGenerator)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotGenerator         = $slotGenerator;
    }

    /**
     * @param Event $event
     * @param int   $quantity
     */
    public function create(Event $event, $quantity)
    {
        $interval = self::INTERVAL;
        $duration = self::DURATION;

        $now   = new \DateTime();
        $begin = new \DateTime(sprintf('%s %s', $now->format('Y-m-d'), '08:00:00'));
        $end   = clone $begin;
        $end->add(new \DateInterval(sprintf('PT%sM', ($interval + $duration) * $quantity)));

        $slots = $this->slotGenerator->generate(
            $event,
            [new Recipe($begin, $end, $interval, $duration)]
        );

        foreach ($slots as $slot) {
            $this->meetingSlotRepository->add($slot);
        }
    }

    /**
     * @param Event $event
     *
     * @return MeetingSlot[]
     */
    public function findByEvent(Event $event)
    {
        return $this->meetingSlotRepository->findByEvent($event);
    }

    /**
     * @param Event $event
     * @param int   $slotId
     *
     * @return null|MeetingSlot
     */
    public function findByEventAndId(Event $event, $slotId)
    {
        return $this->meetingSlotRepository->find($event, $slotId);
    }
}
