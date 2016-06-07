<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantsView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var int
     */
    public $quantityMax;

    /**
     * @var int
     */
    public $numberIncluded;

    /**
     * @var ParticipantView[]
     */
    public $participants;

    /**
     * @param string $title
     * @param string $description
     * @param float  $unitPrice
     * @param string $vatMode
     * @param int    $quantityMax
     * @param int    $numberIncluded
     * @param array  $participants
     */
    public function __construct(
        $title,
        $description,
        $unitPrice,
        $vatMode,
        $quantityMax,
        $numberIncluded,
        array $participants
    ) {
        $this->title          = $title;
        $this->description    = $description;
        $this->unitPrice      = $unitPrice;
        $this->vatMode        = $vatMode;
        $this->quantityMax    = $quantityMax;
        $this->numberIncluded = $numberIncluded;
        $this->participants   = $participants;
    }
}
