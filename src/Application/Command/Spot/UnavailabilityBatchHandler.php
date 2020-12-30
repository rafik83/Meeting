<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;

class UnavailabilityBatchHandler
{
    /**
     * @var SpotUnavailabilityRepositoryInterface
     */
    private $spotUnavailabilityRepository;

    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * UnavailabilityBatchHandler constructor.
     *
     * @param SpotRepositoryInterface               $spotRepository
     * @param SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository
     * @param MeetingRepositoryInterface            $meetingRepository
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->spotUnavailabilityRepository = $spotUnavailabilityRepository;
        $this->spotRepository               = $spotRepository;
        $this->meetingRepository            = $meetingRepository;
    }

    /**
     * @param UnavailabilityBatch $batch
     *
     * @return UnavailabilityBatchResult
     */
    public function handle(UnavailabilityBatch $batch)
    {
        $spots = $this->spotRepository->findMany($batch->getEvent(), $batch->getSpotIds());

        $spotWithMeetingWarning = [];

        foreach ($spots as $spot) {
            // remove all spot unavailability for this spot
            $this->spotUnavailabilityRepository->remove($spot);

            foreach ($batch->meetingSlots as $meetingSlot) {
                $meetings = $this->meetingRepository->findBySpotAndSlot($spot, $meetingSlot);

                if (count($meetings) > 0) {
                    $spotWithMeetingWarning[$spot->getId()] = $spot;

                    continue;
                }

                $spotUnavailability = new SpotUnavailability($meetingSlot, $spot);
                $this->spotUnavailabilityRepository->add($spotUnavailability);
            }
        }

        return new UnavailabilityBatchResult($spotWithMeetingWarning);
    }
}
