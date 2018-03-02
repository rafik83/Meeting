<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\View\SSOComexposiumView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class SSOComexposiumViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var string */
    private $comexposiumSSOLoaderLibEndpoint;

    public function setUp()
    {
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->comexposiumSSOLoaderLibEndpoint = 'https://example.net/endpoint';
        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleNotEnabled()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));

        $this->assertEquals(null, $result);
    }

    public function testHandleParametersNotPresent()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));

        $this->assertEquals(null, $result);
    }

    public function testHandleNoUser()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn(null);

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'en', 'email@example.net'));
        $expected = new SSOComexposiumView(
            'salon',
            'sessionSalon',
            'application123',
            'eng-GB',
            'email@example.net',
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleNoSheet()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $user = $this->prophesize(User::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));
        $expected = new SSOComexposiumView(
            'salon',
            'sessionSalon',
            'application123',
            'fre-FR',
            'email@example.net',
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithSheetImportedAndOwner()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $sheet->isImported()->shouldBeCalled()->willReturn(true);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn(null);
        $sheet->isOwner($user->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));
        $expected = new SSOComexposiumView(
            'salon',
            'sessionSalon',
            'application123',
            'fre-FR',
            'email@example.net',
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithSheetImportedAndParticipantImported()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $sheet->isImported()->shouldBeCalled()->willReturn(true);
        $participant = $this->prophesize(Participant::class);
        $participant->isImported()->shouldBeCalled()->willReturn(true);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant);
        $sheet->isOwner($user->reveal())->shouldBeCalled()->willReturn(false);

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));
        $expected = new SSOComexposiumView(
            'salon',
            'sessionSalon',
            'application123',
            'fre-FR',
            'email@example.net',
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithSheetImportedParticipantNotImportedAndNotOwner()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $sheet->isImported()->shouldBeCalled()->willReturn(true);
        $participant = $this->prophesize(Participant::class);
        $participant->isImported()->shouldBeCalled()->willReturn(false);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant);
        $sheet->isOwner($user->reveal())->shouldBeCalled()->willReturn(false);

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));

        $this->assertEquals(null, $result);
    }

    public function testHandleWithSheetNotImported()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $sheet->isImported()->shouldBeCalled()->willReturn(false);
        $participant = $this->prophesize(Participant::class);
        $participant->isImported()->shouldBeCalled()->willReturn(false);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant);
        $sheet->isOwner($user->reveal())->shouldBeCalled()->willReturn(false);

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr', 'email@example.net'));

        $this->assertEquals(null, $result);
    }
}
