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
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param ParticipantRepositoryInterface            $participantRepository
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->participantRepository            = $participantRepository;
    }

    /**
     * @param Participate $participate
     *
     * @throws ParticipantNotAvailableException
     * @throws ParticipantRequiredException
     */
    public function handle(Participate $participate)
    {
        if (0 === count($participate->participants)) {
            throw new ParticipantRequiredException();
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipants(
            $participate->participants,
            $participate->happening->getBegin(),
            $participate->happening->getEnd()
        );

        foreach ($participate->participants as $participant) {
            $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndParticipant(
                $participate->happening,
                $participant
            );

            if (null === $happeningParticipation) {
                if (!in_array($participant, $availableParticipants)) {
                    throw new ParticipantNotAvailableException();
                }

                $this->happeningParticipationRepository->add(
                    new HappeningParticipation($participate->happening, $participant)
                );
            }
        }
    }
}
