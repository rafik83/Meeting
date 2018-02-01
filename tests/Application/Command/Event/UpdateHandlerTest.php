<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImage;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImageHandler;
use Proximum\Vimeet\Application\Command\Event\Update;
use Proximum\Vimeet\Application\Command\Event\UpdateHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Event\Event\LocaleChangedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateHandlerTest extends TestCase
{
    /** @var RemoveImageHandler */
    private $removeImageHandler;

    /** @var Event */
    private $event;

    /** @var Prefix */
    private $prefix;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var Generator */
    private $guidelineGenerator;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var UpdateHandler */
    private $handler;

    public function setUp()
    {
        $this->removeImageHandler = $this->prophesize(RemoveImageHandler::class);
        $this->event              = EventFactory::createEvent();
        $this->prefix             = EventFactory::createInvoicePrefix();
        $this->eventRepository    = $this->prophesize(EventRepositoryInterface::class);
        $this->guidelineGenerator = $this->prophesize(Generator::class);
        $this->fileStorage        = $this->prophesize(FileStorageInterface::class);
        $this->eventDispatcher    = $this->prophesize(EventDispatcherInterface::class);

        $this->event->update(
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
            'team-project@example.net',
            $this->prefix,
            true,
            true
        );

        $this->event->getTranslations()->set('fr', new EventTranslation($this->event, 'fr', 'Bonjour'));
        $this->event->getTranslations()->set('en', new EventTranslation($this->event, 'en', 'Hello'));

        $this->handler = new UpdateHandler(
            $this->eventRepository->reveal(),
            $this->guidelineGenerator->reveal(),
            $this->fileStorage->reveal(),
            $this->eventDispatcher->reveal(),
            $this->removeImageHandler->reveal()
        );
    }

    public function testHandle()
    {
        // Actual event
        $event = $this->event;
        $prefix = $this->prefix;
        $event->getConfiguration()->setColors('#111111', '#BBBBBB', '#333333', '#CCCCCC');
        $event->setLogo('here.jpeg', 'jpeg');
        $event->getConfiguration()->setBackgroundImage('tutu.jpeg');

        // Update command
        $update                = new Update($event);
        $update->title         = 'barfoo';
        $update->locales       = ['fr', 'en'];
        $update->fallback      = 'en';
        $update->translations  = [
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
        $update->invoicePrefix = $prefix;
        $update->analyticsCode = 'analyticsCode';
        $update->visible       = false;
        $update->backgroundImage = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $update->backgroundColor = '#CCCCCC';

        // Expected event
        $expectedEvent  = EventFactory::createEvent();
        $expectedPrefix = EventFactory::createInvoicePrefix();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
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
            'team-event@example.net',
            $expectedPrefix,
            false,
            true
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');
        $expectedEvent
            ->getConfiguration()
            ->setAnalyticsCode('analyticsCode');
        $expectedEvent->getConfiguration()->setBackgroundImage('tata.jpeg');

        // Mock
        $this->eventRepository->set(Argument::that(function (Event $event) use ($expectedEvent) {
            return true;
        }))->shouldBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $this->guidelineGenerator->generate($event)->shouldBeCalled();
        $this->fileStorage->remove('here.jpeg')->shouldBeCalled();
        $this->fileStorage->remove('tutu.jpeg')->shouldBeCalled();
        $this->fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('toto.jpeg');
        $this->fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('tata.jpeg');
        $this->fileStorage->getExtension(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('jpeg');

        $this->handler->handle($update);
    }

    public function testHandleRemoveBackgroundImage()
    {
        $event = $this->event;
        $event->getConfiguration()->setBackgroundImage('tutu.jpeg');
        $event->getConfiguration()->setColors('leftColor', 'rightColor', 'textColor', 'backgroundColor');

        $update                = new Update($event);
        $update->title         = 'barfoo';
        $update->locales       = ['fr', 'en'];
        $update->fallback      = 'en';
        $update->currency      = 'USD';
        $update->logo          = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';
        $update->invoicePrefix = $this->prefix;
        $update->analyticsCode = 'analyticsCode';
        $update->visible       = false;
        $update->isBackgroundImageToRemove = true;

        $expectedEvent  = $this->event;
        $expectedPrefix = $this->prefix;
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
            'team-event@example.net',
            $expectedPrefix,
            false,
            true
        );

        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->removeImageHandler->handle(new RemoveImage($event))->shouldBeCalled();

        $this->handler->handle($update);
    }

    public function testHandleAddLocale()
    {
        // Actual event
        $event  = $this->event;
        $event->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
        $prefix = $this->prefix;

        // Update command
        $update                = new Update($event);
        $update->title         = 'foobar';
        $update->locales       = ['fr', 'en', 'de'];
        $update->fallback      = 'fr';
        $update->translations  = [
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
        $update->invoicePrefix = $prefix;
        $update->visible       = false;

        // Expected event
        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
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
            'team-event@example.net',
            $prefix,
            false,
            true
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->getTranslations()->set('de', new EventTranslation($expectedEvent, 'de', ''));

        // Mock
        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $this->guidelineGenerator->generate($event)->shouldNotBeCalled();

        $eventLocaleChanged = new LocaleChangedEvent($expectedEvent);
        $this->eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $eventLocaleChanged)->shouldBeCalled();

        $this->handler->handle($update);
    }

    public function testHandleRemoveLocale()
    {
        // Actual event
        $event  = $this->event;
        $prefix = $this->prefix;
        $event->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');

        // Update command
        $update                = new Update($event);
        $update->title         = 'foobar';
        $update->locales       = ['fr'];
        $update->fallback      = 'fr';
        $update->translations  = [
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
        $update->invoicePrefix = $prefix;
        $update->visible       = false;

        // Expected event
        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
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
            'team-event@example.net',
            $prefix,
            false,
            true
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));

        // Mock
        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $this->guidelineGenerator->generate($event)->shouldNotBeCalled();

        $eventLocaleChanged = new LocaleChangedEvent($expectedEvent);
        $this->eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $eventLocaleChanged)->shouldBeCalled();

        $this->handler->handle($update);
    }

    public function testHandleWithAlreadyUsedDomain()
    {
        $this->expectException(DomainAlreadyUsedException::class);
        // Actual event
        $event  = $this->event;
        $prefix = $this->prefix;
        $event->getConfiguration()->setColors('#111111', '#BBBBBB', '#333333', '#CCCCCC');
        $event->setLogo('here.jpg', 'jpg');

        // Update command
        $update                = new Update($event);
        $update->title         = 'barfoo';
        $update->locales       = ['fr', 'en'];
        $update->fallback      = 'en';
        $update->translations  = [
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
        $update->invoicePrefix = $prefix;
        $update->visible       = false;

        // Expected event
        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC', '#CCCCCC');
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
            'team-event@example.net',
            $prefix,
            false,
            true
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');

        // Mock
        $this->eventRepository->set($expectedEvent)->shouldNotBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()
            ->willReturn(EventFactory::createEvent());
        $this->guidelineGenerator->generate($event)->shouldNotBeCalled();
        $this->fileStorage->remove('here.jpg')->shouldNotBeCalled();
        $this->fileStorage->upload('shouldBeUploadFile')->shouldNotBeCalled();

        $this->handler->handle($update);
    }
}
