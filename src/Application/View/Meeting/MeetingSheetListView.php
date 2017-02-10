<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
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
