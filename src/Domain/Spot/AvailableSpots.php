<?php

namespace Proximum\Vimeet\Domain\Spot;

use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class AvailableSpots
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * AvailableSpots constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param MeetingSlot $slot
     * @param Sheet       $fromSheet
     * @param Sheet       $toSheet
     * @param int         $participantQuantity
     * @param bool        $isVisio
     *
     * @throws NoSpotsAvailableForThisSlotAndMeetingException
     *
     * @return Spot
     */
    public function getBySlot(
        MeetingSlot $slot,
        Sheet $fromSheet,
        Sheet $toSheet,
        int $participantQuantity,
        bool $isVisio
    ): Spot {
        // Get available spots for this slot and meeting
        $spots = $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot,
            $participantQuantity,
            null,
            $fromSheet,
            $toSheet,
            $isVisio
        );

        // If no spot available
        if (0 === count($spots)) {
            throw new NoSpotsAvailableForThisSlotAndMeetingException();
        }

        // Get first spot
        $spot = reset($spots);

        return $spot;
    }
}
