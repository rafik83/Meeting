<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class UnparticipateHandler
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
     * @param Unparticipate $unparticipate
     */
    public function handle(Unparticipate $unparticipate)
    {
        $happeningParticipation = $this
            ->happeneningParticipationRepository
            ->findByHappeningAndParticipant($unparticipate->happening, $unparticipate->participant);

        if ($happeningParticipation) {
            $this->happeneningParticipationRepository->remove($happeningParticipation);
        }
    }
}
