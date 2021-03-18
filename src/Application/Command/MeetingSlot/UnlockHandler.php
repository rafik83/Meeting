<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\ToggleLockedEvent;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class UnlockHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * UnlockHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Unlock $command
     */
    public function handle(Unlock $command)
    {
        $this->meetingSlotRepository->set($command->meetingSlot->unlock());

        $this->delayedEventDispatcher->dispatch(
            Events::SLOT_TOGGLE_LOCKED,
            new ToggleLockedEvent($command->meetingSlot->getEvent())
        );
    }
}
