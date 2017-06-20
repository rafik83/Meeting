<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\ChangeMail;
use Proximum\Vimeet\Application\Command\User\ChangeMailHandler;
use Proximum\Vimeet\Application\Components\Token\ChangeMailTokenGenerator;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Exception\Field\EmptyFieldException;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Exception\User\SameEmailException;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChangeMailHandlerTest extends TestCase
{
    public function testHandle()
    {
        $date  = new DateTime();
        $user  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Expected
        $expectedChangeMailToken = new ChangeMailToken($user, 'toto@toto.fr', '1234567890', $date);
        $expectedEvent = new ChangeMailAddressEvent($user, $event, $expectedChangeMailToken);

        // Mock
        $tokenGenerator = $this->prophesize(ChangeMailTokenGenerator::class);
        $tokenGenerator->generate($user, 'toto@toto.fr')->shouldBeCalled()->willReturn($expectedChangeMailToken);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('toto@toto.fr')->shouldBeCalled()->willReturn(null);

        $changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $changeMailTokenRepository->deleteAllForUser($user)->shouldBeCalled();
        $changeMailTokenRepository->create($expectedChangeMailToken)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch('change_mail', $expectedEvent)->shouldBeCalled();

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = 'toto@toto.fr';

        // Handler
        $handler = new ChangeMailHandler(
            $userRepository->reveal(),
            $changeMailTokenRepository->reveal(),
            $tokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithNoEmail()
    {
        $this->expectException(EmptyFieldException::class);
        $date  = new DateTime();
        $user  = new User(null, '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Mock
        $tokenGenerator = $this->prophesize(ChangeMailTokenGenerator::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = null;

        // Handler
        $handler = new ChangeMailHandler(
            $userRepository->reveal(),
            $changeMailTokenRepository->reveal(),
            $tokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithSameEmail()
    {
        $this->expectException(SameEmailException::class);
        $user  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Mock
        $tokenGenerator = $this->prophesize(ChangeMailTokenGenerator::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = 'test@test.fr';

        // Handler
        $handler = new ChangeMailHandler(
            $userRepository->reveal(),
            $changeMailTokenRepository->reveal(),
            $tokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithEmailAlreadyExist()
    {
        $this->expectException(EmailAlreadyExistsException::class);
        $date  = new DateTime();
        $user  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $userExpected  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Mock
        $tokenGenerator = $this->prophesize(ChangeMailTokenGenerator::class);
        $changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('toto@toto.fr')->shouldBeCalled()->willReturn($userExpected);

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = 'toto@toto.fr';

        // Handler
        $handler = new ChangeMailHandler(
            $userRepository->reveal(),
            $changeMailTokenRepository->reveal(),
            $tokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }
}
