<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Create;
use Proximum\Vimeet\Application\Command\Event\CreateHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Actual user
        $dateTime = new \DateTime();
        $user = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_SUPER_ADMIN',
            $dateTime
        );

        $uploadedFile = new UploadedFile('gulpfile.js', 'gulpfile');

        // Update command
        $create                = new Create($user);
        $create->title         = 'barfoo';
        $create->locales       = ['fr', 'en'];
        $create->fallback      = 'en';
        $create->currency      = 'USD';
        $create->leftColor     = '#FFFFFF';
        $create->rightColor    = '#000000';
        $create->textColor     = '#CCCCCC';
        $create->logo          = $uploadedFile;
        $create->domain        = 'hello.vimeet.proximum.dev';
        $create->timeZone      = 'Europe/Paris';
        $create->organiserName = 'proximum';

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
            'hello.vimeet.proximum.dev',
            'proximum'
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpg', 'jpg');

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
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload($uploadedFile)->shouldBeCalled()->willReturn('toto.jpg');
        $fileStorage->getExtension($uploadedFile )->shouldBeCalled()->willReturn('jpg');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldBeCalled();

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithOrganizer()
    {
        // Actual user
        $dateTime = new \DateTime();
        $user = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'toto',
            'tata',
            'ROLE_ORGANIZER',
            $dateTime
        );

        $uploadedFile = new UploadedFile('gulpfile.js', 'gulpfile');

        // Update command
        $create             = new Create($user);
        $create->title      = 'barfoo';
        $create->locales    = ['fr', 'en'];
        $create->fallback   = 'en';
        $create->currency   = 'USD';
        $create->leftColor  = '#FFFFFF';
        $create->rightColor = '#000000';
        $create->textColor  = '#CCCCCC';
        $create->logo       = $uploadedFile;
        $create->domain     = 'hello.vimeet.proximum.dev';
        $create->timeZone   = 'Europe/Paris';

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
            'hello.vimeet.proximum.dev',
            'proximum'
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpg', 'jpg');

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
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);

        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload($uploadedFile)->shouldBeCalled()->willReturn('toto.jpg');
        $fileStorage->getExtension($uploadedFile)->shouldBeCalled()->willReturn('jpg');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldBeCalled();

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithAlreadyUsedDomain()
    {
        $this->expectException(DomainAlreadyUsedException::class);

        // Actual user
        $dateTime = new \DateTime();
        $user = new Admin(
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
        $create->logo          = 'shouldBeUploadFile';
        $create->domain        = 'hello.vimeet.proximum.dev';
        $create->timeZone      = 'Europe/Paris';
        $create->organiserName = 'proximum';

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
            'hello.vimeet.proximum.dev',
            'proximum'
        );
        $expectedEvent->getConfiguration()->setColors('#FFFFFF', '#000000', '#CCCCCC');
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));
        $expectedEvent->setLogo('toto.jpg', 'jpg');

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
        $eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(EventFactory::createEvent());
        $guidelineGenerator = $this->prophesize(Generator::class);
        $guidelineGenerator->generate(Argument::that(function (Event $event) use ($expectedEvent) {
            return $event->getTitle() === $expectedEvent->getTitle();
        }))->shouldNotBeCalled();
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload('shouldBeUploadFile')->shouldNotBeCalled()->willReturn('toto.jpg');
        $fileStorage->getExtension('shouldBeUploadFile')->shouldNotBeCalled()->willReturn('jpg');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return $content->getType() === Event\Content::TYPE_TERMS_OF_SALE;
        }))->shouldNotBeCalled();

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($create);
    }
}
