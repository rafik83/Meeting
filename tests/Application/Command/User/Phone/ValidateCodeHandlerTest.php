<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\Phone\ValidateCode;
use Proximum\Vimeet\Application\Command\User\Phone\ValidateCodeHandler;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeAlreadyValidatedException;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeNotValidException;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class ValidateCodeHandlerTest extends TestCase
{
    public function testHandleCodeNotValid()
    {
        $this->expectException(CodeNotValidException::class);

        $dateTime = new \DateTime();
        $userEventPhone = $this->prophesize(UserEventPhone::class);
        $userEventPhone->getCode()->willReturn('1234');
        $userEventPhone->isValidated()->willReturn(false);

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->set($userEventPhone->reveal())->shouldNotBeCalled();

        $command = new ValidateCode($userEventPhone->reveal());
        $command->code = '4321';
        $handler = new ValidateCodeHandler($userEventPhoneRepository->reveal(), $dateTime);
        $handler->handle($command);
    }

    public function testHandleUserEventPhoneAlreadyValidated()
    {
        $this->expectException(CodeAlreadyValidatedException::class);

        $dateTime = new \DateTime();
        $userEventPhone = $this->prophesize(UserEventPhone::class);
        $userEventPhone->isValidated()->willReturn(true);

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->set($userEventPhone->reveal())->shouldNotBeCalled();

        $command = new ValidateCode($userEventPhone->reveal());
        $handler = new ValidateCodeHandler($userEventPhoneRepository->reveal(), $dateTime);
        $handler->handle($command);
    }

    public function testHandle()
    {
        $dateTime = new \DateTime();
        $userEventPhone = $this->prophesize(UserEventPhone::class);
        $userEventPhone->isValidated()->willReturn(false);
        $userEventPhone->getCode()->willReturn('1234');
        $userEventPhone->validate($dateTime)->shouldBeCalled();

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->set($userEventPhone->reveal())->shouldBeCalled();

        $command = new ValidateCode($userEventPhone->reveal());
        $command->code = '1234';
        $handler = new ValidateCodeHandler($userEventPhoneRepository->reveal(), $dateTime);
        $handler->handle($command);
    }
}
