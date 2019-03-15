<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class AgendaView
{
    /** @var DayView[] */
    public $days;

    /** @var Sheet */
    public $sheet;

    /** @var Participant that owns the agenda looked */
    public $participant;

    /** @var bool */
    public $isUserAloneParticipant;

    /** @var ParticipantView[] */
    public $participants;

    /** @var bool */
    public $isPhoneValidationRequired;

    /** @var string */
    public $timezone;

    /** @var bool */
    public $canMoveMeeting;

    /** @var bool */
    public $canRemoveMeeting;

    public function __construct(
        array $dayViews,
        string $timezone,
        Sheet $sheet,
        Participant $participant,
        bool $isUserAloneParticipant,
        array $participants,
        bool $isPhoneValidationRequired,
        bool $canMoveMeeting,
        bool $canRemoveMeeting
    ) {
        $this->days = $dayViews;
        $this->sheet = $sheet;
        $this->participant = $participant;
        $this->isUserAloneParticipant = $isUserAloneParticipant;
        $this->participants = $participants;
        $this->isPhoneValidationRequired = $isPhoneValidationRequired;
        $this->timezone = $timezone;
        $this->canMoveMeeting = $canMoveMeeting;
        $this->canRemoveMeeting = $canRemoveMeeting;
    }

    /**
     * @return null|ParticipantView
     */
    public function getCurrentParticipantView(): ?ParticipantView
    {
        foreach ($this->participants as $participantView) {
            if ($participantView->id === $this->participant->getId()) {
                return $participantView;
            }
        }

        return null;
    }
}
