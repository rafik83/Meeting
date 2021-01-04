<?php

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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var UpdateHandler */
    private $handler;

    public function setUp()
    {
        $this->event              = EventFactory::createEvent();
        $this->prefix             = EventFactory::createInvoicePrefix();
        $this->eventRepository    = $this->prophesize(EventRepositoryInterface::class);
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
            true,
            false,
            false
        );

        $this->event->getTranslations()->set('fr', new EventTranslation($this->event, 'fr', 'Bonjour'));
        $this->event->getTranslations()->set('en', new EventTranslation($this->event, 'en', 'Hello'));

        $this->handler = new UpdateHandler(
            $this->eventRepository->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    public function testHandle()
    {
        // Actual event
        $event = $this->event;
        $prefix = $this->prefix;

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
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';
        $update->invoicePrefix = $prefix;
        $update->analyticsCode = 'analyticsCode';
        $update->visible       = false;
        $update->googleLoginEnabled = true;
        $update->linkedinLoginEnabled = true;
        $update->accessControlEnabled = true;

        // Expected event
        $expectedEvent  = EventFactory::createEvent();
        $expectedPrefix = EventFactory::createInvoicePrefix();
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
            true,
            false,
            false,
            true,
            true,
            true
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent
            ->getConfiguration()
            ->setAnalyticsCode('analyticsCode');

        // Mock
        $this->eventRepository->set(Argument::that(function (Event $event) use ($expectedEvent) {
            return true;
        }))->shouldBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);

        $this->handler->handle($update);
    }

    public function testHandleAddLocale(): void
    {
        // Actual event
        $event  = $this->event;
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
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';
        $update->invoicePrefix = $prefix;
        $update->visible       = false;
        $update->displayParticipantNameOnPlanning = true;

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            true,
            false,
            false
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));
        $expectedEvent->getTranslations()->set('de', new EventTranslation($expectedEvent, 'de', ''));
        $expectedEvent->getConfiguration()->setParticipantInfoToDisplayOnPlanning(true, false);

        // Mock
        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);

        $eventLocaleChanged = new LocaleChangedEvent($expectedEvent);
        $this->eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $eventLocaleChanged)->shouldBeCalled();

        $this->handler->handle($update);
    }

    public function testHandleRemoveLocale(): void
    {
        // Actual event
        $event  = $this->event;
        $prefix = $this->prefix;

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
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';
        $update->invoicePrefix = $prefix;
        $update->visible       = false;

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            true,
            false,
            false
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Bonjour'));

        // Mock
        $this->eventRepository->set($expectedEvent)->shouldBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()->willReturn(null);

        $eventLocaleChanged = new LocaleChangedEvent($expectedEvent);
        $this->eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $eventLocaleChanged)->shouldBeCalled();

        $this->handler->handle($update);
    }

    public function testHandleWithAlreadyUsedDomain(): void
    {
        $this->expectException(DomainAlreadyUsedException::class);

        // Actual event
        $event  = $this->event;
        $prefix = $this->prefix;

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
        $update->logo          = 'shouldBeUploadFile';
        $update->domain        = 'hello.vimeet.proximum.dev';
        $update->timeZone      = 'Europe/Paris';
        $update->organiserName = 'proximum';
        $update->emailTeam     = 'team-event@example.net';
        $update->invoicePrefix = $prefix;
        $update->visible       = false;

        // Expected event
        $expectedEvent = EventFactory::createEvent();
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
            true,
            false,
            false
        );
        $expectedEvent->getTranslations()->set('fr', new EventTranslation($expectedEvent, 'fr', 'Salut'));
        $expectedEvent->getTranslations()->set('en', new EventTranslation($expectedEvent, 'en', 'Hello'));

        // Mock
        $this->eventRepository->set($expectedEvent)->shouldNotBeCalled();
        $this->eventRepository->getEventByDomain('hello.vimeet.proximum.dev')->shouldBeCalled()
            ->willReturn(EventFactory::createEvent());

        $this->handler->handle($update);
    }
}
