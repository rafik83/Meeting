<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class ParticipationCount
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * ParticipationCount constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param Happening $happening
     *
     * @return int
     */
    public function getRemaining(Happening $happening)
    {
        return count($this->happeningParticipationRepository->findByHappening($happening));
    }

    /**
     * @param Happening $happening
     *
     * @return bool
     */
    public function isFull(Happening $happening)
    {
        return $this->getRemaining($happening) === 0;
    }
}
