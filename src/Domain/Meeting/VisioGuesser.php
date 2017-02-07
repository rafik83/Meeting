<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

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
     * @param array $participants
     *
     * @return bool
     */
    private function isParticipantVisio(array $participants)
    {
        foreach($participants as $participant) {
            if ($participant->isVisio()) {
                return true;
            }
        }

        return false;
    }
}
