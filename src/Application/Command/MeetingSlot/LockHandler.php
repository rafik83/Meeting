<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\ToggleLockedEvent;
use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToLockSlotException;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class LockHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * LockHandler constructor.
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
     * @param Lock $command
     *
     * @throws IsNotAllowedToLockSlotException
     */
    public function handle(Lock $command)
    {
        $meetingSlotWithMeetingIds = $this
            ->meetingSlotRepository
            ->findWithAtLeastOneMeetingByEvent($command->meetingSlot->getEvent());

        if (isset($meetingSlotWithMeetingIds[$command->meetingSlot->getId()])) {
            throw new IsNotAllowedToLockSlotException('Slot already used by scheduled meetings');
        }

        $this->meetingSlotRepository->set($command->meetingSlot->lock());

        $this->delayedEventDispatcher->dispatch(
            Events::SLOT_TOGGLE_LOCKED,
            new ToggleLockedEvent($command->meetingSlot->getEvent())
        );
    }
}
