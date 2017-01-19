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
    /**
     * @var DayView[]
     */
    public $days;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * Participant that owns the agenda looked
     *
     * @var Participant
     */
    public $participant;

    /**
     * @var bool
     */
    public $isUserAloneParticipant;

    /**
     * @var ParticipantView[]
     */
    public $participants;

    /**
     * @param array             $dayViews
     * @param Sheet             $sheet
     * @param Participant       $participant
     * @param bool              $isUserAloneParticipant
     * @param ParticipantView[] $participants
     */
    public function __construct(
        array $dayViews,
        Sheet $sheet,
        Participant $participant,
        $isUserAloneParticipant,
        array $participants
    ) {
        $this->days                   = $dayViews;
        $this->sheet                  = $sheet;
        $this->participant            = $participant;
        $this->isUserAloneParticipant = $isUserAloneParticipant;
        $this->participants           = $participants;
    }

    /**
     * @return int
     */
    public function getNumberOfDays()
    {
        return count($this->days);
    }

    /**
     * @return null|ParticipantView
     */
    public function getCurrentParticipantView()
    {
        foreach ($this->participants as $participantView) {
            if ($participantView->id === $this->participant->getId()) {
                return $participantView;
            }
        }

        return null;
    }

    /**
     * In case of one day, take the fullscreen size
     * If more, display 2 column size by size
     *
     * @return int
     */
    public function getColSize()
    {
        return $this->getNumberOfDays() === 1 ? 12 : 6;
    }

}
