<?php

namespace Proximum\Vimeet\Tests\Application\Query\User;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\User\UserDetailsViewQuery;
use Proximum\Vimeet\Application\Query\User\UserDetailsViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\Exception\UserEventMissingException;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserDetailsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
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
        $sheet1->getEvent()->willReturn($mockedEvent1->reveal());
        $sheet1->getId()->willReturn(1);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getEvent()->willReturn($mockedEvent2->reveal());
        $sheet2->getId()->willReturn(2);

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getEvent()->willReturn($mockedEvent3->reveal());
        $sheet3->getId()->willReturn(3);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getByUser($user)->shouldBeCalled()->willReturn([$sheet1, $sheet2, $sheet3]);

        $handler = new UserDetailsViewQueryHandler($sheetRepository->reveal());
        $view = $handler->handle(new UserDetailsViewQuery($user, $mockedEvent1->reveal()));

        $this->assertEquals($user, $view->user);
        $this->assertEquals($mockedEvent1->reveal(), $view->event);

        $this->assertCount(3, $view->userSheetView);

        $this->assertEquals(2, $view->userSheetView[1]->eventId);
        $this->assertEquals('event three', $view->userSheetView[2]->eventTitle);
    }

    public function testUserHasNoSheet()
    {
        $this->expectException(UserEventMissingException::class);

        $event = EventFactory::createEvent();
        $user = UserFactory::create('toto@toto.com');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getByUser($user)->shouldBeCalled()->willReturn([]);

        $handler = new UserDetailsViewQueryHandler($sheetRepository->reveal());
        $handler->handle(new UserDetailsViewQuery($user, $event));
    }

    public function testUserHasNoSheetForThisEvent()
    {
        $this->expectException(UserEventMissingException::class);

        $user = UserFactory::create('toto@toto.com');

        $mockedEvent1 = $this->prophesize(Event::class);
        $mockedEvent1->getId()->willReturn(1);

        $mockedEvent2 = $this->prophesize(Event::class);
        $mockedEvent2->getId()->willReturn(2);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getEvent()->willReturn($mockedEvent2->reveal());

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getByUser($user)->shouldBeCalled()->willReturn([$sheet1->reveal()]);

        $handler = new UserDetailsViewQueryHandler($sheetRepository->reveal());
        $handler->handle(new UserDetailsViewQuery($user, $mockedEvent1->reveal()));
    }
}
