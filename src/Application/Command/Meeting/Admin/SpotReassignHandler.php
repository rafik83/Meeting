<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

/**
 * Reassign meeting spot when one of sheet's spot is available
 */
class SpotReassignHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var VisioGuesser */
    private $visioGuesser;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SpotRepositoryInterface $spotRepository,
        VisioGuesser $visioGuesser
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->spotRepository = $spotRepository;
        $this->visioGuesser = $visioGuesser;
    }

    public function handle(SpotReassign $spotReassign): void
    {
        $meetings = $this->meetingRepository->getNonBlockedSpotByEvent($spotReassign->event);

        foreach ($meetings as $meeting) {
            $fromSpot = $meeting->getFromSheet()->getSpot();
            $toSpot   = $meeting->getToSheet()->getSpot();

            // do not process meeting spot which already a meeting sheet's spot
            if (\in_array($meeting->getSpot(), [$fromSpot, $toSpot])) {
                continue;
            }

            // Get available spots for this slot and meeting
            $spots = $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
                $meeting->getSlot(),
                $meeting->countParticipants(),
                $meeting,
                null,
                null,
                $this->visioGuesser->hasMeetingParticipantVisio($meeting)
            );

            /** @var Spot|false $bestSpot */
            $bestSpot = reset($spots);

            if (false === $bestSpot) {
                continue;
            }

            if ($meeting->getSpot()->getId() !== $bestSpot->getId()) {
                $meeting->updateSpot($bestSpot, $meeting->isBlockedSpot(), $meeting->isBlockedSlot());
                $this->meetingRepository->set($meeting);
            }
        }
    }
}
