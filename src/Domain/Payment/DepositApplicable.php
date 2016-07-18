<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Payment;

use Proximum\Vimeet\Domain\Model\Event;

class DepositApplicable
{
    /**
     * @param Event              $event
     * @param \DateTimeInterface $now
     * @param float              $total
     *
     * @return bool
     */
    public static function isApplicable(Event $event, \DateTimeInterface $now, $total)
    {
        return $event->getConfiguration()->isDepositAllowed()
        && $now < $event->getConfiguration()->getDepositUntil()
        && $total > $event->getConfiguration()->getMinimumForDeposit();
    }

    /**
     * @param Event              $event
     * @param \DateTimeInterface $now
     * @param float              $total
     *
     * @return float
     */
    public static function calculateDeposit(Event $event, \DateTimeInterface $now, $total)
    {
        if (!self::isApplicable($event, $now, $total)) {
            return $total;
        }

        $pourcentageOfDeposit = $event->getConfiguration()->getDeposit();

        return ceil(($total * $pourcentageOfDeposit) / 100);
    }
}
