<?php

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\RoomingList;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQuery;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserStayToAssignView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserStayView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type as DomainType;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\View\Rooming\StayView;

class ListViewQueryHandlerTest extends TestCase
{
    public function test_handle(): void
    {
        $dateArrival = new \DateTime('2018-12-10 10:00:00.000');
        $dateDeparture = new \DateTime('2018-12-16 18:00:00.000');

        $overnightAccommodation1Arrival = new \DateTime('2018-12-10 00:00:00.000');
        $overnightAccommodation1Departure = new \DateTime('2018-12-12 00:00:00.000');

        $overnightAccommodation2Arrival = new \DateTime('2018-12-15 00:00:00.000');
        $overnightAccommodation2Departure = new \DateTime('2018-12-16 00:00:00.000');

        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(DomainType::class);
        $event->getId()->shouldBeCalled()->willReturn(1000);
        $firstDay = $this->prophesize(Event\Day::class);
        $lastDay = $this->prophesize(Event\Day::class);
        $firstEventDay = clone $dateArrival;
        $lastEventDay = clone $dateDeparture;
        $defaultFirstRoomingDay = clone $firstEventDay;
        $defaultFirstRoomingDay->sub(new \DateInterval('P1D'));
        $defaultLastRoomingDay = clone $lastEventDay;
        $event->getFirstDay()->shouldBeCalled()->willReturn($firstDay->reveal());
        $event->getLastDay()->shouldBeCalled()->willReturn($lastDay->reveal());
        $event->getTimeZone()->shouldBeCalled()->willReturn('America/Los_Angeles');
        $firstDay->getDay()->shouldBeCalled()->willReturn($firstEventDay);
        $lastDay->getDay()->shouldBeCalled()->willReturn($lastEventDay);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository
            ->getWithSheetAndTypeByEvent($event->reveal(),
                'fr',
                [$type->reveal()],
                [Sheet::STATE_PENDING, Sheet::STATE_VALIDATED, Sheet::STATE_ACCEPTED, Sheet::STATE_REFUSED])
            ->shouldBeCalled()
            ->willReturn(
                [
                    new UserSheetTypeView(
                        1,
                        11,
                        'Jean',
                        'Dupont',
                        'Aanera',
                        'Stand A10',
                        'Fournisseur',
                        null,
                        null,
                        false,
                        false,
                        'pending'
                    ),
                    new UserSheetTypeView(
                        2,
                        11,
                        'Amélie',
                        'Poulain',
                        'Aanera',
                        'Stand A10',
                        'Fournisseur',
                        $dateArrival,
                        $dateDeparture,
                        true,
                        true,
                        'validated'
                    ),
                    new UserSheetTypeView(
                        2,
                        12,
                        'Amélie',
                        'Poulain',
                        'Allianz',
                        null,
                        'Visiteur',
                        $dateArrival,
                        $dateDeparture,
                        true,
                        true,
                        'accepted'
                    ),
                    new UserSheetTypeView(
                        3,
                        12,
                        'Thierry',
                        'Henry',
                        'Allianz',
                        null,
                        'Visiteur',
                        new \DateTime('2018-12-12 10:00:00.000'),
                        new \DateTime('2018-12-17 18:00:00.000'),
                        true,
                        false,
                        'validated'
                    ),
                ]
            )
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository->getStayViewsByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    new StayView(
                        100,
                        2,
                        $overnightAccommodation1Arrival,
                        $overnightAccommodation1Departure,
                        'Novotel',
                        'single',
                        'A123'
                    ),
                    new StayView(
                        200,
                        2,
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Departure,
                        'Mariott',
                        'double',
                        'A321'
                    ),
                    new StayView(
                        200,
                        3,
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Departure,
                        'Mariott',
                        'double',
                        'A321'
                    ),
                ]
            )
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(1000, Type::ROOMING_COMMENT)
            ->shouldBeCalled()
            ->willReturn(
                [
                    2 => new ExtraData(
                        $this->prophesize(User::class)->reveal(),
                        $event->reveal(),
                        Type::ROOMING_COMMENT,
                        "Ceci est un test\nCeci est un autre test",
                        new \DateTime()
                    ),
                ]
            )
        ;

        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(1000, Type::ROOMING_TASTING)
            ->shouldBeCalled()
            ->willReturn(
                [
                    1 => new ExtraData(
                        $this->prophesize(User::class)->reveal(),
                        $event->reveal(),
                        Type::ROOMING_TASTING,
                        'Tasting en chambre N123',
                        new \DateTime()
                    ),
                ]
            )
        ;

        $overlappedTimeRangeTruncater = new OverlappedTimeRangeTruncater();

        $handler = new ListViewQueryHandler(
            $userRepository->reveal(),
            $stayRepository->reveal(),
            $extraDataRepository->reveal(),
            $overlappedTimeRangeTruncater
        );
        $result = $handler->handle(new ListViewQuery($event->reveal(),
            'fr',
            [$type->reveal()],
            [Sheet::STATE_PENDING, Sheet::STATE_VALIDATED, Sheet::STATE_ACCEPTED, Sheet::STATE_REFUSED]));

        $expected = new ListView(
            [
                1 => new ListDetailView(
                    1,
                    'Jean',
                    'Dupont',
                    $defaultFirstRoomingDay,
                    $defaultLastRoomingDay,
                    false,
                    false,
                    false,
                    null,
                    'Tasting en chambre N123',
                    [
                        new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10', 'pending'),
                    ],
                    [
                        new UserStayToAssignView(
                            $defaultFirstRoomingDay,
                            $defaultLastRoomingDay
                        ),
                    ]
                ),
                2 => new ListDetailView(
                    2,
                    'Amélie',
                    'Poulain',
                    $dateArrival,
                    $dateDeparture,
                    true,
                    true,
                    true,
                    "Ceci est un test\nCeci est un autre test",
                    null,
                    [
                        new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10', 'validated'),
                        new SheetView(12, 'Allianz', 'Visiteur', null, 'accepted'),
                    ],
                    [
                        new UserStayView(
                            100,
                            $overnightAccommodation1Arrival,
                            $overnightAccommodation1Departure,
                            'Novotel',
                            'single',
                            'A123'
                        ),
                        new UserStayToAssignView(
                            new \DateTime('2018-12-12 00:00:00.000'),
                            new \DateTime('2018-12-15 00:00:00.000')
                        ),
                        new UserStayView(
                            200,
                            $overnightAccommodation2Arrival,
                            $overnightAccommodation2Departure,
                            'Mariott',
                            'double',
                            'A321',
                            new RoommateView(3, 'Thierry', 'Henry')
                        ),
                    ]
                ),
                3 => new ListDetailView(
                    3,
                    'Thierry',
                    'Henry',
                    new \DateTime('2018-12-12 10:00:00.000'),
                    new \DateTime('2018-12-17 18:00:00.000'),
                    true,
                    true,
                    false,
                    null,
                    null,
                    [
                        new SheetView(12, 'Allianz', 'Visiteur', null, 'validated'),
                    ],
                    [
                        new UserStayToAssignView(
                            new \DateTime('2018-12-12 00:00:00.000'),
                            new \DateTime('2018-12-15 00:00:00.000')
                        ),
                        new UserStayView(
                            200,
                            $overnightAccommodation2Arrival,
                            $overnightAccommodation2Departure,
                            'Mariott',
                            'double',
                            'A321',
                            new RoommateView(2, 'Amélie', 'Poulain')
                        ),
                        new UserStayToAssignView(
                            new \DateTime('2018-12-16 00:00:00.000'),
                            new \DateTime('2018-12-17 00:00:00.000')
                        ),
                    ]
                ),
            ],
            3
        );

        $this->assertEquals($expected, $result);
    }

    public function test_without_stay_handle(): void
    {
        $dateArrival = new \DateTime('2018-12-10 16:00:00.000');
        $dateDeparture = new \DateTime('2018-12-16 18:00:00.000');

        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1000);
        $firstEventDay = $this->prophesize(Event\Day::class);
        $lastEventDay = $this->prophesize(Event\Day::class);
        $dummyDate = new \DateTime();
        $event->getFirstDay()->shouldBeCalled()->willReturn($firstEventDay->reveal());
        $event->getLastDay()->shouldBeCalled()->willReturn($lastEventDay->reveal());
        $firstEventDay->getDay()->shouldBeCalled()->willReturn($dummyDate);
        $lastEventDay->getDay()->shouldBeCalled()->willReturn($dummyDate);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository
            ->getWithSheetAndTypeByEvent($event->reveal(), 'fr', [], [])
            ->shouldBeCalled()
            ->willReturn(
                [
                    new UserSheetTypeView(
                        1,
                        11,
                        'Jean',
                        'Dupont',
                        'Aanera',
                        'Stand A10',
                        'Fournisseur',
                        $dateArrival,
                        $dateDeparture,
                        false,
                        false,
                        'validated'
                    ),
                ]
            )
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository->getStayViewsByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(1000, Type::ROOMING_COMMENT)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(1000, Type::ROOMING_TASTING)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $overlappedTimeRangeTruncater = new OverlappedTimeRangeTruncater();

        $handler = new ListViewQueryHandler(
            $userRepository->reveal(),
            $stayRepository->reveal(),
            $extraDataRepository->reveal(),
            $overlappedTimeRangeTruncater
        );
        $result = $handler->handle(new ListViewQuery($event->reveal(), 'fr', [], []));

        $expected = new ListView(
            [
                1 => new ListDetailView(
                    1,
                    'Jean',
                    'Dupont',
                    $dateArrival,
                    $dateDeparture,
                    true,
                    false,
                    false,
                    null,
                    null,
                    [
                        new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10', 'validated'),
                    ],
                    [
                        new UserStayToAssignView($dateArrival, $dateDeparture),
                    ]
                ),
            ],
            1
        );

        $this->assertEquals($expected, $result);
    }
}
