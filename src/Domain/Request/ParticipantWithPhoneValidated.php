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
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

/**
 * Get participant with tip confirmation phone enabled and phone validated from meeting request
 *
 * @see UserEventPhoneChecker Check if participant user has phone validated
 */
class ParticipantWithPhoneValidated
{
    /**
     * @var UserEventPhoneChecker
     */
    private $userEventPhoneChecker;

    /**
     * ParticipantWithPhoneValidated constructor.
     *
     * @param UserEventPhoneChecker $userEventPhoneChecker
     */
    public function __construct(UserEventPhoneChecker $userEventPhoneChecker)
    {
        $this->userEventPhoneChecker = $userEventPhoneChecker;
    }

    /**
     * @param Event $event
     * @param array $participants
     *
     * @return null|Participant
     */
    public function getParticipant(Event $event, array $participants): ?Participant
    {
        foreach ($participants as $participant) {
            if ($this->userEventPhoneChecker->isValidated($participant->getUser(), $event)) {
                return $participant;
            }
        }

        return null;
    }
}
