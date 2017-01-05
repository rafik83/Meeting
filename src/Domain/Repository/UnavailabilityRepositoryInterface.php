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
use Proximum\Vimeet\Domain\Model\Unavailability;

interface UnavailabilityRepositoryInterface
{
    /**
     * @param Unavailability $unavailability
     */
    public function add(Unavailability $unavailability);

    /**
     * @param Unavailability $unavailability
     */
    public function set(Unavailability $unavailability);

    /**
     * @param Unavailability $unavailability
     */
    public function remove(Unavailability $unavailability);

    /**
     * @param Event $event
     *
     * @return Unavailability[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Participant $participant
     *
     * @return int
     */
    public function countByParticipant(Participant $participant);

    /**
     * @param Participant $participant
     *
     * @return Unavailability[]
     */
    public function findByParticipant(Participant $participant);

    /**
     * @param Unavailability $unavailability
     *
     * @return Unavailability[]
     */
    public function getOverlapUnavailabilities(Unavailability $unavailability);
}
