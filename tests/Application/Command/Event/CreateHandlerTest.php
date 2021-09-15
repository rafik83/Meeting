<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
    public function testHandle(): void
    {
        // Actual user
        $prefix   = new Prefix('Vimeet', 'Vi');
        $admin = $this->prophesize(Admin::class);

        // Update command
        $create                = new Create($admin->reveal());
        $create->title         = 'barfoo';
        $create->locales       = ['fr', 'en'];
        $create->mode          = 'et';
        $create->vat           = 20;
        $create->fallback      = 'en';
        $create->currency      = 'USD';
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
        $create->visio         = false;

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

        $expectedEvent->getConfiguration()->setVisio(false);
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));

        // Mock
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set($admin->reveal())->shouldNotBeCalled();
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
        }))->shouldBeCalled()->willReturn('/path/to/assets/for/event/123');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return Event\Content::TYPE_TERMS_OF_SALE === $content->getType();
        }))->shouldBeCalled();

        $duplicator = $this->prophesize(Duplicator::class);

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithOrganizer(): void
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
        $create->mode          = 'et';
        $create->vat           = 20;
        $create->fallback      = 'en';
        $create->currency      = 'USD';
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
        $create->visio         = false;

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

        $expectedEvent->getConfiguration()->setVisio(false);
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));

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
        }))->shouldBeCalled()->willReturn('/path/to/assets/for/event/123');


        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return Event\Content::TYPE_TERMS_OF_SALE === $content->getType();
        }))->shouldBeCalled();

        $duplicator = $this->prophesize(Duplicator::class);

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithAlreadyUsedDomain(): void
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
        $create->mode          = 'et';
        $create->fallback      = 'en';
        $create->currency      = 'USD';
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
        $create->visio         = false;

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
        $expectedEvent->getConfiguration()->setVisio(false);

        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', ''));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', ''));

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
        }))->shouldNotBeCalled()->willReturn('/path/to/assets/for/event/123');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository->add(Argument::that(function (Event\Content $content) use ($expectedEvent) {
            return Event\Content::TYPE_TERMS_OF_SALE === $content->getType();
        }))->shouldNotBeCalled();

        $duplicator = $this->prophesize(Duplicator::class);

        // Handle
        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $eventRepository->reveal(),
            $contentRepository->reveal(),
            $guidelineGenerator->reveal(),
            $duplicator->reveal()
        );
        $handler->handle($create);
    }

    public function testCreateFromOtherEvent(): void
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

        $event->getConfiguration()->setVisio(false);

        $create = new Create($user, $event);

        // Mock
        $adminRepository    = $this->prophesize(AdminRepositoryInterface::class);
        $eventRepository    = $this->prophesize(EventRepositoryInterface::class);
        $guidelineGenerator = $this->prophesize(Generator::class);
        $contentRepository  = $this->prophesize(ContentRepositoryInterface::class);
        $duplicator         = $this->prophesize(Duplicator::class);

        $eventRepository->getEventByDomain('hello.vimeet.proximum')->shouldBeCalled()->willReturn(null);

        $eventRepository->add(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle()
                && $expectedEvent->getConfiguration()->getHeaderLeftColor() === $event->getConfiguration()->getHeaderLeftColor()
            ;
        }))->shouldBeCalled();

        $eventRepository->set(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle();
        }))->shouldBeCalled();

        $adminRepository->set($user)->shouldNotBeCalled();

        $guidelineGenerator->generate(Argument::that(function (Event $expectedEvent) use ($event) {
            return $expectedEvent->getTitle() === $event->getTitle();
        }))->shouldBeCalled()->willReturn('/path/to/assets/for/event/123');

        $contentRepository->add(Argument::that(function (Event\Content $content) use ($event) {
            return Event\Content::TYPE_TERMS_OF_SALE === $content->getType();
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
            $duplicator->reveal()
        );
        $handler->handle($create);
    }
}
