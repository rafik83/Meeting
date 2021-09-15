<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version\Notification;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommandHandler;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Exception\User\Agenda\Version\UserPhoneNotAvailableException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class SMSNotificationCommandHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $SMSSender, $translator, $userEventPhoneRepository, $eventUrlGenerator, $event, $sheet, $user;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->SMSSender = $this->prophesize(SMSSenderInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $this->eventUrlGenerator = $this->prophesize(Event\EventUrlGeneratorInterface::class);
    }

    public function testNoPhone()
    {
        $diff = 'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.';

        // Expected
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->SMSSender->send(Argument::any())->shouldNotBeCalled();

        // Handler
        $sendNotificationHandler = new SMSNotificationCommandHandler(
            $this->SMSSender->reveal(),
            $this->translator->reveal(),
            $this->userEventPhoneRepository->reveal(),
            $this->eventUrlGenerator->reveal()
        );
        $sendNotificationHandler->handle(
            new SMSNotificationCommand(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                $diff
            )
        );
    }

    public function testHandle()
    {
        // Context
        $diff = 'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.';

        $phone = $this->prophesize(User\UserEventPhone::class);
        $phone->getPhone()->willReturn('+123123123');
        $this->user->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->sheet->getId()->willReturn(3);
        $this->sheet->getUserLocale($this->user->reveal())->willReturn('fr');

        // Expected
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($phone->reveal());
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_AGENDA_MODIFIED,
                [],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                'fr'
            )->shouldBeCalled()
            ->willReturn('start:');

        $this->eventUrlGenerator
            ->generateEventAbsoluteUrl(
                $this->event->reveal(),
                Route::AGENDA_DEFAULT,
                [
                    'sheet' => 3,
                    '_locale' => 'fr'
                ]
            )->shouldBeCalled()
            ->willReturn('http://toto.tata.events/fr/sheet/3/agenda');

        $this->SMSSender
            ->send(
                new SMS(
                    '+123123123',
                    "start:\nVotre rendez-vous avec Tata est déplacé à 10h00 en STAND10.\nhttp://toto.tata.events/fr/sheet/3/agenda"
                )
            )->shouldBeCalled();

        // Handler
        $sendNotificationHandler = new SMSNotificationCommandHandler(
            $this->SMSSender->reveal(),
            $this->translator->reveal(),
            $this->userEventPhoneRepository->reveal(),
            $this->eventUrlGenerator->reveal()
        );
        $sendNotificationHandler->handle(
            new SMSNotificationCommand(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                $diff
            )
        );
    }
}
