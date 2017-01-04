<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class ParticipantViewQueryHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface    $participantRepository
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository
    ) {
        $this->participantRepository    = $participantRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    /**
     * @param ParticipantViewQuery $query
     *
     * @return ParticipantView[]
     */
    public function handle(ParticipantViewQuery $query)
    {
        $participantViews = [];

        foreach ($query->sheets as $sheet) {
            $participants = $this->participantRepository->getParticipantsBySheetId($sheet->id);

            if (!empty($participants)) {
                foreach ($participants as $participant) {
                    $participantViews[] = new ParticipantView(
                        $participant->getId(),
                        $participant->getUser()->getAccount()->getCompleteName(),
                        $sheet,
                        []
                    );
                }

                // TO DO, Calculate the unavailability by slot
            }
        }

        return $participantViews;
    }
}
