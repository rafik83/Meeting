<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class MeetingSlot
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * MeetingSlot constructor.
     *
     * @param Event     $event
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function __construct(Event $event, \DateTime $begin, \DateTime $end)
    {
        $this->event    = $event;
        $this->begin    = $begin;
        $this->end      = $end;
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Get begin.
     *
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }
}
