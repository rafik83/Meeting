<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\User;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\User\UserDetailsViewQuery;
use Proximum\Vimeet\Application\Query\User\UserDetailsViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\Exception\UserEventMissingException;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserDetailsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $user = UserFactory::create('toto@toto.com');

        $mockedEvent1 = $this->prophesize(Event::class);
        $mockedEvent1->getId()->willReturn(1);
        $mockedEvent1->getTitle()->willReturn('event one');

        $mockedEvent2 = $this->prophesize(Event::class);
        $mockedEvent2->getId()->willReturn(2);
        $mockedEvent2->getTitle()->willReturn('event two');

        $mockedEvent3 = $this->prophesize(Event::class);
        $mockedEvent3->getId()->willReturn(3);
        $mockedEvent3->getTitle()->willReturn('event three');

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getEvent()->willReturn($mockedEvent1);
        $sheet1->getId()->willReturn(1);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getEvent()->willReturn($mockedEvent2);
        $sheet2->getId()->willReturn(2);

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getEvent()->willReturn($mockedEvent3);
        $sheet3->getId()->willReturn(3);

        $userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $userEvent = $this->prophesize(UserEvent::class);
        $userEventRepository->getUserEvent($user, $event)->shouldBeCalled()->willReturn($userEvent->reveal());
        $sheetRepository->getByUser($user)->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $handler = new UserDetailsViewQueryHandler(
            $userEventRepository->reveal(),
            $sheetRepository->reveal()
        );

        $view = $handler->handle(new UserDetailsViewQuery($user, $event));

        $this->assertEquals($user, $view->user);
        $this->assertEquals($event, $view->event);

        $this->assertCount(3, $view->userSheetView);

        $this->assertEquals(2, $view->userSheetView[1]->eventId);
        $this->assertEquals('event three', $view->userSheetView[2]->eventTitle);
    }

    public function testUserEventMissingException()
    {
        $this->expectException(UserEventMissingException::class);

        $event = EventFactory::createEvent();
        $user = UserFactory::create('toto@toto.com');

        $userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $userEventRepository->getUserEvent($user, $event)->shouldBeCalled()->willReturn(null);

        $handler = new UserDetailsViewQueryHandler(
            $userEventRepository->reveal(),
            $sheetRepository->reveal()
        );

        $handler->handle(new UserDetailsViewQuery($user, $event));
    }
}
