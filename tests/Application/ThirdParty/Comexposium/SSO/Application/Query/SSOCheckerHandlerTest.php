<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOCheckerHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotOnEventException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\TokenChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class SSOCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $tokenChecker;

    /** @var ObjectProphecy */
    private $event;

    /** @var SSOCheckerHandler */
    private $handler;

    public function setUp()
    {
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->tokenChecker = $this->prophesize(TokenChecker::class);
        $this->handler = new SSOCheckerHandler(
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->tokenChecker->reveal()
        );
        $this->event = $this->prophesize(Event::class);
    }

    public function testNoUser()
    {
        $this->expectException(UserNotFoundException::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn(null);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token');
        $this->handler->handle($command);
    }

    public function testNoUserOnThisEvent()
    {
        $this->expectException(UserNotOnEventException::class);
        $user = $this->prophesize(User::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())->willReturn([]);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token');
        $this->handler->handle($command);
    }

    public function testComboEmailTokenNotValid()
    {
        $this->expectException(ComboEmailUserNotValidException::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $this->tokenChecker->isMailTokenComboValid('email@example.net', 'token')->shouldBeCalled()->willReturn(false);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token');
        $this->handler->handle($command);
    }

    public function testHandle()
    {
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $this->tokenChecker->isMailTokenComboValid('email@example.net', 'token')->shouldBeCalled()->willReturn(true);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token');
        $result = $this->handler->handle($command);

        $this->assertEquals($user->reveal(), $result);
    }
}
