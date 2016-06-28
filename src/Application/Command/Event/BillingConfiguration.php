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
    public $translations = [];


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
            $this->translations[$locale] = [
                'iban'             => $event->getIban($locale),
                'billingAddress'   => $event->getBillingAddress($locale),
                'paymentCondition' => $event->getPaymentCondition($locale),
                'footer'           => $event->getFooter($locale),
            ];
        }
    }
}
