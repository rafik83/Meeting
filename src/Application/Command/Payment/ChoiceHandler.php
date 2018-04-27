<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Domain\Model\Transaction;

class ChoiceHandler extends AbstractChoiceHandler
{
    /**
     * @param Choice $choice
     *
     * @return Transaction
     */
    public function handle(Choice $choice)
    {
        $total = $this->totalToPay->getTotal($choice->sheet);

        return $this->handleChoice($choice, $total);
    }
}
