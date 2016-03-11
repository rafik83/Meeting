<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\User\NewPassword;
use Proximum\Vimeet\Application\Command\User\NewPasswordHandler;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class NewPasswordHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user              = new User('test@test.fr', 'test', 'test', 'fr');
        $expectedUser      = new User('test@test.fr', 'test', 'tatatata', 'fr');
        $command           = new NewPassword($user);
        $command->password = 'totototo';

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($expectedUser)->shouldBeCalled();

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('test');

        $encoder = $this->prophesize(PasswordEncoderInterface::class);
        $encoder->encode($user, $command->password)->shouldBeCalled()->willReturn('tatatata');

        $forgottenPasswordToken = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordToken->deleteAllForUser($user)->shouldBeCalled();

        $handler = new NewPasswordHandler(
            $userRepository->reveal(),
            $encoder->reveal(),
            $saltGenerator->reveal(),
            $forgottenPasswordToken->reveal()
        );
        $handler->handle($command);
    }
}
