<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting;

class MeetingView
{
    /** @var int */
    public $id;

    /** @var string */
    public $spotReference;

    /** @var int */
    public $fromSheetId;

    /** @var string */
    public $fromSheetTitle;

    /** @var int */
    public $toSheetId;

    /** @var string */
    public $toSheetTitle;

    /** @var string[] */
    public $fromParticipantsName;

    /** @var string[] */
    public $toParticipantsName;

    public function __construct(
        int $id,
        string $spotReference,
        int $fromSheetId,
        string $fromSheetTitle = null,
        array $fromParticipantsName,
        int $toSheetId,
        string $toSheetTitle = null,
        array $toParticipantsName
    ) {
        $this->id = $id;
        $this->spotReference = $spotReference;
        $this->fromSheetId = $fromSheetId;
        $this->fromSheetTitle = $fromSheetTitle;
        $this->toSheetId = $toSheetId;
        $this->toSheetTitle = $toSheetTitle;
        $this->fromParticipantsName = $fromParticipantsName;
        $this->toParticipantsName = $toParticipantsName;
    }
}
