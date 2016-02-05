<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class HappeningListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $speakers;

    /**
     * @var bool
     */
    public $canUpdate;

    /**
     * HappeningListView constructor.
     *
     * @param int                $id
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $title
     * @param array              $speakers
     * @param bool               $canUpdate
     */
    public function __construct($id, \DateTimeInterface $begin, \DateTimeInterface $end, $title, array $speakers, $canUpdate)
    {
        $this->id        = $id;
        $this->begin     = $begin;
        $this->end       = $end;
        $this->title     = $title;
        $this->speakers  = $speakers;
        $this->canUpdate = $canUpdate;
    }
}
