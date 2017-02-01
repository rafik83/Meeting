<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantsViewQueryHandler
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ParticipantsViewQueryHandler constructor.
     *
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param ParticipantsViewQuery $query
     *
     * @return MeetingParticipantView[]
     */
    public function handle(ParticipantsViewQuery $query)
    {
        $participantView = [];

        foreach ($query->participants as $participant) {
            $participantInfo = $this->participantInfoGuesser->guessParticipantInfos(
                $participant,
                $query->locale
            );

            $participantView[] = new MeetingParticipantView(
                $participantInfo[Tag::PARTICIPANT_FIRSTNAME],
                $participantInfo[Tag::PARTICIPANT_LASTNAME],
                $participantInfo[Tag::PARTICIPANT_POSITION],
                $participantInfo[Tag::PARTICIPANT_PHONE],
                $participantInfo[Tag::PARTICIPANT_GENDER],
                $participant->getEmail()
            );
        }

        return $participantView;
    }
}
