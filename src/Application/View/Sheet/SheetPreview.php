<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

use Proximum\Vimeet\Application\Components\Sheet\Block\BlockDataView;

class SheetPreview
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $typeTitle;

    /**
     * @var ParticipantDataView[]
     */
    public $participants;

    /**
     * @var int
     */
    public $remainingParticipants;

    /**
     * @var BlockDataView[]
     */
    public $blocks;

    /**
     * @var int
     */
    public $orders;

    /**
     * @var string
     */
    public $step;

    /**
     * SheetPreview constructor.
     *
     * @param string                $locale
     * @param int                   $id
     * @param string                $typeTitle
     * @param ParticipantDataView[] $participants
     * @param int                   $remainingParticipants
     * @param BlockDataView[]       $blocks
     * @param int                   $orders
     * @param string                $step
     */
    public function __construct($locale, $id, $typeTitle, array $participants, $remainingParticipants, array $blocks, $orders, $step)
    {
        $this->locale                = $locale;
        $this->id                    = $id;
        $this->typeTitle             = $typeTitle;
        $this->participants          = $participants;
        $this->remainingParticipants = $remainingParticipants;
        $this->blocks                = $blocks;
        $this->orders                = $orders;
        $this->step                  = $step;
    }
}
