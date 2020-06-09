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
use Proximum\Vimeet\Domain\Model\Contact;
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
    public function handle(ParticipantsViewQuery $query): array
    {
        $meetingParticipantViews = [];
        $phoneVisible = true;
        $emailVisible = true;

        foreach ($query->participants as $participant) {
            $participantInfo = $this->participantInfoGuesser->guessParticipantInfos(
                $participant,
                $query->locale
            );

            $participantContactEvaluation = [];
            $participantContactComment = [];

            foreach ($query->contacts as $contact) {
                if ($participant->getUser()->getId() !== $contact->getContact()->getId()) {
                    continue;
                }

                if ($contact->hasEvaluation()) {
                    $participantContactEvaluation[] = $this->formatContactValue($contact, $contact->getEvaluation());
                }

                if ($contact->hasComment()) {
                    $participantContactComment[] = $this->formatContactValue($contact, $contact->getComment());
                }
            }

            $gender = $participantInfo[Tag::PARTICIPANT_GENDER];

            $meetingParticipantViews[] = new MeetingParticipantView(
                $participantInfo[Tag::PARTICIPANT_FIRSTNAME],
                $participantInfo[Tag::PARTICIPANT_LASTNAME],
                $participantInfo[Tag::PARTICIPANT_POSITION],
                $phoneVisible ? $participantInfo[Tag::PARTICIPANT_PHONE] : $this->translator->trans('info not available'),
                $gender ? $this->translator->trans('gender.'.$gender, [], 'messages') : '',
                $emailVisible ? $participant->getEmail() : $this->translator->trans('info not available'),
                implode("\n", $participantContactEvaluation),
                implode("\n", $participantContactComment)
            );
        }

        return $meetingParticipantViews;
    }

    private function formatContactValue(Contact $contact, string $value): string
    {
        return sprintf(
            '%s: %s',
            $contact->getUser()->getFullname(),
            $value
        );
    }
}
