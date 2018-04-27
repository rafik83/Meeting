<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;

class MeetingView extends AbstractTimeEntityView
{
    /** @var int */
    public $id;

    /** @var string */
    public $userSheetTitle;

    /** @var int */
    public $sheetMetId;

    /** @var string */
    public $spotRef;

    /** @var MeetingParticipantView[] */
    public $participants;

    /** @var string */
    public $sheetMetTitle;

    /** @var string */
    public $timeZone;

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;

    /** @var bool */
    public $isSheetDetailsSeeAble;

    /** @var bool */
    public $isUserParticipantMultipleSheets;

    /** @var bool */
    private $isVisio;

    /** @var bool */
    private $isVisioAvailable;

    /**
     * @param int                      $id
     * @param string                   $userSheetTitle
     * @param int                      $sheetMetId
     * @param string                   $sheetMetTitle
     * @param \DateTimeInterface       $begin
     * @param \DateTimeInterface       $end
     * @param string                   $spotRef
     * @param string                   $timeZone
     * @param string                   $leftColor
     * @param string                   $rightColor
     * @param MeetingParticipantView[] $participants
     * @param bool                     $isSheetDetailsSeeAble
     * @param bool                     $isUserParticipantMultipleSheets
     * @param bool                     $isVisio
     * @param bool                     $isVisioAvailable
     */
    public function __construct(
        int $id,
        $userSheetTitle,
        $sheetMetId,
        $sheetMetTitle,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $spotRef,
        $timeZone,
        $leftColor,
        $rightColor,
        array $participants,
        $isSheetDetailsSeeAble = false,
        $isUserParticipantMultipleSheets = false,
        bool $isVisio = false,
        bool $isVisioAvailable = false
    ) {
        $this->id                              = $id;
        $this->userSheetTitle                  = $userSheetTitle;
        $this->sheetMetId                      = $sheetMetId;
        $this->sheetMetTitle                   = $sheetMetTitle;
        $this->spotRef                         = $spotRef;
        $this->begin                           = $begin;
        $this->end                             = $end;
        $this->timeZone                        = $timeZone;
        $this->leftColor                       = $leftColor;
        $this->rightColor                      = $rightColor;
        $this->participants                    = $participants;
        $this->isSheetDetailsSeeAble           = $isSheetDetailsSeeAble;
        $this->isUserParticipantMultipleSheets = $isUserParticipantMultipleSheets;
        $this->isVisio                         = $isVisio;
        $this->isVisioAvailable                = $isVisioAvailable;
    }

    /**
     * @return \DateInterval
     */
    public function getDuration(): \DateInterval
    {
        return $this->end->diff($this->begin);
    }

    /**
     * @return bool
     */
    public function isVisio(): bool
    {
        return $this->isVisio;
    }

    /**
     * @return bool
     */
    public function isVisioAvailable(): bool
    {
        return $this->isVisioAvailable;
    }

    /**
     * @return bool
     */
    public function isVisioAndAvailable(): bool
    {
        return $this->isVisio && $this->isVisioAvailable;
    }
}
