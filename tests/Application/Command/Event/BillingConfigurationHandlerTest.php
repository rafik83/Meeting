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
        $expectedEvent->getConfiguration()->setBillingConfiguration('FR14-000', 'payment address', 'condition', 'footers', 'legalInfo');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpg');

        // Command
        $billingConfiguration = new BillingConfiguration($event);
        $billingConfiguration->iban             = 'FR14-000';
        $billingConfiguration->billingAddress   = 'payment address';
        $billingConfiguration->paymentCondition = 'condition';
        $billingConfiguration->footers          = 'footers';
        $billingConfiguration->legalInfo        = 'legalInfo';

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->add($expectedEvent)->shouldBeCalled();

        $handler = new BillingConfigurationHandler($eventRepository->reveal());
        $result = $handler->handle($billingConfiguration);

        // Test
        $this->assertEquals($expectedEvent, $result);
    }
}
