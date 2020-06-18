<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Application\Components\Participant\ParticipantGuesser;
use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class GetTimezoneHelper
{
    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    /** @var ParticipantGuesser */
    private $participantGuesser;

    public function __construct(IsParticipantVisio $isParticipantVisio, ParticipantGuesser $participantGuesser)
    {
        $this->isParticipantVisio = $isParticipantVisio;
        $this->participantGuesser = $participantGuesser;
    }

    public function getTimezoneByEventAndParticipant(Event $event, Participant $participant): string
    {
        if ($this->isParticipantVisio->isSatisfiedBy($participant) && $participant->getTimezone()) {
            return $participant->getTimezone();
        }

        return $event->getTimeZone();
    }

    public function getTimezoneByEventAndUser(Event $event, User $user): string
    {
        try {
            $participant = $this->participantGuesser->getUserEventParticipant($user, $event);

            return $this->getTimezoneByEventAndParticipant($event, $participant);
        } catch (ParticipantNotFoundException $exception) {
        } catch (SheetNotFoundException $exception) {
        }

        return $event->getTimeZone();
    }
}
