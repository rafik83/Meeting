<?php

namespace Proximum\Vimeet\Domain\Payment;

class DepositApplicable
{
    /**
     * @param PaymentConditionsView $paymentConditionsView
     * @param \DateTimeInterface    $now
     * @param float                 $total
     *
     * @return bool
     */
    public static function isApplicable(PaymentConditionsView $paymentConditionsView, \DateTimeInterface $now, $total)
    {
        return $paymentConditionsView->allowDeposit
            && $now < $paymentConditionsView->depositUntil
            && $total > $paymentConditionsView->minimumForDeposit
        ;
    }

    /**
     * @param PaymentConditionsView $paymentConditionsView
     * @param \DateTimeInterface    $now
     * @param float                 $total
     *
     * @return float
     */
    public static function calculateDeposit(PaymentConditionsView $paymentConditionsView, \DateTimeInterface $now, $total)
    {
        if (!self::isApplicable($paymentConditionsView, $now, $total)) {
            return $total;
        }

        $percentageOfDeposit = $paymentConditionsView->deposit;

        return ceil(($total * $percentageOfDeposit) / 100);
    }
}
