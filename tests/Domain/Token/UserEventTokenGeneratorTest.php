<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Token;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UniqidGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class UserEventTokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUserEventTokenForConfirmAgendaExistingToken()
    {
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $dateTime = new \DateTime();
        $type = UserEventTokenType::AGENDA_CONFIRMED;

        $userEventToken = new UserEventToken($event->reveal(), $user->reveal(), $type, 'token', $dateTime);

        $userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $userEventTokenRepository->findByEventAndUser($event, $user, $type)->shouldBeCalled()->willReturn($userEventToken);

        $uniqidGenerator = $this->prophesize(UniqidGenerator::class);
        $uniqidGenerator->generate()->shouldNotBeCalled();

        $generator = new UserEventTokenGenerator($userEventTokenRepository->reveal(), $uniqidGenerator->reveal(), $dateTime);
        $result = $generator->getUserEventTokenForConfirmAgenda($event->reveal(), $user->reveal(), $type);


        $this->assertEquals($userEventToken, $result);
    }

    public function testGetUserEventTokenForConfirmAgenda()
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalle()->willReturn(456);
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(123);
        $dateTime = new \DateTime();
        $type = UserEventTokenType::AGENDA_CONFIRMED;
        $uniqid = uniqid(mt_rand());

        $token = hash('sha1', sprintf('%s%s%s%s%s', 123, 456, $type, $dateTime->format('c'), $uniqid));
        $expectedUserEventToken = new UserEventToken($event->reveal(), $user->reveal(), $type, $token, $dateTime);

        $userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $userEventTokenRepository->findByEventAndUser($event, $user, $type)->shouldBeCalled()->willReturn(null);
        $userEventTokenRepository->add($expectedUserEventToken)->shouldBeCalled();

        $uniqidGenerator = $this->prophesize(UniqidGenerator::class);
        $uniqidGenerator->generate()->shouldBeCalled()->willReturn($uniqid);

        $generator = new UserEventTokenGenerator($userEventTokenRepository->reveal(), $uniqidGenerator->reveal(), $dateTime);
        $result = $generator->getUserEventTokenForConfirmAgenda($event->reveal(), $user->reveal(), $type);

        $this->assertEquals($token, $result->getToken());
        $this->assertEquals($type, $result->getType());
        $this->assertEquals($token, $result);
    }
}
