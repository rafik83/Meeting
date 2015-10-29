<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\AddHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class AddHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWhenUserNotExists()
    {
        $event = new Event();
        $type  = new Type();
        $type->setParticipantTemplate(
            json_encode(
                ['foobar' => [
                    "required" => "true",
                    "private"  => "false",
                ]]
            )
        );
        $sheet = new Sheet($event, $type, [], []);
        $owner = false;

        $expectedUser        = new User('test@test.com', '', '', 'fr');
        $expectedParticipant = new Participant($sheet, $expectedUser, ['foobar' => 'barfoo'], $owner);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn(null);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $add = new Add($sheet, 'fr');
        $add->email = 'test@test.com';
        $add->data  = ['foobar' => 'barfoo'];

        $handler = new AddHandler($userRepository->reveal(), $participantRepository->reveal());
        $handler->handle($add);
    }

    public function testHandleWhenUserExists()
    {
        $event = new Event();
        $type  = new Type();
        $type->setParticipantTemplate(
            json_encode(
                ['foobar' => [
                    "required" => "true",
                    "private"  => "false",
                ]]
            )
        );
        $sheet = new Sheet($event, $type, [], []);
        $user  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $owner = false;

        $expectedParticipant = new Participant($sheet, $user, ['foobar' => 'barfoo'], $owner);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn($user);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $add = new Add($sheet, 'fr');
        $add->email = 'test@test.com';
        $add->data  = ['foobar' => 'barfoo'];

        $handler = new AddHandler($userRepository->reveal(), $participantRepository->reveal());
        $handler->handle($add);
    }
}
