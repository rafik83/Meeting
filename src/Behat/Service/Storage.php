<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class Storage
{
    /** @var null|Event */
    private $lastEvent;

    /** @var null|Sheet */
    private $lastSheet;

    /**
     * @return null|Event
     */
    public function getLastEvent()
    {
        return $this->lastEvent;
    }

    /**
     * @param Event $event
     */
    public function setLastEvent(Event $event)
    {
        $this->lastEvent = $event;
    }

    /**
     * @return null|Sheet
     */
    public function getLastSheet()
    {
        return $this->lastSheet;
    }

    /**
     * @param Sheet $sheet
     */
    public function setLastSheet(Sheet $sheet)
    {
        $this->lastSheet = $sheet;
    }
}
