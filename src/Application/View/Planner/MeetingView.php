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

    /**
     * @param int               $id
     * @param SheetView[]       $sheetList
     * @param ParticipantView[] $participantList
     */
    public function __construct($id, array $sheetList, array $participantList)
    {
        $this->id              = $id;
        $this->sheetList       = $sheetList;
        $this->participantList = $participantList;
    }
}
