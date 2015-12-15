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
    public function findScheduledByScheduleAndParticipant(Schedule $schedule, Participant $participant);

    /**
     * @param Meeting $meeting
     */
    public function add(Meeting $meeting);

    /**
     * @param Meeting $meeting
     */
    public function set(Meeting $meeting);

    /**
     * @param Event $event
     * @param int   $page
     * @param int   $limit
     *
     * @return Meeting[]
     */
    public function getByEvent(Event $event, $page, $limit);
}
