<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Event;

use Proximum\Vimeet\Application\Command\Event\BillingConfiguration;
use Proximum\Vimeet\Application\Command\Event\BillingConfigurationHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class BillingConfigurationHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Data
        $event = new Event(
            'barfoo',
            'en',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum.dev'
        );

        $event->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', ''));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', ''));
        $event->setLogo('toto.jpg');

        $expectedEvent = new Event(
            'barfoo',
            'en',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum.dev'
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', '', 'FR14-000', 'billing address', 'condition', 'footers'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', '', 'FR14-000', 'billing address', 'condition', 'footers'));
        $expectedEvent->setLogo('toto.jpg');

        // Command
        $billingConfiguration = new BillingConfiguration($event);
        $billingConfiguration->translations = [
            'fr' => [
                'iban'             => 'FR14-000',
                'billingAddress'   => 'billing address',
                'paymentCondition' => 'condition',
                'footer'           => 'footers',
            ],
            'en' => [
                'iban'             => 'FR14-000',
                'billingAddress'   => 'billing address',
                'paymentCondition' => 'condition',
                'footer'           => 'footers',
            ]
        ];

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->add($expectedEvent)->shouldBeCalled();

        $handler = new BillingConfigurationHandler($eventRepository->reveal());
        $handler->handle($billingConfiguration);
    }
}
