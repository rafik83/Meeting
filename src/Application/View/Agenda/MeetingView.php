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
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;

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

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

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

    /** @var null|VideoConferenceView */
    private $videoConferenceView;

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
     * @param VideoConferenceView|null $videoConferenceView
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
        VideoConferenceView $videoConferenceView = null
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
        $this->videoConferenceView             = $videoConferenceView;
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
     * @return null|VideoConferenceView
     */
    public function getVideoConference(): ?VideoConferenceView
    {
        return $this->videoConferenceView;
    }
}
