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

    /** @var bool */
    public $isUserParticipantMultipleSheet;

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

    /** @var bool */
    public $isParticipantVisio;

    /** @var bool */
    public $isItDDay;

    /** @var string */
    public $timezoneTranslated;

    public function __construct(
        array $dayViews,
        string $timezone,
        string $timezoneTranslated,
        Sheet $sheet,
        Participant $participant,
        bool $isUserAloneParticipant,
        bool $isUserParticipantMultipleSheet,
        array $participants,
        bool $isPhoneValidationRequired,
        bool $canMoveMeeting,
        bool $canRemoveMeeting,
        bool $isParticipantVisio,
        bool $isItDDay
    ) {
        $this->days = $dayViews;
        $this->sheet = $sheet;
        $this->participant = $participant;
        $this->isUserAloneParticipant = $isUserAloneParticipant;
        $this->isUserParticipantMultipleSheet = $isUserParticipantMultipleSheet;
        $this->participants = $participants;
        $this->isPhoneValidationRequired = $isPhoneValidationRequired;
        $this->timezone = $timezone;
        $this->timezoneTranslated = $timezoneTranslated;
        $this->canMoveMeeting = $canMoveMeeting;
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->isParticipantVisio = $isParticipantVisio;
        $this->isItDDay = $isItDDay;
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

    public function showSheetAgenda(): bool
    {
        return !$this->isUserAloneParticipant || $this->isUserParticipantMultipleSheet;
    }
}
