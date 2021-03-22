<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedSpotEvent;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UpdateSpotHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    private MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SpotRepositoryInterface $spotRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->spotRepository = $spotRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->meetingUpdateSlotViewQueryHandler = $meetingUpdateSlotViewQueryHandler;
    }

    /**
     * @param UpdateSpot $updateSpot
     *
     * @throws MeetingIsBlockedSpotException
     * @throws SpotNotAvailableForThisMeetingException
     */
    public function handle(UpdateSpot $updateSpot)
    {
        // Update participants
        $updateSpot->meeting->setParticipants($updateSpot->sheet, $updateSpot->participants);

        // Get available slots
        $meetingUpdateSlotView = $this->meetingUpdateSlotViewQueryHandler->handle(
            new MeetingUpdateSlotViewQuery($updateSpot->meeting, $updateSpot->visio)
        );

        // Check if selected slot is in available slots
        if (false === \in_array($updateSpot->slot->getId(), $meetingUpdateSlotView->availableSlotsId, true)) {
            throw new SlotNotAvailableForThisMeetingException();
        }

        // Update slot
        $updateSpot->meeting->updateSlot($updateSpot->slot);

        // Update spot
        $isMeetingSpotChanged     = $updateSpot->spot !== $updateSpot->meeting->getSpot();
        $isMeetingSpotStayBlocked = $updateSpot->meeting->isBlockedSpot() && $updateSpot->isBlockedSpot();

        if (true === $isMeetingSpotChanged && true === $isMeetingSpotStayBlocked) {
            throw new MeetingIsBlockedSpotException();
        }

        if (false === \in_array(
            $updateSpot->spot,
            $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
                $updateSpot->meeting->getSlot(),
                $updateSpot->meeting->countParticipants(),
                $updateSpot->meeting,
                null,
                null,
                $updateSpot->visio
            )
        )) {
            throw new SpotNotAvailableForThisMeetingException();
        }

        $updateSpot->meeting->updateSpot($updateSpot->spot, $updateSpot->blockedSpot, $updateSpot->blockedSlot);
        $updateSpot->meeting->resetStatus();
        $this->meetingRepository->set($updateSpot->meeting);

        $this->delayedEventDispatcher->dispatch(
            Events::MEETING_MOVED_SPOT,
            new MeetingMovedSpotEvent($updateSpot->meeting)
        );
    }
}
