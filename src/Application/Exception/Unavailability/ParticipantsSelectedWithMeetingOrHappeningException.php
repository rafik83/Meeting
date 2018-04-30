<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Unavailability;

class ParticipantsSelectedWithMeetingOrHappeningException extends UnavailabilityException
{
    /**
     * Array of participants name
     *
     * @var array
     */
    public $participants;

    /**
     * @param array $participants
     */
    public function __construct(array $participants = [])
    {
        parent::__construct('Participants selected have meeting or happening conflict with the selected unavailability');

        $this->participants = $participants;
    }

    /**
     * @return int
     */
    public function getNumberOfConflict()
    {
        return count($this->participants);
    }

    /**
     * @return string
     */
    public function getListOfParticipantsName()
    {
        return implode(', ', $this->participants);
    }
}
