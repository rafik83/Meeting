<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Payment;

class Mode
{
    // Payment mode
    const PAYMENT_BANK_CARD     = 'bank_card';
    const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_BANK_CHECK    = 'bank_check';

    /**
     * Return all the payment modes allowed by the platform
     *
     * @return array
     */
    public static function getPaymentModes()
    {
        return [
            self::PAYMENT_BANK_CARD     => self::PAYMENT_BANK_CARD,
            self::PAYMENT_BANK_TRANSFER => self::PAYMENT_BANK_TRANSFER,
            self::PAYMENT_BANK_CHECK    => self::PAYMENT_BANK_CHECK,
        ];
    }
}
