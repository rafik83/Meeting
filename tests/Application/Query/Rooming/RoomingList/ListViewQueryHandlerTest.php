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
        $event->getId()->shouldBeCalled()->willReturn(1000);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository
            ->getWithSheetAndTypeByEvent($event->reveal(), 'fr')
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
                        false
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
                        true
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
                        true
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
                        false
                    ),
                ]
            )
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository->getStaysByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn(
                [
                    new StayView(
                        100,
                        2,
                        $overnightAccommodation1Arrival,
                        $overnightAccommodation1Departure,
                        'Novotel',
                        'single'
                    ),
                    new StayView(
                        200,
                        2,
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Departure,
                        'Mariott',
                        'double'
                    ),
                    new StayView(
                        200,
                        3,
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Departure,
                        'Mariott',
                        'double'
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

        $overlappedTimeRangeTruncater = new OverlappedTimeRangeTruncater();

        $handler = new ListViewQueryHandler(
            $userRepository->reveal(),
            $stayRepository->reveal(),
            $extraDataRepository->reveal(),
            $overlappedTimeRangeTruncater
        );
        $result = $handler->handle(new ListViewQuery($event->reveal(), 'fr'));

        $expected = new ListView(
            [
                1 => new ListDetailView(
                    1,
                    'Jean',
                    'Dupont',
                    null,
                    null,
                    false,
                    false,
                    null,
                    [
                        new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10'),
                    ],
                    []
                ),
                2 => new ListDetailView(
                    2,
                    'Amélie',
                    'Poulain',
                    $dateArrival,
                    $dateDeparture,
                    true,
                    true,
                    "Ceci est un test\nCeci est un autre test",
                    [
                        new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10'),
                        new SheetView(12, 'Allianz', 'Visiteur', null),
                    ],
                    [
                        new UserStayView(
                            100,
                            $overnightAccommodation1Arrival,
                            $overnightAccommodation1Departure,
                            'Novotel',
                            'single'
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
                    false,
                    null,
                    [
                        new SheetView(12, 'Allianz', 'Visiteur', null),
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
                            new RoommateView(2, 'Amélie', 'Poulain')
                        ),
                        new UserStayToAssignView(
                            new \DateTime('2018-12-16 00:00:00.000'),
                            new \DateTime('2018-12-17 00:00:00.000')
                        ),
                    ]
                ),
            ]
        );

        $this->assertEquals($expected, $result);
    }

    public function test_without_stay_handle(): void
    {
        $dateArrival = new \DateTime('2018-12-10 16:00:00.000');
        $dateDeparture = new \DateTime('2018-12-16 18:00:00.000');

        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1000);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository
            ->getWithSheetAndTypeByEvent($event->reveal(), 'fr')
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
                        false
                    ),
                ]
            )
        ;

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $stayRepository->getStaysByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(1000, Type::ROOMING_COMMENT)
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
        $result = $handler->handle(new ListViewQuery($event->reveal(), 'fr'));

        $expected = new ListView(
            [
                1 => new ListDetailView(
                    1,
                    'Jean',
                    'Dupont',
                    $dateArrival,
                    $dateDeparture,
                    false,
                    false,
                    null,
                    [
                        new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10'),
                    ],
                    [
                        new UserStayToAssignView($dateArrival, $dateDeparture),
                    ]
                ),
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
