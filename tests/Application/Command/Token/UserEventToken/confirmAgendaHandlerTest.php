<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Token\UserEventToken;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgenda;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgendaHandler;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenAlreadyConfirmedException;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class confirmAgendaHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->isConfirmed()->shouldBeCalled()->willReturn(false);
        $userEventToken->isAgendaConfirmation()->shouldBeCalled()->willReturn(true);
        $userEventToken->confirm($dateTime)->shouldBeCalled();

        $userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $userEventTokenRepository->set($userEventToken->reveal())->shouldBeCalled();

        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler($userEventTokenRepository->reveal(), $dateTime);
        $handler->handle($command);
    }

    public function testHandleUserEventTokenAlreadyConfirmedException()
    {
        $dateTime = new \DateTime();
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->isConfirmed()->shouldBeCalled()->willReturn(true);
        $userEventToken->confirm($dateTime)->shouldNotBeCalled();

        $userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $userEventTokenRepository->set($userEventToken->reveal())->shouldNotBeCalled();

        $this->expectException(UserEventTokenAlreadyConfirmedException::class);

        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler($userEventTokenRepository->reveal(), $dateTime);
        $handler->handle($command);
    }

    public function testHandleUserEventTokenUnexpectedTypeException()
    {
        $dateTime = new \DateTime();
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->isConfirmed()->shouldBeCalled()->willReturn(false);
        $userEventToken->isAgendaConfirmation()->shouldBeCalled()->willReturn(false);
        $userEventToken->confirm($dateTime)->shouldNotBeCalled();

        $userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $userEventTokenRepository->set($userEventToken->reveal())->shouldNotBeCalled();

        $this->expectException(UserEventTokenUnexpectedTypeException::class);

        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler($userEventTokenRepository->reveal(), $dateTime);
        $handler->handle($command);
    }
}
