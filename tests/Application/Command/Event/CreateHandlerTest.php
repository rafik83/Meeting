<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Create;
use Proximum\Vimeet\Application\Command\Event\CreateHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Event\Duplicator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Actual user
        $prefix   = new Prefix('Vimeet', 'Vi');
        $dateTime = new \DateTime();
        $user     = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_SUPER_ADMIN',
            $dateTime
        );

        // Update command
        $create                = new Create($user);
        $create->title         = 'barfoo';
        $create->locales       = ['fr', 'en'];
        $create->fallback      = 'en';
        $create->currency      = 'USD';
        $create->leftColor     = '#FFFFFF';
        $create->rightColor    = '#000000';
        $create->textColor     = '#CCCCCC';
        $create->invoicePrefix = $prefix;
        // It mocks the creation of an UploadedFile
        $create->logo          = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $create->domain        = 'hello.vimeet.proximum';
        $create->timeZone      = 'Europe/Paris';
        $create->organiserName = 'proximum';
        $create->emailTeam     = 'team-project@example.net';
        $create->visible       = true;

        // Expected event
        $expectedEvent = new Event(
            'barfoo',
            'en',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum',
            'proximum',
            'team-project@example.net',
            $prefix,
            true
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');

        $expectedUser = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_SUPER_ADMIN',
            $dateTime
        );
        // Mock
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set($expectedUser)->shouldNotBeCalled();
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->add(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();
        $eventRepository->set(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('toto.jpeg');
        $fileStorage->getExtension(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('jpeg');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldBeCalled();

        $duplicator = $this->prophesize(Duplicator::class);

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithOrganizer()
    {
        // Actual user
        $prefix   = new Prefix('Vimeet', 'Vi');
        $dateTime = new \DateTime();
        $user     = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_ORGANIZER',
            $dateTime
        );

        // Update command
        $create                = new Create($user);
        $create->title         = 'barfoo';
        $create->locales       = ['fr', 'en'];
        $create->fallback      = 'en';
        $create->currency      = 'USD';
        $create->leftColor     = '#FFFFFF';
        $create->rightColor    = '#000000';
        $create->textColor     = '#CCCCCC';
        $create->invoicePrefix = $prefix;
        $create->logo          = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $create->domain        = 'hello.vimeet.proximum';
        $create->timeZone      = 'Europe/Paris';
        $create->emailTeam     = 'team-project@example.net';
        $create->visible       = true;

        // Expected event
        $expectedEvent = new Event(
            'barfoo',
            'en',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum',
            'proximum',
            'team-project@example.net',
            $prefix,
            true
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');

        $expectedUser = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_ORGANIZER',
            $dateTime
        );
        $expectedUser->addEvent($expectedEvent);

        // Mock
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set(Argument::that(function (Admin $admin) use ($expectedUser) {
            return $admin->getEvents()->count() === $expectedUser->getEvents()->count();
        }))->shouldBeCalled();

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->add(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();
        $eventRepository->set(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum')->shouldBeCalled()->willReturn(null);

        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('toto.jpeg');
        $fileStorage->getExtension(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('jpeg');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldBeCalled();

        $duplicator = $this->prophesize(Duplicator::class);

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithAlreadyUsedDomain()
    {
        $this->expectException(DomainAlreadyUsedException::class);

        // Actual user
        $prefix   = new Prefix('Vimeet', 'Vi');
        $dateTime = new \DateTime();
        $user     = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_SUPER_ADMIN',
            $dateTime
        );

        // Update command
        $create                = new Create($user);
        $create->title         = 'barfoo';
        $create->locales       = ['fr', 'en'];
        $create->fallback      = 'en';
        $create->currency      = 'USD';
        $create->leftColor     = '#FFFFFF';
        $create->rightColor    = '#000000';
        $create->textColor     = '#CCCCCC';
        $create->invoicePrefix = $prefix;
        $create->logo          = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $create->domain        = 'hello.vimeet.proximum';
        $create->timeZone      = 'Europe/Paris';
        $create->organiserName = 'proximum';
        $create->visible       = true;

        // Expected event
        $expectedEvent = new Event(
            'barfoo',
            'en',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum',
            'proximum',
            'team-project@example.net',
            $prefix,
            true
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpeg', 'jpeg');

        $expectedUser = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_SUPER_ADMIN',
            $dateTime
        );

        // Mock
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set($expectedUser)->shouldNotBeCalled();
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->add(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldNotBeCalled();
        $eventRepository->getEventByDomain('hello.vimeet.proximum')->shouldBeCalled()
            ->willReturn(EventFactory::createEvent());
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldNotBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldNotBeCalled()->willReturn('toto.jpeg');
        $fileStorage->getExtension(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldNotBeCalled()->willReturn('jpeg');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldNotBeCalled();

        $duplicator = $this->prophesize(Duplicator::class);

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }

    public function testCreateFromOtherEvent()
    {
        $prefix = new Prefix('Vimeet', 'Vi');
        $user   = AdminFactory::create();
        $duplicatedEvent = EventFactory::createEvent('barfoo');
        $event  = new Event(
            'barfoo',
            'en',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'USD',
            'Europe/Paris',
            'hello.vimeet.proximum',
            'proximum',
            'team-project@example.net',
            $prefix,
            true,
            $duplicatedEvent
        );

        $create = new Create($user, $event);

        // Mock
        $adminRepository    = $this->prophesize(AdminRepositoryInterface::class);
        $eventRepository    = $this->prophesize(EventRepositoryInterface::class);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $fileStorage        = $this->prophesize(FileStorageInterface::class);
        $contentRepository  = $this->prophesize(ContentRepositoryInterface::class);
        $duplicator         = $this->prophesize(Duplicator::class);

        $eventRepository->getEventByDomain('hello.vimeet.proximum')->shouldBeCalled()->willReturn(null);

        $eventRepository->add(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle();
        }))->shouldBeCalled();

        $eventRepository->set(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle();
        }))->shouldBeCalled();

        $adminRepository->set($user)->shouldNotBeCalled();

        $guidelineGenerator->generate(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle();
        }))->shouldBeCalled();

        $fileStorage->upload(Argument::type(UploadedFile::class))->shouldNotBeCalled();
        $fileStorage->getExtension(Argument::type(UploadedFile::class))->shouldNotBeCalled();

        $contentRepository->add(Argument::that(function (Event\Content $content) use ($event) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldBeCalled();

        $duplicator->duplicate(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle();
        }))->shouldBeCalled();

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }
}
