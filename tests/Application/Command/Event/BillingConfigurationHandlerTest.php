<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\BillingConfiguration;
use Proximum\Vimeet\Application\Command\Event\BillingConfigurationHandler;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BillingConfigurationHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();

        $event->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', ''));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', ''));
        $event->setLogo('toto.jpg', 'jpg');
        $event->setInvoiceLogo('toto.jpg', 'jpg');

        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr',
            new EventTranslation($expectedEvent, 'fr', '', 'FR14-000', 'billing address', 'condition', 'footers'));
        $expectedEvent->getTranslations()->set('en',
            new EventTranslation($expectedEvent, 'en', '', 'FR14-000', 'billing address', 'condition', 'footers'));
        $expectedEvent->setLogo('toto.jpg', 'jpg');
        $expectedEvent->setInvoiceLogo('toto.jpg', 'jpg');

        // Command
        $billingConfiguration               = new BillingConfiguration($event);
        $billingConfiguration->translations = [
            'fr' => [
                'bankInfo'         => 'FR14-000',
                'billingAddress'   => 'billing address',
                'paymentCondition' => 'condition',
                'paymentFooter'    => 'footers',
            ],
            'en' => [
                'bankInfo'         => 'FR14-000',
                'billingAddress'   => 'billing address',
                'paymentCondition' => 'condition',
                'paymentFooter'    => 'footers',
            ],
        ];

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->add($expectedEvent)->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $handler = new BillingConfigurationHandler($eventRepository->reveal(), $fileStorage->reveal());
        $handler->handle($billingConfiguration);
    }
}
