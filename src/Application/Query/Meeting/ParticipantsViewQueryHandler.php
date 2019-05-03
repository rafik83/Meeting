<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ParticipantsViewQueryHandler constructor.
     *
     * @param ParticipantInfoGuesser $participantInfoGuesser
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        TranslatorInterface $translator
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->translator = $translator;
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

            $participantContactEvaluation = null;
            $participantContactComment = null;
            foreach ($query->contacts as $contact) {
                if ($participant->getUser()->getId() === $contact->getId()) {
                    $participantContactEvaluation = $contact->getEvaluation();
                    $participantContactComment = $contact->getComment();
                    break;
                }
            }

            $gender = $participantInfo[Tag::PARTICIPANT_GENDER];

            $participantView[] = new MeetingParticipantView(
                $participantInfo[Tag::PARTICIPANT_FIRSTNAME],
                $participantInfo[Tag::PARTICIPANT_LASTNAME],
                $participantInfo[Tag::PARTICIPANT_POSITION],
                $participantInfo[Tag::PARTICIPANT_PHONE],
                $this->translator->trans('gender.'.$gender, [], 'messages'),
                $participant->getEmail(),
                $participantContactEvaluation,
                $participantContactComment
            );
        }

        return $participantView;
    }
}
