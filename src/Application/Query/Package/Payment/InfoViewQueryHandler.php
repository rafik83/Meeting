<?php

namespace Proximum\Vimeet\Application\Query\Package\Payment;

use Proximum\Vimeet\Application\View\Package\Payment\InfoView;
use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;

class InfoViewQueryHandler
{
    public function handle(InfoViewQuery $query): InfoView
    {
        $event = $query->sheet->getEvent();
        $paymentConditions = $query->sheet->getType()->getPaymentConditions();

        if ($paymentConditions instanceof PaymentConditions) {
            return new InfoView(
                $event->getOrganiserName(),
                $paymentConditions->getBillingAddress($query->locale),
                $event->getOrganiserEmail(),
                $paymentConditions->getBankInfo($query->locale),
                $paymentConditions->getPaymentCondition($query->locale)
            );
        }

        return new InfoView(
            $event->getOrganiserName(),
            $event->getBillingAddress($query->locale),
            $event->getOrganiserEmail(),
            $event->getBankInfo($query->locale),
            $event->getPaymentCondition($query->locale)
        );
    }
}
