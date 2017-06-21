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
    /** @varObjectProphecy */
    private $tokenGenerator;

    /** @varObjectProphecy */
    private $userRepository;

    /** @varObjectProphecy */
    private $changeMailTokenRepository;

    /** @varObjectProphecy */
    private $eventDispatcher;

    public function setUp()
    {
        $this->tokenGenerator            = $this->prophesize(ChangeMailTokenGenerator::class);
        $this->userRepository            = $this->prophesize(UserRepositoryInterface::class);
        $this->changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $this->eventDispatcher           = $this->prophesize(EventDispatcherInterface::class);

    }

    public function testHandle()
    {
        $date  = new DateTime();
        $user  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Expected
        $expectedChangeMailToken = new ChangeMailToken($user, 'toto@toto.fr', '1234567890', $date);
        $expectedEvent = new ChangeMailAddressEvent($user, $event, $expectedChangeMailToken);

        // Mock
        $this->tokenGenerator->generate($user, 'toto@toto.fr')->shouldBeCalled()->willReturn($expectedChangeMailToken);

        $this->userRepository->findByEmail('toto@toto.fr')->shouldBeCalled()->willReturn(null);

        $this->changeMailTokenRepository->deleteAllForUser($user)->shouldBeCalled();
        $this->changeMailTokenRepository->create($expectedChangeMailToken)->shouldBeCalled();

        $this->eventDispatcher->dispatch('change_mail', $expectedEvent)->shouldBeCalled();

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = 'toto@toto.fr';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithNoEmail()
    {
        $this->expectException(EmptyFieldException::class);
        $date  = new DateTime();
        $user  = new User(null, '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = null;

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithSameEmail()
    {
        $this->expectException(SameEmailException::class);
        $user  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $event = EventFactory::createEvent();

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = 'test@test.fr';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal()
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

        $this->userRepository->findByEmail('toto@toto.fr')->shouldBeCalled()->willReturn($userExpected);

        // Base
        $changeMail = new ChangeMail($user, $event);
        $changeMail->mail = 'toto@toto.fr';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($changeMail);
    }
}
