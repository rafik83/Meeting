<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Repository\HappeneningParticipationRepositoryInterface;

class UnparticipateHandler
{
    /**
     * @var HappeneningParticipationRepositoryInterface
     */
    private $happeneningParticipationRepository;

    /**
     * ParticipateHandler constructor.
     *
     * @param HappeneningParticipationRepositoryInterface $happeneningParticipationRepository
     */
    public function __construct(HappeneningParticipationRepositoryInterface $happeneningParticipationRepository)
    {
        $this->happeneningParticipationRepository = $happeneningParticipationRepository;
    }

    /**
     * @param Unparticipate $unparticipate
     */
    public function handle(Unparticipate $unparticipate)
    {
    }
}
