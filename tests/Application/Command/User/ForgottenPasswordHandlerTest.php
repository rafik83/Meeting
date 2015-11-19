<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\User;

use DateTime;
use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Command\User\ForgottenPasswordHandler;
use Proximum\Vimeet\Application\Components\Token\ForgottenPasswordTokenGenerator;
use Proximum\Vimeet\Application\Event\ApplicationEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\ResetPasswordEvent;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventView;

class ForgottenPasswordTokenHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $eventView = new EventView(1, 'test', 'test', 'fr', []);
        $command   = new ForgottenPassword($eventView, 'fr');
        $command->email = 'test@test.fr';

        $dateTime               = new DateTime();
        $user                   = new User('test@test.fr', 'test', 'test', 'fr');
        $forgottenPasswordToken = new ForgottenPasswordToken(
            $user,
            'token',
            $dateTime
        );
        $event = new ResetPasswordEvent(
            $user,
            $command->eventView,
            $forgottenPasswordToken,
            $command->locale
        );


        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail($command->email)->shouldBeCalled()->willReturn($user);

        $forgottenPasswordTokenGenerator = $this->prophesize(ForgottenPasswordTokenGenerator::class);
        $forgottenPasswordTokenGenerator->generate($user)->shouldBeCalled()->willReturn(new ForgottenPasswordToken(
            $user,
            'token',
            $dateTime
        ));

        $forgottenPasswordTokenRepository = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordTokenRepository->deleteAllForUser($user)->shouldBeCalled();
        $forgottenPasswordTokenRepository->create($forgottenPasswordToken)->shouldBeCalled();

        $applicationEventDispatcher = $this->prophesize(ApplicationEventDispatcherInterface::class);
        $applicationEventDispatcher->dispatch('reset_password', $event)->shouldBeCalled();

        $handler = new ForgottenPasswordHandler(
            $forgottenPasswordTokenGenerator->reveal(),
            $userRepository->reveal(),
            $forgottenPasswordTokenRepository->reveal(),
            $applicationEventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testEmailDoesNotExistException()
    {
        $this->setExpectedException(EmailDoesNotExistException::class);

        $eventView = new EventView(1, 'test', 'test', 'fr', []);
        $command   = new ForgottenPassword($eventView, 'fr');
        $command->email = 'test2@test.fr';

        $user                   = new User('test@test.fr', 'test', 'test', 'fr');
        $forgottenPasswordToken = new ForgottenPasswordToken($user, 'token', new DateTime());
        $event                  = new ResetPasswordEvent(
            $user,
            $command->eventView,
            $forgottenPasswordToken,
            $command->locale
        );

        $forgottenPasswordTokenGenerator = $this->prophesize(ForgottenPasswordTokenGenerator::class);
        $forgottenPasswordTokenGenerator->generate($user)->shouldNotBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail($command->email)->shouldBeCalled()->willReturn(null);

        $forgottenPasswordTokenRepository = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordTokenRepository->deleteAllForUser($user)->shouldNotBeCalled();
        $forgottenPasswordTokenRepository->create($forgottenPasswordToken)->shouldNotBeCalled();

        $applicationEventDispatcher = $this->prophesize(ApplicationEventDispatcherInterface::class);
        $applicationEventDispatcher->dispatch($event)->shouldNotBeCalled();

        $handler = new ForgottenPasswordHandler(
            $forgottenPasswordTokenGenerator->reveal(),
            $userRepository->reveal(),
            $forgottenPasswordTokenRepository->reveal(),
            $applicationEventDispatcher->reveal()
        );

        $handler->handle($command);
    }
}
