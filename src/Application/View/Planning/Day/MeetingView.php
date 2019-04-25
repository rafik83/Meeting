<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planning\Day;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;

class MeetingView extends AbstractTimeEntityView
{
    /** @var string */
    public $spotRef;

    /** @var string */
    public $sheetMetTitle;

    /** @var string */
    public $userSheetTitle;

    /** @var ParticipantMetView[] */
    public $participantsMetViews;

    /** @var bool */
    public $hasParticipantsInfo;

    /** @var Sheet */
    public $userSheet;

    /** @var LinkedSheets|null */
    public $sheetMet;

    /**
     * @param \DateTimeInterface   $begin
     * @param \DateTimeInterface   $end
     * @param string               $spotRef
     * @param string               $userSheetTitle
     * @param string               $sheetMetTitle
     * @param bool                 $hasParticipantsInfo
     * @param ParticipantMetView[] $participantMetViews
     * @param Sheet|null           $userSheet
     * @param Sheet|null           $sheetMet
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $spotRef,
        $userSheetTitle,
        $sheetMetTitle,
        bool $hasParticipantsInfo = false,
        array $participantMetViews = [],
        Sheet $userSheet = null,
        Sheet $sheetMet = null
    ) {
        parent::__construct($begin, $end);

        $this->spotRef = $spotRef;
        $this->userSheetTitle = $userSheetTitle;
        $this->sheetMetTitle = $sheetMetTitle;
        $this->begin = $begin;
        $this->end = $end;
        $this->participantsMetViews = $participantMetViews;
        $this->hasParticipantsInfo = $hasParticipantsInfo;
        $this->userSheet = $userSheet;
        $this->sheetMet = $sheetMet;
    }
}
