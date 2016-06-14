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
     * @var \DateTimeInterface
     */
    private $begin;

    /**
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * @var boolean
     */
    private $enabled = true;

    /**
     * MeetingSlot constructor.
     *
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $enabled
     */
    public function __construct(Event $event, \DateTimeInterface $begin, \DateTimeInterface $end, $enabled = true)
    {
        $this->event   = $event;
        $this->begin   = $begin;
        $this->end     = $end;
        $this->enabled = $enabled;
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
     * Get begin.
     *
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
