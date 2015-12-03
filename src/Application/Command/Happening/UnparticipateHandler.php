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
     * @param Unparticipate $unparticipate
     */
    public function handle(Unparticipate $unparticipate)
    {
        $happeningParticipation = $this
            ->happeningParticipationRepository
            ->findByHappeningAndParticipant($unparticipate->happening, $unparticipate->participant);

        if ($happeningParticipation) {
            $this->happeningParticipationRepository->remove($happeningParticipation);
        }
    }
}
