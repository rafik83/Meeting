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

class VisioGuesser
{
    /**
     * @param Meeting $meeting
     *
     * @return bool
     */
    public function hasMeetingParticipantVisio(Meeting $meeting)
    {
        $participants = $meeting->getAllParticipants();

        return $this->isParticipantVisio($participants);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasMeetingRequestParticipantVisio(Request $request)
    {
        $participants = $request->getAllParticipants();

        return $this->isParticipantVisio($participants);
    }

    /**
     * @param Participant[] $participants
     *
     * @return bool
     */
    public function isParticipantVisio(array $participants)
    {
        foreach ($participants as $participant) {
            if ($participant->isVisio()) {
                return true;
            }
        }

        return false;
    }
}
