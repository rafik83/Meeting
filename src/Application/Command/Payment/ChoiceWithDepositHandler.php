<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Application\Exception\Payment\DepositNotAvailableException;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;

class ChoiceWithDepositHandler extends AbstractChoiceHandler
{
    /**
     * @param ChoiceWithDeposit $choice
     *
     * @throws DepositNotAvailableException
     */
    public function handle(ChoiceWithDeposit $choice)
    {
        $total = $this->totalToPay->getTotal($choice->sheet);

        if ($choice->deposit) {
            $totalDeposit = DepositApplicable::calculateDeposit($choice->sheet->getEvent(), $this->datetime, $total);

            if ($total === $totalDeposit) {
                throw new DepositNotAvailableException();
            }

            $total = $totalDeposit;
        }

        $this->handleChoice($choice, $total);
    }
}
