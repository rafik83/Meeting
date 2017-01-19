<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;

class MeetingView extends AbstractTimeEntityView
{
    /**
     * @var int
     */
    public $sheetMetId;

    /**
     * @var string
     */
    public $spotRef;

    /**
     * @var MeetingParticipantView[]
     */
    public $participants;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var string
     */
    public $sheetMetTitle;

    /**
     * @var string
     */
    public $timeZone;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * @param int                      $sheetMetId
     * @param string                   $sheetMetTitle
     * @param \DateTimeInterface       $begin
     * @param \DateTimeInterface       $end
     * @param string                   $spotRef
     * @param string                   $timeZone
     * @param string                   $leftColor
     * @param string                   $rightColor
     * @param MeetingParticipantView[] $participants
     */
    public function __construct(
        $sheetMetId,
        $sheetMetTitle,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $spotRef,
        $timeZone,
        $leftColor,
        $rightColor,
        array $participants
    ) {
        $this->sheetMetId    = $sheetMetId;
        $this->sheetMetTitle = $sheetMetTitle;
        $this->spotRef       = $spotRef;
        $this->begin         = $begin;
        $this->end           = $end;
        $this->timeZone      = $timeZone;
        $this->leftColor     = $leftColor;
        $this->rightColor    = $rightColor;
        $this->participants  = $participants;
    }

    /**
     * @return \DateInterval
     */
    public function getDuration()
    {
        return $this->end->diff($this->begin);
    }
}
