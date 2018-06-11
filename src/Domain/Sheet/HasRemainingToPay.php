<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;

final class HasRemainingToPay
{
    /** @var Balance */
    private $balance;

    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        if (!$sheet->getPackage()->isPassable()) {
            return true;
        }

        if (!$sheet->hasNotCancelledOrders()) {
            return false;
        }

        return 0 === $this->balance->getRemainingToPay($sheet);
    }
}
