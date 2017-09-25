<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Request;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Tip\ConfirmationPhoneTipChecker;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

/**
 * Get participant with tip confirmation phone enabled and phone validated from meeting request
 *
 * @see ConfirmationPhoneTipChecker Check if Tip Confirmation Phone is enabled for sheet type
 * @see UserEventPhoneChecker Check if participant user has phone validated
 */
class ParticipantWithPhoneValidated
{
    /**
     * @var ConfirmationPhoneTipChecker
     */
    private $confirmationPhoneTipChecker;

    /**
     * @var UserEventPhoneChecker
     */
    private $userEventPhoneChecker;

    /**
     * ParticipantWithPhoneValidated constructor.
     *
     * @param ConfirmationPhoneTipChecker $confirmationPhoneTipChecker
     * @param UserEventPhoneChecker       $userEventPhoneChecker
     */
    public function __construct(
        ConfirmationPhoneTipChecker $confirmationPhoneTipChecker,
        UserEventPhoneChecker $userEventPhoneChecker
    ) {
        $this->confirmationPhoneTipChecker = $confirmationPhoneTipChecker;
        $this->userEventPhoneChecker       = $userEventPhoneChecker;
    }

    /**
     * @param Event $event
     * @param Sheet $sheet
     * @param array $participants
     *
     * @return null|Participant
     * @throws \Exception
     */
    public function getParticipant(Event $event, Sheet $sheet, array $participants): ?Participant
    {
        if (!$this->confirmationPhoneTipChecker->isEnabled($event, $sheet->getType())) {
            throw new \Exception(); //TODO: changer l'exception
        }

        foreach ($participants as $participant) {
            if ($this->userEventPhoneChecker->isValidated($participant->getUser(), $event)) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return null|Participant
     * @throws \Exception
     *
     */
    public function getFromParticipant(Request $request): ?Participant
    {
        $event = $request->getEvent();
        $sheet = $request->getFromSheet();

        return $this->getParticipant(
            $event,
            $sheet,
            $request->getFromParticipantsArray()
        );
    }

    /**
     * @param Request $request
     *
     * @return null|Participant
     * @throws \Exception
     */
    public function getToParticipant(Request $request)
    {
        $event = $request->getEvent();
        $sheet = $request->getToSheet();

        return $this->getParticipant(
            $event,
            $sheet,
            $request->getToParticipantsArray()
        );
    }
}
