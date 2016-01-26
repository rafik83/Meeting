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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Schedule;

interface HappeningRepositoryInterface
{
    /**
     * @param Happening $happening
     */
    public function add(Happening $happening);

    /**
     * @param Happening $happening
     */
    public function set(Happening $happening);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Happening[]
     */
    public function findByEvent(Event $event, $locale);

    /**
     * @param Schedule    $schedule
     * @param Participant $participant
     *
     * @return Happening[]
     */
    public function findByScheduleAndParticipant(Schedule $schedule, Participant $participant);
}
