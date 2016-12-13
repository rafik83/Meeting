<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;

interface HappeningParticipationRepositoryInterface
{
    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function add(HappeningParticipation $happeningParticipation);

    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function remove(HappeningParticipation $happeningParticipation);

    /**
     * @param Happening   $happening
     * @param Participant $participant
     *
     * @return HappeningParticipation
     */
    public function findByHappeningAndParticipant(Happening $happening, Participant $participant);

    /**
     * @param Participant $participant
     *
     * @return HappeningParticipation[]
     */
    public function findByParticipant(Participant $participant);

    /**
     * @param Happening $happening
     *
     * @return int
     */
    public function countParticipationByHappening(Happening $happening);
}
