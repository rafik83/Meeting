<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class UnavailabilityView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $code;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @param int                $id
     * @param string             $code
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function __construct(
        $id,
        $code,
        \DateTimeInterface $begin,
        \DateTimeInterface $end
    ) {
        $this->id    = $id;
        $this->code  = $code;
        $this->begin = $begin;
        $this->end   = $end;
    }
}
