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
use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Application\Command\User\Phone\SendCodeHandler;
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

        $userEventPhoneRepositoryInterface = $this->prophesize(UserEventPhoneRepositoryInterface::class);

        $userEventPhoneRepositoryInterface
            ->add(new User\UserEventPhone($user->reveal(), $event->reveal(), $code, $phone, $dateTime))
            ->shouldBeCalled();

        $sendCodeHandler = new SendCodeHandler($userEventPhoneRepositoryInterface->reveal(), $dateTime);
        $sendCodeHandler->handle(new SendCode($user->reveal(), $event->reveal(), $phone));
    }
}
