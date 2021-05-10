<?php

namespace Proximum\Vimeet\Domain\Payment;

class Mode
{
    const PAYMENT_PAYPAL        = 'paypal';
    const PAYMENT_BANK_CARD     = 'bank_card';
    const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_BANK_CHECK    = 'bank_check';
    const PAYMENT_BANK_CASH     = 'bank_cash';
    const PAYMENT_CCIP          = 'ccip';

    /**
     * Return all the payment modes allowed on front
     *
     * @return array
     */
    public static function getPaymentModes()
    {
        return [
            self::PAYMENT_PAYPAL        => self::PAYMENT_PAYPAL,
            self::PAYMENT_BANK_TRANSFER => self::PAYMENT_BANK_TRANSFER,
            self::PAYMENT_BANK_CHECK    => self::PAYMENT_BANK_CHECK,
            self::PAYMENT_CCIP          => self::PAYMENT_CCIP,
        ];
    }

    /**
     * @return array
     */
    public static function getModeThatRequiredPaymentInfo()
    {
        return [
            self::PAYMENT_BANK_TRANSFER => self::PAYMENT_BANK_TRANSFER,
            self::PAYMENT_BANK_CHECK    => self::PAYMENT_BANK_CHECK,
        ];
    }

    /**
     * @return array
     */
    public static function getTransactionModes()
    {
        return [
            self::PAYMENT_PAYPAL        => self::PAYMENT_PAYPAL,
            self::PAYMENT_BANK_CARD     => self::PAYMENT_BANK_CARD,
            self::PAYMENT_BANK_TRANSFER => self::PAYMENT_BANK_TRANSFER,
            self::PAYMENT_BANK_CHECK    => self::PAYMENT_BANK_CHECK,
            self::PAYMENT_BANK_CASH     => self::PAYMENT_BANK_CASH,
            self::PAYMENT_CCIP          => self::PAYMENT_CCIP,
        ];
    }
}
