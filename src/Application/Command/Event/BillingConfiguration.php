<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class BillingConfiguration
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $legalInfo;

    /**
     * @var array
     */
    public $ibanTranslations = [];

    /**
     * @var array
     */
    public $billingAddressTranslations = [];

    /**
     * @var array
     */
    public $paymentConditionTranslations = [];

    /**
     * @var array
     */
    public $footerTranslations = [];

    /**
     * BillingConfiguration constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        $this->legalInfo = $event->getConfiguration()->getLegalInfo();

        foreach ($event->getLocales() as $locale) {
            $this->ibanTranslations[$locale] = [
                'iban' => null,
            ];

            $this->billingAddressTranslations[$locale] = [
                'address' => null,
            ];

            $this->paymentConditionTranslations[$locale] = [
                'paymentCondition' => null,
            ];

            $this->footerTranslations[$locale] = [
                'footer' => null,
            ];
        }
    }
}
