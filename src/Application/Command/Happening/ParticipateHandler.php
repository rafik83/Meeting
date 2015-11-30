<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class ParticipateHandler
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeneningParticipationRepository;

    /**
     * ParticipateHandler constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeneningParticipationRepository
     */
    public function __construct(HappeningParticipationRepositoryInterface $happeneningParticipationRepository)
    {
        $this->happeneningParticipationRepository = $happeneningParticipationRepository;
    }

    /**
     * @param Participate $participate
     */
    public function handle(Participate $participate)
    {
        foreach ($participate->participants as $participant) {
            $happeningParticipation = new HappeningParticipation($participate->happening, $participant);
            $this->happeneningParticipationRepository->add($happeningParticipation);
        }
    }
}
