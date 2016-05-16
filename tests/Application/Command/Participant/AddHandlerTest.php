<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\AddHandler;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AddHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWhenUserNotExists()
    {
        $now   = new \DateTime();
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], $now);
        $owner = false;

        $expectedSheet       = new Sheet($event, $type, [], [], $now);
        $expectedUser        = new User('test@test.com', '', '', 'fr');
        $expectedParticipant = new Participant($expectedSheet, $expectedUser, ['foobar' => 'barfoo'], $owner, false);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn(null);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);
        $eventDispatcher                = $this->prophesize(EventDispatcherInterface::class);

        $expectedActivateAccountToken = new ActivateAccountToken(
            $expectedUser,
            'STRING',
            $sheet,
            $now
        );

        $activateAccountEvent = new ActivateAccountEvent(
            $expectedUser,
            $event,
            $expectedActivateAccountToken,
            'fr'
        );

        $activateAccountTokenGenerator->generate($expectedUser, $sheet)->shouldBeCalled()->willReturn($expectedActivateAccountToken);
        $activateAccountTokenRepository->deleteAllForUser($expectedUser)->shouldBeCalled();
        $activateAccountTokenRepository->create($expectedActivateAccountToken)->shouldBeCalled();
        $eventDispatcher->dispatch('user_activate_account', $activateAccountEvent)->shouldBeCalled();

        $add = new Add($sheet, 'fr');
        $add->email = 'test@test.com';
        $add->data  = ['foobar' => 'barfoo'];

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $activateAccountTokenRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($add);
    }

    public function testHandleWhenUserExists()
    {
        $now   = new \DateTime();
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], $now);
        $user  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $owner = false;

        $expectedSheet       = new Sheet($event, $type, [], [], $now);
        $expectedParticipant = new Participant($expectedSheet, $user, ['foobar' => 'barfoo'], $owner, false);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn($user);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);
        $eventDispatcher                = $this->prophesize(EventDispatcherInterface::class);

        $add = new Add($sheet, 'fr');
        $add->email = 'test@test.com';
        $add->data  = ['foobar' => 'barfoo'];

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $activateAccountTokenRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($add);
    }
}
