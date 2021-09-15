<?php

namespace Proximum\Vimeet\Domain\Payment;

use Proximum\Vimeet\Domain\Payment\Mode;

/**
 * User can pay online if at least one payment platform is available for online payment
 */
class CanPayOnline
{
    public function isSatisfiedBy(array $paymentModes): bool
    {
        foreach ($paymentModes as $paymentMode) {
            if ($paymentMode === Mode::PAYMENT_PAYPAL) {
                return true;
            }
            if ($paymentMode === Mode::PAYMENT_CCIP) {
                return true;
            }
        }

        return false;
    }
}
