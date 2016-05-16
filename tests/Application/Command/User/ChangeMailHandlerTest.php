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
use Proximum\Vimeet\Application\Command\User\ChangeMail;
use Proximum\Vimeet\Application\Command\User\ChangeMailHandler;
use Proximum\Vimeet\Application\Components\Token\ChangeMailTokenGenerator;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChangeMailHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $date = new DateTime();
        $user = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $eventView = new EventView(1, 'TESTEVENT', 'TOTO', 'fr', 'fr', [], 'EUROPE/PARIS', '');

        // Expected
        $expectedChangeMailToken = new ChangeMailToken($user, 'toto@toto.fr', '1234567890', $date);
        $expectedEvent = new ChangeMailAddressEvent($user, $eventView, $expectedChangeMailToken);

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
        $changeMail = new ChangeMail($user, $eventView);
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
