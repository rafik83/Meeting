<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner;

class MeetingView
{
    /** @var int */
    public $id;

    /** @var SheetView[] */
    public $sheetList;

    /** @var ParticipantView[] */
    public $participantList;

    /** @var string */
    public $reference;

    /** @var bool */
    public $isVisio;

    /**
     * @param int               $id
     * @param SheetView[]       $sheetList
     * @param ParticipantView[] $participantList
     * @param bool              $isVisio
     */
    public function __construct($id, array $sheetList, array $participantList, $isVisio = false)
    {
        $this->id              = $id;
        $this->sheetList       = $sheetList;
        $this->participantList = $participantList;
        $this->isVisio         = $isVisio;
        $this->reference       = sprintf('meeting%s', $id);
    }
}
