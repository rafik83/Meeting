<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class FullHappeningQueryHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    public function handle(FullHappeningQuery $query): void
    {
        $participationCount = $this->happeningParticipationRepository->countParticipationByEvent($query->event);

        foreach ($query->programView->days as $day) {
            foreach ($day->happenings as $happening) {
                if (!$happening->hasParticipations() && null !== $happening->limitParticipant) {
                    if (isset($participationCount[$happening->getId()])
                        && $participationCount[$happening->getId()] >= $happening->limitParticipant
                    ) {
                        $happening->setFull();
                    }
                }
            }
        }
    }
}
