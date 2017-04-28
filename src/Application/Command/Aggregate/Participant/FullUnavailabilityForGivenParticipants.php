<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

class FullUnavailabilityForGivenParticipants
{
    /** @var int[] */
    public $participantIds;

    /**
     * @param array $participantIds
     */
    public function __construct(array $participantIds)
    {
        $this->participantIds = $participantIds;
    }
}
