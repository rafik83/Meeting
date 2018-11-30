<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class VisioGuesser
{
    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(IsParticipantVisio $isParticipantVisio)
    {
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function hasMeetingParticipantVisio(Meeting $meeting): bool
    {
        $participants = $meeting->getAllParticipants();

        return $this->isParticipantVisio($participants);
    }

    public function hasMeetingRequestParticipantVisio(Request $request): bool
    {
        $participants = $request->getAllParticipants();

        return $this->isParticipantVisio($participants);
    }

    /**
     * @param Participant[] $participants
     *
     * @return bool
     */
    public function isParticipantVisio(array $participants): bool
    {
        foreach ($participants as $participant) {
            if ($this->isParticipantVisio->isSatisfiedBy($participant)) {
                return true;
            }
        }

        return false;
    }
}
