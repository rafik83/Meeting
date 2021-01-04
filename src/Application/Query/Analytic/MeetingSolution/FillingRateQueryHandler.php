<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class FillingRateQueryHandler
{
    /** @var array */
    public $spotUnavailabilities;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
        $this->spotUnavailabilities = [];
    }

    /**
     * @param FillingRateQuery $query
     *
     * @return int
     */
    public function handle(FillingRateQuery $query): int
    {
        $capacity = 0;

        foreach ($query->slots as $slot) {
            foreach ($query->spots as $spot) {
                $capacity += $this->getSpotCapacityForSlot($spot, $slot);
            }
        }

        // Avoid division by 0
        if (0 === $capacity) {
            $capacity = 1;
        }

        $numberOfMeetingOnShared = $this->meetingRepository->countMeetingForSpots($query->spots);
        $fillingRate = 100 * ($numberOfMeetingOnShared / $capacity);

        return $fillingRate;
    }

    /**
     * @param Spot        $spot
     * @param MeetingSlot $slot
     *
     * @return int
     */
    private function getSpotCapacityForSlot(Spot $spot, MeetingSlot $slot): int
    {
        if (!isset($this->spotUnavailabilities[$spot->getId()])) {
            $this->spotUnavailabilities[$spot->getId()] = [];

            foreach ($spot->getSpotUnavailabilities() as $unavailability) {
                $this->spotUnavailabilities[$spot->getId()][$unavailability->getSlot()->getId()] = true;
            }
        }

        if (isset($this->spotUnavailabilities[$spot->getId()][$slot->getId()])) {
            return 0;
        }

        return $spot->getMeetingCapacity();
    }
}
