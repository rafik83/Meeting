<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @return null|HappeningParticipation
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

    /**
     * @param Event $event
     *
     * @return array
     */
    public function countParticipationByEvent(Event $event);
  
    /**
     * @param Sheet       $sheet
     * @param Happening[] $happenings
     *
     * @return HappeningParticipation[]
     */
    public function getParticipationsForSheet(Sheet $sheet, $happenings);

    /**
     * @param Participant $participant
     * @param Happening   $happening
     */
    public function removeParticipantForHappening(Participant $participant, Happening $happening);

    /**
     * @param Participant $participant
     *
     * @return null|int
     */
    public function checkAnyParticipation(Participant $participant);
}
