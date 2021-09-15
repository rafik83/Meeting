<?php

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\RoomingList\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\RoomingListViewQuery;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\RoomingListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\UserViewQuery;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\UserViewQueryHandler;
use Proximum\Vimeet\Application\View\Rooming\ExportList\RoomingListView;
use Proximum\Vimeet\Application\View\Rooming\ExportList\StayView;
use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class RoomingListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $stay1 = $this->prophesize(Stay::class);
        $stay2 = $this->prophesize(Stay::class);
        $stay3 = $this->prophesize(Stay::class);
        $stay4 = $this->prophesize(Stay::class);

        $stay1Arrival = new \DateTime('2019-01-08 10:00:00.000');
        $stay2Arrival = new \DateTime('2019-01-08 10:00:00.000');
        $stay3Arrival = new \DateTime('2019-01-08 10:00:00.000');
        $stay4Arrival = new \DateTime('2019-01-10 10:00:00.000');

        $stay1Departure = new \DateTime('2019-01-10 10:00:00.000');
        $stay2Departure = new \DateTime('2019-01-12 10:00:00.000');
        $stay3Departure = new \DateTime('2019-01-12 10:00:00.000');
        $stay4Departure = new \DateTime('2019-01-12 10:00:00.000');

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $user4 = $this->prophesize(User::class);

        $userView1 = $this->prophesize(UserSheetView::class);
        $userView2 = $this->prophesize(UserSheetView::class);
        $userView3 = $this->prophesize(UserSheetView::class);
        $userView4 = $this->prophesize(UserSheetView::class);

        $accommodation1 = $this->prophesize(Accommodation::class);
        $accommodation2 = $this->prophesize(Accommodation::class);

        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');

        $stays = [$stay1->reveal(), $stay2->reveal(), $stay3->reveal(), $stay4->reveal()];

        $stay1->getUsers()->shouldBeCalled()->willReturn([$user1->reveal()]);
        $stay2->getUsers()->shouldBeCalled()->willReturn([$user2->reveal()]);
        $stay3->getUsers()->shouldBeCalled()->willReturn([$user3->reveal(), $user4->reveal()]);
        $stay4->getUsers()->shouldBeCalled()->willReturn([$user1->reveal()]);

        $stay1->getArrival()->shouldBeCalled()->willReturn($stay1Arrival);
        $stay2->getArrival()->shouldBeCalled()->willReturn($stay2Arrival);
        $stay3->getArrival()->shouldBeCalled()->willReturn($stay3Arrival);
        $stay4->getArrival()->shouldBeCalled()->willReturn($stay4Arrival);

        $stay1->getDeparture()->shouldBeCalled()->willReturn($stay1Departure);
        $stay2->getDeparture()->shouldBeCalled()->willReturn($stay2Departure);
        $stay3->getDeparture()->shouldBeCalled()->willReturn($stay3Departure);
        $stay4->getDeparture()->shouldBeCalled()->willReturn($stay4Departure);

        $stay1->getRoomType()->shouldBeCalled()->willReturn('single');
        $stay2->getRoomType()->shouldBeCalled()->willReturn('single');
        $stay3->getRoomType()->shouldBeCalled()->willReturn('double');
        $stay4->getRoomType()->shouldBeCalled()->willReturn('single');

        $stay1->getRoomNumber()->shouldBeCalled()->willReturn('A123');
        $stay2->getRoomNumber()->shouldBeCalled()->willReturn('A124');
        $stay3->getRoomNumber()->shouldBeCalled()->willReturn('A125');
        $stay4->getRoomNumber()->shouldBeCalled()->willReturn('A126');

        $stay1->getAccommodation()->shouldBeCalled()->willReturn($accommodation1->reveal());
        $stay2->getAccommodation()->shouldBeCalled()->willReturn($accommodation1->reveal());
        $stay3->getAccommodation()->shouldBeCalled()->willReturn($accommodation1->reveal());
        $stay4->getAccommodation()->shouldBeCalled()->willReturn($accommodation2->reveal());

        $accommodation1->getTitle()->shouldBeCalled()->willReturn('Mariott');
        $accommodation2->getTitle()->shouldBeCalled()->willReturn('Novotel');

        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user2->getId()->shouldBeCalled()->willReturn(2);
        $user3->getId()->shouldBeCalled()->willReturn(3);
        $user4->getId()->shouldBeCalled()->willReturn(4);

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $userViewQueryHandler = $this->prophesize(UserViewQueryHandler::class);

        $stayRepository->getStaysByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn($stays)
        ;

        $userViewQueryHandler
            ->handle(new UserViewQuery($event->reveal(), $user1->reveal(), 'en'))
            ->shouldBeCalledTimes(1)
            ->willReturn($userView1->reveal())
        ;
        $userViewQueryHandler
            ->handle(new UserViewQuery($event->reveal(), $user2->reveal(), 'en'))
            ->shouldBeCalledTimes(1)
            ->willReturn($userView2->reveal())
        ;
        $userViewQueryHandler
            ->handle(new UserViewQuery($event->reveal(), $user3->reveal(), 'en'))
            ->shouldBeCalledTimes(1)
            ->willReturn($userView3->reveal())
        ;
        $userViewQueryHandler
            ->handle(new UserViewQuery($event->reveal(), $user4->reveal(), 'en'))
            ->shouldBeCalledTimes(1)
            ->willReturn($userView4->reveal())
        ;

        $handler = new RoomingListViewQueryHandler(
            $stayRepository->reveal(),
            $userViewQueryHandler->reveal()
        );

        $query = new RoomingListViewQuery($event->reveal(), 'fr');
        $result = $handler->handle($query);

        $expected = new RoomingListView(
            'fr',
            [
                new StayView('Mariott', '08/01/2019', '10/01/2019', 'single', 'A123', [1 => $userView1->reveal()]),
                new StayView('Mariott', '08/01/2019', '12/01/2019', 'single', 'A124', [2 => $userView2->reveal()]),
                new StayView('Mariott', '08/01/2019', '12/01/2019', 'double', 'A125', [3 => $userView3->reveal(), 4 => $userView4->reveal()]),
                new StayView('Novotel', '10/01/2019', '12/01/2019', 'single', 'A126', [1 => $userView1->reveal()]),
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
