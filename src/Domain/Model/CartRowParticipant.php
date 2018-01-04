<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class CartRowParticipant
{
    /** @var CartRow */
    private $cartRow;

    /** @var Participant */
    private $participant;

    public function __construct(CartRow $cartRow, Participant $participant)
    {
        $this->cartRow = $cartRow;
        $this->participant = $participant;
    }
}
