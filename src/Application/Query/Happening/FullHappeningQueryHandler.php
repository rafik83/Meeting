<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class FullHappeningQueryHandler
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param FullHappeningQuery $query
     */
    public function handle(FullHappeningQuery $query)
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
