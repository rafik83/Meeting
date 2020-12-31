<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\DeletedEvent;
use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToRemoveSlotException;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class RemoveHandler
{
    /** @var MeetingSlotRepositoryInterface */
    public $meetingSlotRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MeetingRepositoryInterface $meetingRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingRepository = $meetingRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Remove $command
     *
     * @throws IsNotAllowedToRemoveSlotException
     */
    public function handle(Remove $command)
    {
        if ($this->meetingRepository->hasMeetingOnSlot($command->meetingSlot)) {
            throw new IsNotAllowedToRemoveSlotException('Slot already used by scheduled meetings');
        }

        $this->meetingSlotRepository->remove($command->meetingSlot);

        $this->delayedEventDispatcher->dispatch(
            Events::SLOT_DELETED,
            new DeletedEvent($command->meetingSlot->getEvent())
        );
    }
}
