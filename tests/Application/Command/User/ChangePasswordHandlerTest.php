<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\User\ChangePassword;
use Proximum\Vimeet\Application\Command\User\ChangePasswordHandler;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ChangePasswordHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Actual user
        $user         = new User('test@test.com', '__salt__', 'encoded_password', 'fr');

        // Command
        $command                = new ChangePassword($user);
        $command->plainPassword = 'new-password';

        // Expected user after password update
        $expectedUser = new User('test@test.com', '__new_salt__', 'encoded_new_password', 'fr');

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__new_salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder
            ->encode($user, $command->plainPassword)
            ->shouldBeCalled()
            ->willReturn('encoded_new_password');

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($expectedUser)->shouldBeCalled();

        $handler = new ChangePasswordHandler(
            $userRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal()
        );
        $handler->handle($command);
    }
}
