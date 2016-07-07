<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Payment;

class ChoiceHandler extends AbstractChoiceHandler
{
    /**
     * @param Choice $choice
     */
    public function handle(Choice $choice)
    {
        $total = $this->totalToPay->getTotal($choice->sheet);

        $this->handleChoice($choice, $total);
    }
}
