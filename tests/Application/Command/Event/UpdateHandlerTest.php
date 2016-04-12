<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use Proximum\Vimeet\Application\Command\Event\Update;
use Proximum\Vimeet\Application\Command\Event\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setLeftColor('#111111');
        $event->getConfiguration()->setRightColor('#BBBBBB');
        $event->getConfiguration()->setTextColor('#333333');
        $event->update('foobar', ['fr', 'en'], 'fr', Event::VAT_MODE_ATI, 20);
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));

        // Update command
        $update = new Update($event);
        $update->title        = 'barfoo';
        $update->locales      = ['fr', 'en'];
        $update->fallback     = 'en';
        $update->translations = [
            'fr' => [
                'description' => 'Salut',
            ],
            'en' => [
                'description' => 'Hello',
            ],
        ];
        $update->leftColor  = '#FFFFFF';
        $update->rightColor = '#000000';
        $update->textColor  = '#CCCCCC';

        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setLeftColor('#FFFFFF');
        $expectedEvent->getConfiguration()->setRightColor('#000000');
        $expectedEvent->getConfiguration()->setTextColor('#CCCCCC');
        $expectedEvent->update('barfoo', ['fr', 'en'], 'en', Event::VAT_MODE_ATI, 20);
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal());
        $handler->handle($update);
    }

    public function testHandleAddLocale()
    {
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setLeftColor('#111111');
        $event->getConfiguration()->setRightColor('#BBBBBB');
        $event->getConfiguration()->setTextColor('#333333');
        $event->update('foobar', ['fr', 'en'], 'fr', Event::VAT_MODE_ATI, 20);
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));

        // Update command
        $update = new Update($event);
        $update->title        = 'foobar';
        $update->locales      = ['fr', 'en', 'de'];
        $update->fallback     = 'fr';
        $update->translations = [
            'fr' => [
                'description' => 'Bonjour',
            ],
            'en' => [
                'description' => 'Hello',
            ],
        ];
        $update->leftColor  = '#FFFFFF';
        $update->rightColor = '#000000';
        $update->textColor  = '#CCCCCC';

        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setLeftColor('#FFFFFF');
        $expectedEvent->getConfiguration()->setRightColor('#000000');
        $expectedEvent->getConfiguration()->setTextColor('#CCCCCC');
        $expectedEvent->update('foobar', ['fr', 'en', 'de'], 'fr', Event::VAT_MODE_ATI, 20);
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->getTranslations()->set('de', new EventTranslation($expectedEvent, 'de', ''));

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal());
        $handler->handle($update);
    }

    public function testHandleRemoveLocale()
    {
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setLeftColor('#111111');
        $event->getConfiguration()->setRightColor('#BBBBBB');
        $event->getConfiguration()->setTextColor('#333333');
        $event->update('foobar', ['fr', 'en'], 'fr', Event::VAT_MODE_ATI, 20);
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));

        // Update command
        $update = new Update($event);
        $update->title        = 'foobar';
        $update->locales      = ['fr'];
        $update->fallback     = 'fr';
        $update->translations = [
            'fr' => [
                'description' => 'Bonjour',
            ],
            'en' => [
                'description' => 'Hello',
            ],
        ];
        $update->leftColor  = '#FFFFFF';
        $update->rightColor = '#000000';
        $update->textColor  = '#CCCCCC';

        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setLeftColor('#FFFFFF');
        $expectedEvent->getConfiguration()->setRightColor('#000000');
        $expectedEvent->getConfiguration()->setTextColor('#CCCCCC');
        $expectedEvent->update('foobar', ['fr'], 'fr', Event::VAT_MODE_ATI, 20);
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal());
        $handler->handle($update);
    }
}
