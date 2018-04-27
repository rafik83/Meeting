<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

class RemoveResult
{
    /**
     * Array of participant's name
     *
     * @var array
     */
    private $participants;

    /**
     * @param array $participants
     */
    public function __construct(array $participants = [])
    {
        $this->participants = $participants;
    }

    /**
     * @return bool
     */
    public function hasParticipantWithMeeting()
    {
        return !empty($this->participants);
    }

    /**
     * @return int
     */
    public function countParticipants()
    {
        return \count($this->participants);
    }

    /**
     * @return string
     */
    public function getParticipantsName()
    {
        return implode(', ', $this->participants);
    }
}
