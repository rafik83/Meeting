<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
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

    /**
     * @param array             $dayViews
     * @param Sheet             $sheet
     * @param Participant       $participant
     * @param bool              $isUserAloneParticipant
     * @param ParticipantView[] $participants
     * @param bool              $isPhoneValidationRequired
     */
    public function __construct(
        array $dayViews,
        Sheet $sheet,
        Participant $participant,
        bool $isUserAloneParticipant,
        array $participants,
        bool $isPhoneValidationRequired
    ) {
        $this->days                   = $dayViews;
        $this->sheet                  = $sheet;
        $this->participant            = $participant;
        $this->isUserAloneParticipant = $isUserAloneParticipant;
        $this->participants           = $participants;
        $this->isPhoneValidationRequired = $isPhoneValidationRequired;
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
