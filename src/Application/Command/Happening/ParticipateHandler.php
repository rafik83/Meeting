<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class ParticipateHandler
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * ParticipateHandler constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param Participate $participate
     *
     * @throws ParticipantNotAvailableException
     */
    public function handle(Participate $participate)
    {
        foreach ($participate->participants as $participant) {
            $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndParticipant(
                $participate->happening,
                $participant
            );

            $isNotAvailable = true;

            if ($isNotAvailable) {
                throw new ParticipantNotAvailableException();
            }

            if (null === $happeningParticipation) {
                $this->happeningParticipationRepository->add(
                    new HappeningParticipation($participate->happening, $participant)
                );
            }
        }
    }
}
