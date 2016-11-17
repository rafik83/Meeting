<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Update;
use Proximum\Vimeet\Application\Command\Event\UpdateHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Event\Event\LocaleChangedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Actual event
        $event = EventFactory::createEvent();
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
            'old.vimeet.proximum.dev',
            'oldProximum',
            'team-project@example.net'
        );
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));
        $event->setLogo('here.jpeg', 'jpeg');

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
        $update->currency      = 'USD';
        $update->leftColor     = '#FFFFFF';
        $update->rightColor    = '#000000';
        $update->textColor     = '#CCCCCC';
        $update->logo          = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            'hello.vimeet.proximum.dev',
            'proximum',
            'team-event@example.net'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->remove('here.jpeg')->shouldBeCalled();
        $fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('toto.jpeg');
        $fileStorage->getExtension(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('jpeg');

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        // Handle
        $handler = new UpdateHandler(
            $eventRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($update);
    }

    public function testHandleAddLocale()
    {
        // Actual event
        $event = EventFactory::createEvent();
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
            'old.vimeet.proximum.dev',
            'oldProximum',
            'team-project@example.net'
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
        $update->currency      = 'EUR';
        $update->leftColor     = '#FFFFFF';
        $update->rightColor    = '#000000';
        $update->textColor     = '#CCCCCC';
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            'hello.vimeet.proximum.dev',
            'proximum',
            'team-event@example.net'
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

        $eventLocaleChanged = new LocaleChangedEvent($expectedEvent);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $eventLocaleChanged)->shouldBeCalled();

        // Handle
        $handler = new UpdateHandler(
            $eventRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($update);
    }

    public function testHandleRemoveLocale()
    {
        // Actual event
        $event = EventFactory::createEvent();
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
            'old.vimeet.proximum.dev',
            'oldProximum',
            'team-project@example.net'
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
        $update->currency      = 'EUR';
        $update->leftColor     = '#FFFFFF';
        $update->rightColor    = '#000000';
        $update->textColor     = '#CCCCCC';
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            'hello.vimeet.proximum.dev',
            'proximum',
            'team-event@example.net'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldNotBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $eventLocaleChanged = new LocaleChangedEvent($expectedEvent);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $eventLocaleChanged)->shouldBeCalled();

        // Handle
        $handler = new UpdateHandler(
            $eventRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($update);
    }

    public function testHandleWithAlreadyUsedDomain()
    {
        $this->expectException(DomainAlreadyUsedException::class);
        // Actual event
        $event = EventFactory::createEvent();
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
            'old.vimeet.proximum.dev',
            'oldProximum',
            'team-project@example.net'
        );
        $event->getTranslations()->set('fr', new EventTranslation($event, 'fr', 'Bonjour'));
        $event->getTranslations()->set('en', new EventTranslation($event, 'en', 'Hello'));
        $event->setLogo('here.jpg', 'jpg');

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
        $update->currency      = 'USD';
        $update->leftColor     = '#FFFFFF';
        $update->rightColor    = '#000000';
        $update->textColor     = '#CCCCCC';
        $update->logo          = 'shouldBeUploadFile';
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            'hello.vimeet.proximum.dev',
            'proximum',
            'team-event@example.net'
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldNotBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(EventFactory::createEvent());
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate($event)->shouldNotBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->remove('here.jpg')->shouldNotBeCalled();
        $fileStorage->upload('shouldBeUploadFile')->shouldNotBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);


        // Handle
        $handler = new UpdateHandler(
            $eventRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($update);
    }
}
