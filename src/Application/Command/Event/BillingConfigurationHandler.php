<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class BillingConfigurationHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param BillingConfiguration $billingConfiguration
     */
    public function handle(BillingConfiguration $billingConfiguration)
    {
        $billingConfiguration->event->getConfiguration()->setIban($billingConfiguration->iban);
        $billingConfiguration->event->getConfiguration()->setBillingAddress($billingConfiguration->billingAddress);
        $billingConfiguration->event->getConfiguration()->setPaymentCondition($billingConfiguration->paymentCondition);
        $billingConfiguration->event->getConfiguration()->setLegalInfo($billingConfiguration->legalInfo);
        $billingConfiguration->event->getConfiguration()->setFooters($billingConfiguration->footers);

        $this->eventRepository->add($billingConfiguration->event);
    }
}
