<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class DayView
{
    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var int
     */
    public $scale;

    /**
     * @var HappeningView[]
     */
    public $happenings;

    /**
     * @var UnavailabilityView[]
     */
    public $unavailabilities;

    /**
     * @param \DateTimeInterface   $begin
     * @param \DateTimeInterface   $end
     * @param int                  $scale
     * @param HappeningView[]      $happenings
     * @param UnavailabilityView[] $unavailabilities
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $scale,
        array $happenings,
        array $unavailabilities
    ) {
        $this->begin            = $begin;
        $this->end              = $end;
        $this->scale            = $scale;
        $this->happenings       = $happenings;
        $this->unavailabilities = $unavailabilities;
    }
}
