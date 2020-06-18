<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class GetTimezoneHelper
{
    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    /** @var SheetGuesser */
    private $sheetGuesser;

    public function __construct(IsParticipantVisio $isParticipantVisio, SheetGuesser $sheetGuesser)
    {
        $this->isParticipantVisio = $isParticipantVisio;
        $this->sheetGuesser = $sheetGuesser;
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
        $sheet = $this->sheetGuesser->getUserSheet($user, $event, $event->getLocaleFallback());
        $participant = $sheet->getUserParticipant($user);

        if (!$participant) {
            throw new ParticipantNotFoundException('Participant not found in the given sheet');
        }

        return $this->getTimezoneByEventAndParticipant($event, $participant);
    }
}
