<?php

namespace Proximum\Vimeet\Application\Components\Happening\Participation;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UserParticipantAvailabilityReAggregator
{
    /** @var HappeningParticipationRepositoryInterface */
    private $participationRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param HappeningParticipationRepositoryInterface $participationRepository
     * @param JobQueueInterface                         $jobQueue
     * @param SheetRepositoryInterface                  $sheetRepository
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        JobQueueInterface $jobQueue,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->participationRepository = $participationRepository;
        $this->sheetRepository = $sheetRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param Happening $happening
     */
    public function recalculateAvailabilityAggregator(Happening $happening)
    {
        $participations = $this->participationRepository->findByHappening($happening);

        foreach ($participations as $participation) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($participation->getUser(), $happening->getEvent());

            foreach ($sheets as $sheet) {
                $this->jobQueue->aggregateSheetAvailableSlot($sheet);
            }
        }
    }
}
