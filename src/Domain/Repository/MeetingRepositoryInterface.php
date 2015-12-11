<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;
use Proximum\Vimeet\Domain\Model\Meeting;

interface MeetingRepositoryInterface
{
    /**
     * @param Schedule    $schedule
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function findByScheduleAndParticipant(Schedule $schedule, Participant $participant);

    /**
     * @param Meeting $meeting
     *
     * @return mixed
     */
    public function add(Meeting $meeting);
}
