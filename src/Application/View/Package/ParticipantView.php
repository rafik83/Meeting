<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

use Proximum\Vimeet\Application\View\Participant;

class ParticipantView
{
    /**
     * @var Participant\CardView
     */
    public $card;

    /**
     * @var int
     */
    public $id;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var bool
     */
    public $included;

    /**
     * @param int                  $id
     * @param Participant\CardView $card
     * @param float                $price
     * @param string               $vatMode
     * @param bool                 $included
     */
    public function __construct($id, Participant\CardView $card, $price, $vatMode, $included)
    {
        $this->id       = $id;
        $this->card     = $card;
        $this->price    = $price;
        $this->vatMode  = $vatMode;
        $this->included = $included;
    }
}
