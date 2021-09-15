<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Command\User\ForgottenPasswordHandler;
use Proximum\Vimeet\Application\Components\Token\UserForgottenPasswordTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ResetPasswordEvent;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Repository\User\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForgottenPasswordTokenHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event          = EventFactory::createEvent();
        $command        = new ForgottenPassword($event, 'fr');
        $command->email = 'test@test.fr';

        $dateTime               = new DateTime();
        $user                   = new User('test@test.fr', 'test', 'test', 'fr');
        $forgottenPasswordToken = new ForgottenPasswordToken($user, 'token', $dateTime);
        $event                  = new ResetPasswordEvent($user, $event, $forgottenPasswordToken, $command->locale);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail($command->email)->shouldBeCalled()->willReturn($user);

        $forgottenPasswordTokenGenerator = $this->prophesize(UserForgottenPasswordTokenGenerator::class);
        $forgottenPasswordTokenGenerator->generate($user)->shouldBeCalled()->willReturn(new ForgottenPasswordToken(
            $user,
            'token',
            $dateTime
        ));

        $forgottenPasswordTokenRepository = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordTokenRepository->deleteAllForUser($user)->shouldBeCalled();
        $forgottenPasswordTokenRepository->create($forgottenPasswordToken)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::USER_PASSWORD_RESET, $event)->shouldBeCalled();

        $handler = new ForgottenPasswordHandler(
            $forgottenPasswordTokenGenerator->reveal(),
            $userRepository->reveal(),
            $forgottenPasswordTokenRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testEmailDoesNotExistException()
    {
        $this->expectException(EmailDoesNotExistException::class);

        $event          = EventFactory::createEvent();
        $command        = new ForgottenPassword($event, 'fr');
        $command->email = 'test2@test.fr';

        $user                   = new User('test@test.fr', 'test', 'test', 'fr');
        $forgottenPasswordToken = new ForgottenPasswordToken($user, 'token', new DateTime());
        $event                  = new ResetPasswordEvent($user, $event, $forgottenPasswordToken, $command->locale);

        $forgottenPasswordTokenGenerator = $this->prophesize(UserForgottenPasswordTokenGenerator::class);
        $forgottenPasswordTokenGenerator->generate($user)->shouldNotBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail($command->email)->shouldBeCalled()->willReturn(null);

        $forgottenPasswordTokenRepository = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordTokenRepository->deleteAllForUser($user)->shouldNotBeCalled();
        $forgottenPasswordTokenRepository->create($forgottenPasswordToken)->shouldNotBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($event)->shouldNotBeCalled();

        $handler = new ForgottenPasswordHandler(
            $forgottenPasswordTokenGenerator->reveal(),
            $userRepository->reveal(),
            $forgottenPasswordTokenRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }
}
