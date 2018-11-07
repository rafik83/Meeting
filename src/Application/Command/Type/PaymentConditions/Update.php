<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type\PaymentConditions;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Type;

class Update implements Command
{
    /** @var Type */
    public $type;

    /** @var bool */
    public $specificPaymentConditions;

    /** @var bool */
    public $allowDeposit = false;

    /** @var \DateTimeInterface|null */
    public $depositUntil;

    /** @var float|null */
    public $minimumForDeposit;

    /** @var int|null */
    public $deposit;

    /** @var array */
    public $paymentModes = [];

    /** @var array */
    public $translations = [];

    public function __construct(Type $type)
    {
        $this->type = $type;
        $event = $type->getEvent();
        $paymentConditions = $type->getPaymentConditions();

        $this->specificPaymentConditions = $paymentConditions instanceof Type\PaymentConditions;

        if ($paymentConditions instanceof Type\PaymentConditions) {
            $this->allowDeposit      = $paymentConditions->isAllowDeposit();
            $this->depositUntil      = $paymentConditions->getDepositUntil();
            $this->minimumForDeposit = $paymentConditions->getMinimumForDeposit();
            $this->deposit           = $paymentConditions->getDeposit();
            $this->paymentModes      = $paymentConditions->getPaymentModes();

            /** @var Type\PaymentConditionsTranslation $translation */
            foreach ($paymentConditions->getTranslations() as $translation) {
                $this->translations[$translation->getLocale()] = [
                    'bankInfo' => $translation->getBankInfo(),
                    'billingAddress' => $translation->getBillingAddress(),
                    'paymentCondition' => $translation->getPaymentCondition(),
                    'paymentFooter' => $translation->getPaymentFooter(),
                ];
            }
        }

        // Pre-populate with event data
        if (!$paymentConditions instanceof Type\PaymentConditions) {
            foreach ($event->getLocales() as $locale) {
                $this->translations[$locale] = [
                    'bankInfo' => $event->getBankInfo($locale),
                    'billingAddress' => $event->getBillingAddress($locale),
                    'paymentCondition' => $event->getPaymentCondition($locale),
                    'paymentFooter' => $event->getPaymentFooter($locale),
                ];
            }
        }
    }

    public function isPaymentModesNotEmpty(): bool
    {
        return !empty($this->paymentModes);
    }
}
