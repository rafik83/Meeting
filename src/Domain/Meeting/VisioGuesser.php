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
    public function hasMeetingParticipantVisio(Meeting $meeting)
    {
        $participants = $meeting->getParticipants();
        foreach($participants as $participant) {
            if ($participant->)
        }
    }

    public function hasMeetingRequestParticipantVisio(Request $request)
    {

    }

    private function isVisio()
    {

    }
}
