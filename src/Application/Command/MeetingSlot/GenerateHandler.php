<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\GeneratedEvent;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class GenerateHandler
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SlotGenerator */
    private $slotGenerator;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * GenerateHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param SlotGenerator                   $slotGenerator
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SlotGenerator $slotGenerator,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotGenerator         = $slotGenerator;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Generate $generate
     *
     * @return GenerateResult
     */
    public function handle(Generate $generate)
    {
        $slots = $this->slotGenerator->generate($generate->event, $generate->recipes);

        foreach ($slots as $slot) {
            $this->meetingSlotRepository->add($slot);
        }

        $this->delayedEventDispatcher->dispatch(Events::SLOT_GENERATED, new GeneratedEvent($generate->event));

        return new GenerateResult(count($slots));
    }
}
