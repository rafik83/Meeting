<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\User\Register;
use Proximum\Vimeet\Application\Command\User\RegisterHandler;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class RegisterHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $command           = new Register();
        $command->email    = 'test@test.com';
        $command->password = 'password';
        $command->locale   = 'fr';

        $user         = new User('test@test.com', '__salt__', null, 'fr');
        $expectedUser = new User('test@test.com', '__salt__', 'encoded_password', 'fr');

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(function (User $user) use ($user) {
            return $user->getEmail() === $user->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $handler = new RegisterHandler($userRepository->reveal(), $passwordEncoder->reveal(), $saltGenerator->reveal());
        $handler->handle($command);
    }

    public function testEmailAlreadyExistsException()
    {
        $this->expectException(EmailAlreadyExistsException::class);

        $command           = new Register();
        $command->email    = 'test@test.com';
        $command->password = 'password';

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldNotBeCalled();

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode()->shouldNotBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->emailExists($command->email)->shouldBeCalled()->willReturn(true);
        $userRepository->add()->shouldNotBeCalled();

        $handler = new RegisterHandler($userRepository->reveal(), $passwordEncoder->reveal(), $saltGenerator->reveal());
        $handler->handle($command);
    }
}
