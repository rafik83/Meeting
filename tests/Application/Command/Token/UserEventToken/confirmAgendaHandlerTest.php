<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgenda;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgendaHandler;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class confirmAgendaHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->confirm($dateTime)->shouldBeCalled();

        $userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $userEventTokenRepository->set($userEventToken->reveal())->shouldBeCalled();

        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler($userEventTokenRepository->reveal(), $dateTime);
        $handler->handle($command);
    }
}
