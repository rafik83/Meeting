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
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Application\Command\User\Phone\SendCodeHandler;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class SendCodeHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $phone = '+33611223344';
        $code = '1234';

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $SMSSender = $this->prophesize(SMSSenderInterface::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $userEventPhoneRepository
            ->add(new User\UserEventPhone($user->reveal(), $event->reveal(), $code, $phone, $dateTime))
            ->shouldBeCalled();

        $translator->trans(Argument::any())->shouldBeCalled()->willReturn('SMS custom message');
        $SMSSender->send(new SMS($phone, 'SMS custom message'))->shouldBeCalled();

        $sendCodeHandler = new SendCodeHandler(
            $userEventPhoneRepository->reveal(),
            $SMSSender->reveal(),
            $translator->reveal(),
            $dateTime
        );

        $sendCodeHandler->handle(new SendCode($user->reveal(), $event->reveal(), $phone));
    }
}
