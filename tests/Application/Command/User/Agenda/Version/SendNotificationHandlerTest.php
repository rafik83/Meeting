<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\SendNotification;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\SendNotificationHandler;
use Proximum\Vimeet\Application\Exception\User\Agenda\Version\UserPhoneNotAvailableException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class SendNotificationHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $diffVerbalizer;

    /** @var ObjectProphecy */
    private $SMSSender;

    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $userEventPhoneRepository;

    /** @var ObjectProphecy */
    private $eventUrlGenerator;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->diffVerbalizer = $this->prophesize(DiffVerbalizer::class);
        $this->SMSSender = $this->prophesize(SMSSenderInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $this->eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
    }

    public function testNoPhone()
    {
        // Context
        $currentVersion = $this->prophesize(Version::class);
        $diff = [];

        // Expected
        $this->expectException(UserPhoneNotAvailableException::class);
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->SMSSender->send(Argument::any())->shouldNotBeCalled();

        // Handler
        $sendNotificationHandler = new SendNotificationHandler(
            $this->diffVerbalizer->reveal(),
            $this->SMSSender->reveal(),
            $this->translator->reveal(),
            $this->userEventPhoneRepository->reveal(),
            $this->eventUrlGenerator->reveal()
        );
        $sendNotificationHandler->handle(
            new SendNotification(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                $currentVersion->reveal(),
                $diff
            )
        );
    }

    public function testHandle()
    {
        // Context
        $currentVersion = $this->prophesize(Version::class);
        $diff = [];
        $phone = $this->prophesize(User\UserEventPhone::class);
        $phone->getPhone()->willReturn('+123123123');
        $this->user->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->sheet->getId()->willReturn(3);

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
        $this->diffVerbalizer
            ->verbalizeDiff($currentVersion, $diff, 'fr')
            ->shouldBeCalled()
            ->willReturn("Verbalized Diff\nNew Line");

        $this->eventUrlGenerator
            ->generateEventAbsoluteUrl(
                $this->event->reveal(),
                SendNotificationHandler::EVENT_AGENDA_ROUTE,
                ['sheet' => 3, '_locale' => 'fr']
            )->shouldBeCalled()
            ->willReturn('http://toto.tata.events/fr/sheet/3/agenda');

        $this->SMSSender
            ->send(
                new SMS('+123123123', "start:\nVerbalized Diff\nNew Line\nhttp://toto.tata.events/fr/sheet/3/agenda")
            )->shouldBeCalled();

        // Handler
        $sendNotificationHandler = new SendNotificationHandler(
            $this->diffVerbalizer->reveal(),
            $this->SMSSender->reveal(),
            $this->translator->reveal(),
            $this->userEventPhoneRepository->reveal(),
            $this->eventUrlGenerator->reveal()
        );
        $sendNotificationHandler->handle(
            new SendNotification(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                $currentVersion->reveal(),
                $diff
            )
        );
    }
}
