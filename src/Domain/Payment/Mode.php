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

    // Payment mode for deposit
    const PAYMENT_DEPOSIT_BANK_CARD     = 'deposit_bank_card';
    const PAYMENT_DEPOSIT_BANK_TRANSFER = 'deposit_bank_transfer';
    const PAYMENT_DEPOSIT_BANK_CHECK    = 'deposit_bank_check';

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

    /**
     * Return all the payment modes allowed as a deposit by the platform
     *
     * @return array
     */
    public static function getDepositPaymentModes()
    {
        return [
            self::PAYMENT_DEPOSIT_BANK_CARD     => self::PAYMENT_DEPOSIT_BANK_CARD,
            self::PAYMENT_DEPOSIT_BANK_TRANSFER => self::PAYMENT_DEPOSIT_BANK_TRANSFER,
            self::PAYMENT_DEPOSIT_BANK_CHECK    => self::PAYMENT_DEPOSIT_BANK_CHECK,
        ];
    }

    /**
     * Return all the payment and deposit modes allowed by the platform
     *
     * @return array
     */
    public static function getAllPaymentModes()
    {
        return array_merge(self::getPaymentModes(), self::getDepositPaymentModes());
    }

    /**
     * @param $mode
     *
     * @return bool
     */
    public static function isDeposit($mode)
    {
        return in_array($mode, self::getDepositPaymentModes());
    }
}
