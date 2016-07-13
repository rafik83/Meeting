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
        $billingConfiguration
            ->event
            ->getConfiguration()
            ->setLegalInfo($billingConfiguration->legalInfo);

        foreach ($billingConfiguration->event->getLocales() as $locale) {
            if (!$billingConfiguration->event->getTranslations()->get($locale)) {
                $eventTranslation = new EventTranslation($billingConfiguration->event, $locale, '');

                $billingConfiguration->event->getTranslations()->set($locale, $eventTranslation);
            }
        }

        foreach ($billingConfiguration->translations as $locale => $translation) {
            /** @var EventTranslation $eventTranslation */
            $eventTranslation = $billingConfiguration->event->getTranslations()->get($locale);

            $eventTranslation->setBillingConfiguration(
                $billingConfiguration->translations[$locale]['bankInfo'],
                $billingConfiguration->translations[$locale]['billingAddress'],
                $billingConfiguration->translations[$locale]['paymentCondition'],
                $billingConfiguration->translations[$locale]['paymentFooter']
            );
        }

        $this->eventRepository->add($billingConfiguration->event);

        return $billingConfiguration->event;
    }
}
