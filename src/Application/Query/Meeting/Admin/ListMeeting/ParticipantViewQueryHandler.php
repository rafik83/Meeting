<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    public function handle(ParticipantViewQuery $query): ParticipantView
    {
        return new ParticipantView(
            $query->participant->getId(),
            $this->participantInfoGuesser->guessParticipantCompleteName($query->participant, $query->locale)
        );
    }
}
