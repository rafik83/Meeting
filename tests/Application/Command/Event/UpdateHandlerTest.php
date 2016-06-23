<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Update;
use Proximum\Vimeet\Application\Command\Event\UpdateHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setColors('#111111', '#BBBBBB', '#333333');
        $event->update(
            'foobar',
            ['fr', 'en'],
            'fr',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Madrid',
            'old.vimeet.proximum.dev'
        );
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));
        $event->setLogo('here.jpg');

        // Update command
        $update               = new Update($event);
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
        $update->currency     = 'USD';
        $update->leftColor    = '#FFFFFF';
        $update->rightColor   = '#000000';
        $update->textColor    = '#CCCCCC';
        $update->logo         = 'shouldBeUploadFile';
        $update->domain       = 'hello.vimeet.proximum.dev';
        $update->timeZone     = 'Europe/Paris';

        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->update(
            'barfoo',
            ['fr', 'en'],
            'en',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum.dev'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->setLogo('toto.jpg');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->remove('here.jpg')->shouldBeCalled();
        $fileStorage->upload('shouldBeUploadFile')->shouldBeCalled()->willReturn('toto.jpg');

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal(), $guidelineGenerator->reveal(), $fileStorage->reveal());
        $handler->handle($update);
    }

    public function testHandleAddLocale()
    {
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setColors('#111111', '#BBBBBB', '#333333');
        $event->update(
            'foobar',
            ['fr', 'en'],
            'fr',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Madrid',
            'old.vimeet.proximum.dev'
        );
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));

        // Update command
        $update               = new Update($event);
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
        $update->currency     = 'EUR';
        $update->leftColor    = '#FFFFFF';
        $update->rightColor   = '#000000';
        $update->textColor    = '#CCCCCC';
        $update->domain       = 'hello.vimeet.proximum.dev';
        $update->timeZone     = 'Europe/Paris';

        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->update(
            'foobar',
            ['fr', 'en', 'de'],
            'fr',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Paris',
            'hello.vimeet.proximum.dev'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->getTranslations()->set('de', new EventTranslation($expectedEvent, 'de', ''));

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal(), $guidelineGenerator->reveal(), $fileStorage->reveal());
        $handler->handle($update);
    }

    public function testHandleRemoveLocale()
    {
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $event->update(
            'foobar',
            ['fr', 'en'],
            'fr',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Madrid',
            'old.vimeet.proximum.dev'
        );
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));

        // Update command
        $update               = new Update($event);
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
        $update->currency     = 'EUR';
        $update->leftColor    = '#FFFFFF';
        $update->rightColor   = '#000000';
        $update->textColor    = '#CCCCCC';
        $update->domain       = 'hello.vimeet.proximum.dev';
        $update->timeZone     = 'Europe/Paris';


        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->update(
            'foobar',
            ['fr'],
            'fr',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Paris',
            'hello.vimeet.proximum.dev'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldNotBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal(), $guidelineGenerator->reveal(), $fileStorage->reveal());
        $handler->handle($update);
    }

    public function testHandleWithAlreadyUsedDomain()
    {
        $this->expectException(DomainAlreadyUsedException::class);
        // Actual event
        $event = new Event();
        $event->getConfiguration()->setColors('#111111', '#BBBBBB', '#333333');
        $event->update(
            'foobar',
            ['fr', 'en'],
            'fr',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Madrid',
            'old.vimeet.proximum.dev'
        );
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));
        $event->setLogo('here.jpg');

        // Update command
        $update               = new Update($event);
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
        $update->currency     = 'USD';
        $update->leftColor    = '#FFFFFF';
        $update->rightColor   = '#000000';
        $update->textColor    = '#CCCCCC';
        $update->logo         = 'shouldBeUploadFile';
        $update->domain       = 'hello.vimeet.proximum.dev';
        $update->timeZone     = 'Europe/Paris';

        // Expected event
        $expectedEvent = new Event();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->update(
            'barfoo',
            ['fr', 'en'],
            'en',
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum.dev'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->setLogo('toto.jpg');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldNotBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(new Event());
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldNotBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->remove('here.jpg')->shouldNotBeCalled();
        $fileStorage->upload('shouldBeUploadFile')->shouldNotBeCalled();

        // Handle
        $handler = new UpdateHandler($eventRepository->reveal(), $guidelineGenerator->reveal(), $fileStorage->reveal());
        $handler->handle($update);
    }
}
