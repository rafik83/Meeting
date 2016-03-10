<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\User;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPasswordHandler;
use Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\User\ActivateAccountTokenRepository;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ActivateAccountPasswordHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user = new User('test@test.fr', '__OLDSALT__', '__OLD__', 'fr');

        // Expected
        $expectedUser = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');

        // Mock
        $userRepository                 = $this->prophesize(UserRepositoryInterface::class);
        $encoder                        = $this->prophesize(PasswordEncoderInterface::class);
        $saltGenerator                  = $this->prophesize(SaltGeneratorInterface::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepository::class);

        $saltGenerator->generate()->shouldBeCalled()->willReturn('__SALT__');
        $encoder->encode(Argument::that(function (User $encodedUser) use ($user) {
            return $user->getEmail() === $encodedUser->getEmail();
        }), 'TOTO')->shouldBeCalled()->willReturn('__TEST__');
        $userRepository->set($expectedUser)->shouldBeCalled();
        $activateAccountTokenRepository->deleteAllForUser($expectedUser)->shouldBeCalled();

        $activeAccountPassword = new ActivateAccountPassword($user);
        $activeAccountPassword->password = 'TOTO';

        $handler = new ActivateAccountPasswordHandler(
            $userRepository->reveal(),
            $encoder->reveal(),
            $saltGenerator->reveal(),
            $activateAccountTokenRepository->reveal()
        );
        $handler->handle($activeAccountPassword);
    }
}
