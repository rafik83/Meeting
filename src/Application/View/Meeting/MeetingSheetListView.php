<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class MeetingSheetListView
{
    /**
     * @var MeetingSheetView[]
     */
    public $sheets;

    /**
     * @var string
     */
    public $eventName;

    /**
     * MeetingSheetListView constructor.
     *
     * @param MeetingSheetView[] $sheets
     * @param string             $eventName
     */
    public function __construct(array $sheets, $eventName)
    {
        $this->sheets    = $sheets;
        $this->eventName = $eventName;
    }
}
