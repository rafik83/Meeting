<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\EventTranslation;
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
     *
     * @return \Proximum\Vimeet\Domain\Model\Event
     */
    public function handle(BillingConfiguration $billingConfiguration)
    {
        $billingConfiguration->event->getConfiguration()->setBillingConfiguration(
            $billingConfiguration->iban,
            $billingConfiguration->billingAddress,
            $billingConfiguration->paymentCondition,
            $billingConfiguration->footers,
            $billingConfiguration->legalInfo
        );

        foreach ($billingConfiguration->event->getLocales() as $locale) {
            if (!$billingConfiguration->event->getTranslations()->get($locale)) {
                $eventTranslation = new EventTranslation($billingConfiguration->event, $locale, '');

                $billingConfiguration->event->getTranslations()->set($locale, $eventTranslation);
            }
        }

        $this->eventRepository->add($billingConfiguration->event);

        return $billingConfiguration->event;
    }
}
