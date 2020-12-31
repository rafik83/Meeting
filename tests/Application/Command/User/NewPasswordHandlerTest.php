<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\User\NewPassword;
use Proximum\Vimeet\Application\Command\User\NewPasswordHandler;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class NewPasswordHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event             = EventFactory::createEvent();
        $user              = new User('test@test.fr', 'test', 'test', 'fr');
        $expectedUser      = new User('test@test.fr', 'test', 'tatatata', 'fr');
        $command           = new NewPassword($user, $event);
        $command->password = 'totototo';

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($expectedUser)->shouldBeCalled();

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('test');

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $encoder = $this->prophesize(PasswordEncoderInterface::class);
        $encoder->encode($user, $command->password)->shouldBeCalled()->willReturn('tatatata');

        $forgottenPasswordToken = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordToken->deleteAllForUser($user)->shouldBeCalled();

        $handler = new NewPasswordHandler(
            $userRepository->reveal(),
            $encoder->reveal(),
            $saltGenerator->reveal(),
            $forgottenPasswordToken->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
