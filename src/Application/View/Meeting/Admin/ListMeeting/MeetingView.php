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

    /** @var ParticipantView[] */
    public $fromParticipants;

    /** @var ParticipantView[] */
    public $toParticipants;

    public function __construct(
        int $id,
        string $spotReference,
        int $fromSheetId,
        ?string $fromSheetTitle = null,
        array $fromParticipants,
        int $toSheetId,
        ?string $toSheetTitle = null,
        array $toParticipants
    ) {
        $this->id = $id;
        $this->spotReference = $spotReference;
        $this->fromSheetId = $fromSheetId;
        $this->fromSheetTitle = $fromSheetTitle;
        $this->toSheetId = $toSheetId;
        $this->toSheetTitle = $toSheetTitle;
        $this->fromParticipants = $fromParticipants;
        $this->toParticipants = $toParticipants;
    }
}
