<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;

interface MeetingRepositoryInterface
{
    /**
     * @param Schedule    $schedule
     * @param Participant $participant
     *
     * @return Meeting[]
     */
    public function findScheduledByScheduleAndParticipant(Schedule $schedule, Participant $participant);

    /**
     * @param Meeting $meeting
     */
    public function set(Meeting $meeting);
}
