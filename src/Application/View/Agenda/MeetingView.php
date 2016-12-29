<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;

class MeetingView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var Sheet
     */
    public $sheetMet;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $isNoPreference;

    /**
     * MeetingView constructor.
     *
     * @param Spot               $spot
     * @param Sheet              $sheetMet
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param int                $id
     * @param bool               $isNoPreference
     */
    public function __construct(
        Spot $spot,
        Sheet $sheetMet,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $id,
        $isNoPreference
    ) {
        $this->spot           = $spot;
        $this->begin          = $begin;
        $this->end            = $end;
        $this->id             = $id;
        $this->sheetMet       = $sheetMet;
        $this->isNoPreference = $isNoPreference;
    }
}
