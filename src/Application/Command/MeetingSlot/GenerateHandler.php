<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class GenerateHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var SlotGenerator
     */
    private $slotGenerator;

    /**
     * GenerateHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param SlotGenerator                  $slotGenerator
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository, SlotGenerator $slotGenerator)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotGenerator         = $slotGenerator;
    }

    /**
     * @param Generate $generate
     *
     * @return GenerateResult
     */
    public function handler(Generate $generate)
    {
        $slots = $this->slotGenerator->generate($generate->event, $generate->recipes);

        foreach ($slots as $slot) {
            $this->meetingSlotRepository->add($slot);
        }

        return new GenerateResult(count($slots));
    }
}
