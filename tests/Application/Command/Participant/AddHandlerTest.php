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
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
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
        $event = new Event();
        $type  = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'required' => 'true',
                'private'  => 'false',
            ]
        ]);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $owner = false;

        $expectedSheet       = new Sheet($event, $type, [], [], new \DateTime());
        $expectedUser        = new User('test@test.com', '', '', 'fr');
        $expectedParticipant = new Participant($expectedSheet, $expectedUser, ['foobar' => 'barfoo'], $owner, false);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn(null);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $participantManager = $this->prophesize(ParticipantManager::class);
        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);
        $eventDispatcher                = $this->prophesize(EventDispatcherInterface::class);

        $expectedActivateAccountToken = new ActivateAccountToken(
            $expectedUser,
            'STRING',
            $sheet,
            new \DateTime()
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

        $validator = $this->prophesize(Validator::class);

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantManager->reveal(),
            $participantRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $activateAccountTokenRepository->reveal(),
            $validator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($add);
    }

    public function testHandleWhenUserExists()
    {
        $event = new Event();
        $type  = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'required' => true,
                'private'  => false,
            ]
        ]);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $user  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $owner = false;

        $expectedSheet       = new Sheet($event, $type, [], [], new \DateTime());
        $expectedParticipant = new Participant($expectedSheet, $user, ['foobar' => 'barfoo'], $owner, false);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn($user);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $participantManager = $this->prophesize(ParticipantManager::class);
        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);
        $eventDispatcher                = $this->prophesize(EventDispatcherInterface::class);


        $add = new Add($sheet, 'fr');
        $add->email = 'test@test.com';
        $add->data  = ['foobar' => 'barfoo'];

        $validator = $this->prophesize(Validator::class);

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantManager->reveal(),
            $participantRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $activateAccountTokenRepository->reveal(),
            $validator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($add);
    }

    public function testHandleWithOrder()
    {
        $event = new Event();
        $type  = new Type($event);
        $type->setParticipantTemplate(['foobar' => ['required' => true, 'private'  => false]]);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $userRepository        = $this->prophesize(UserRepositoryInterface::class);
        $participantManager    = $this->prophesize(ParticipantManager::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $createdAt             = new \DateTime;

        // 1
        $user = new User('test@test.com', '__SALT__', 'password', 'fr');
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn($user);

        // 2
        $expectedSheetWithParticipant = new Sheet($event, $type, [], [], new \DateTime());
        $expectedParticipant          = new Participant($expectedSheetWithParticipant, $user, ['foobar' => 'barfoo'], false, false);
        $expectedOrder                = new Order($expectedSheetWithParticipant, 'unpaid', [], [], [], [], $createdAt, 'toto');
        $participantManager->findOrderToAttach(Argument::that(function (Sheet $sheet) {
            return true;
        }))->shouldBeCalled()->willReturn($expectedOrder);

        // 3
        $expectedSheetWithParticipant2 = new Sheet($event, $type, [], [], new \DateTime());
        $expectedParticipant2          = new Participant($expectedSheetWithParticipant2, $user, ['foobar' => 'barfoo'], false, false);
        $expectedOrder2                = new Order($expectedSheetWithParticipant2, 'unpaid', [], [], [], [], $createdAt, 'toto');
        $expectedParticipant2->setActive(true);
        $expectedParticipant2->setOrder($expectedOrder2);
        $participantRepository->add(Argument::that(function (Participant $participant) {
            return $participant->isActive();
        }))->shouldBeCalled();

        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);
        $eventDispatcher                = $this->prophesize(EventDispatcherInterface::class);

        $add = new Add($sheet, 'fr');
        $add->email = 'test@test.com';
        $add->data  = ['foobar' => 'barfoo'];

        $validator = $this->prophesize(Validator::class);

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantManager->reveal(),
            $participantRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $activateAccountTokenRepository->reveal(),
            $validator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($add);
    }
}
