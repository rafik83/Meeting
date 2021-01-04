<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedEvent;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UpdateSlotHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingUpdateSlotViewQueryHandler */
    private $meetingUpdateSlotViewQueryHandler;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param MeetingRepositoryInterface        $meetingRepository
     * @param SpotRepositoryInterface           $spotRepository
     * @param MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler
     * @param DelayedEventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SpotRepositoryInterface $spotRepository,
        MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->meetingRepository                 = $meetingRepository;
        $this->spotRepository                    = $spotRepository;
        $this->meetingUpdateSlotViewQueryHandler = $meetingUpdateSlotViewQueryHandler;
        $this->eventDispatcher                   = $eventDispatcher;
    }

    /**
     * @param UpdateSlot $updateSlot
     *
     * @throws BlockedSpotNotAvailableForThisMeetingAndSlotException
     * @throws MeetingIsBlockedSlotException
     * @throws NoSpotsAvailableForThisSlotAndMeetingException
     * @throws SlotNotAvailableForThisMeetingException
     */
    public function handle(UpdateSlot $updateSlot)
    {
        // Check if meeting can be moved
        if (true === $updateSlot->meeting->isBlockedSlot() && false === $updateSlot->isUpdatedByParticipant) {
            throw new MeetingIsBlockedSlotException();
        }

        // Get available slots
        $meetingUpdateSlotView = $this->meetingUpdateSlotViewQueryHandler->handle(
            new MeetingUpdateSlotViewQuery($updateSlot->meeting, $updateSlot->visio)
        );

        // Check if selected slot is in available slots
        if (false === \in_array($updateSlot->slot->getId(), $meetingUpdateSlotView->availableSlotsId, true)) {
            throw new SlotNotAvailableForThisMeetingException();
        }

        // Get available spots for this slot and meeting
        $spots = $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $updateSlot->slot,
            $updateSlot->meeting->countParticipants(),
            $updateSlot->meeting,
            $updateSlot->meeting->getFromSheet(),
            $updateSlot->meeting->getToSheet(),
            $updateSlot->visio
        );

        // If no spot available
        if (0 === \count($spots)) {
            throw new NoSpotsAvailableForThisSlotAndMeetingException();
        }

        // Get first spot
        $newSpot = reset($spots);

        // Is meeting blockedSpot, keep same spot
        if (true === $updateSlot->meeting->isBlockedSpot()) {
            $newSpot = $updateSlot->meeting->getSpot();

            // Current meeting spot not available for selected slot
            if (false === \in_array($updateSlot->meeting->getSpot(), $spots)) {
                throw new BlockedSpotNotAvailableForThisMeetingAndSlotException();
            }
        }

        $updateSlot->meeting->updateSlotAndSpot($updateSlot->slot, $newSpot);
        $updateSlot->meeting->resetStatus();
        $this->meetingRepository->set($updateSlot->meeting);

        $this->eventDispatcher->dispatch(Events::MEETING_MOVED, new MeetingMovedEvent($updateSlot->meeting));
    }
}
