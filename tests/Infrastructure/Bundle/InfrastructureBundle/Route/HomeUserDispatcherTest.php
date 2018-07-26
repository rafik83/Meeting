<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Route;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Home\HomeDispatch;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Application\View\Home\HomeDispatchView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\AuthorizationCheckerAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\HomeUserDispatcher;

class HomeUserDispatcherTest extends TestCase
{
    public function testAttemptDispatchUser(): void
    {
        $router = $this->prophesize(Router::class);
        $homeDispatch = $this->prophesize(HomeDispatch::class);
        $homeDispatchAnonymousUser = $this->prophesize(HomeDispatchAnonymousUser::class);
        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapter::class);
        $dDayGuesser = $this->prophesize(DDayGuesser::class);
        $agendaAccessChecker = $this->prophesize(AgendaAccessChecker::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $homeDispatchView = $this->prophesize(HomeDispatchView::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $homeDispatchView->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $homeDispatchView->isGroup()->shouldBeCalled()->willReturn(false);

        $router->generate('event_agenda', ['sheet' => 1])
            ->shouldBeCalled()
            ->willReturn('/sheet/1/agenda');

        $authorizationChecker
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true);

        $homeDispatch->handle($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($homeDispatchView->reveal());

        $dDayGuesser->isItDDay($event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $agendaAccessChecker->allowedToAccess($event->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $dispatcher = new HomeUserDispatcher(
            $router->reveal(),
            $homeDispatch->reveal(),
            $homeDispatchAnonymousUser->reveal(),
            $authorizationChecker->reveal(),
            $dDayGuesser->reveal(),
            $agendaAccessChecker->reveal()
        );

        $response = $dispatcher->attemptDispatchUser($event->reveal(), $user->reveal());
        $this->assertInstanceOf(Response::class, $response);
    }
}
