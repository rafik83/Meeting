<?php

namespace Proximum\Vimeet\Application\Query\Payment;

use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;
use Proximum\Vimeet\Domain\Payment\PaymentConditionsView;

class PaymentConditionsViewQueryHandler
{
    /**
     * @param PaymentConditionsViewQuery $query
     *
     * @return PaymentConditionsView
     */
    public function handle(PaymentConditionsViewQuery $query): PaymentConditionsView
    {
        $typePaymentConditions = $query->sheet->getType()->getPaymentConditions();

        if ($typePaymentConditions instanceof PaymentConditions) {
            return new PaymentConditionsView(
                $typePaymentConditions->getPaymentModes(),
                $typePaymentConditions->isAllowDeposit(),
                $typePaymentConditions->getDepositUntil(),
                $typePaymentConditions->getMinimumForDeposit(),
                $typePaymentConditions->getDeposit()
            );
        }

        $event = $query->sheet->getEvent();

        return new PaymentConditionsView(
            $event->getConfiguration()->getPaymentModes(),
            $event->getConfiguration()->isAllowDeposit(),
            $event->getConfiguration()->getDepositUntil(),
            $event->getConfiguration()->getMinimumForDeposit(),
            $event->getConfiguration()->getDeposit()
        );
    }
}
