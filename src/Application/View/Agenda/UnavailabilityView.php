<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class UnavailabilityView extends AbstractTimeEntityView
{
    /** @var int */
    public $id;

    /** @var string */
    public $timeZone;

    /** @var null|string */
    public $message;

    /**
     * @param int                $id
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $timeZone
     * @param string|null        $message
     */
    public function __construct(
        $id,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $timeZone,
        $message = null
    ) {
        $this->id       = $id;
        $this->begin    = $begin;
        $this->end      = $end;
        $this->timeZone = $timeZone;
        $this->message  = $message;
    }

    /**
     * @return bool
     */
    public function hasMessage(): bool
    {
        return $this->message !== null;
    }
}
