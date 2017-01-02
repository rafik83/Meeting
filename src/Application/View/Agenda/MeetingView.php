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
     * @var bool
     */
    public $hasNoPreference;

    /**
     * MeetingView constructor.
     *
     * @param Spot               $spot
     * @param Sheet              $sheetMet
     * @param int                $id
     * @param bool               $hasNoPreference
     */
    public function __construct(
        Spot $spot,
        Sheet $sheetMet,
        $id,
        $hasNoPreference
    ) {
        $this->spot           = $spot;
        $this->id             = $id;
        $this->sheetMet       = $sheetMet;
        $this->hasNoPreference = $hasNoPreference;
    }
}
