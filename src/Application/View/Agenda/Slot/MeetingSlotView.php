<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;

class MeetingSlotView extends AbstractSlotView
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
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param int                $id
     * @param bool               $hasNoPreference
     */
    public function __construct(
        Spot $spot,
        Sheet $sheetMet,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $id,
        $hasNoPreference
    ) {
        parent::__construct($begin, $end);

        $this->spot            = $spot;
        $this->id              = $id;
        $this->sheetMet        = $sheetMet;
        $this->hasNoPreference = $hasNoPreference;
    }
}
