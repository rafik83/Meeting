<?php

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\RoomingList;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQuery;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\ListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserOvernightAccommodationView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ListViewQueryHandlerTest extends TestCase
{
    public function test_handle(): void
    {
        $event = $this->prophesize(Event::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository
            ->getWithSheetAndTypeByEvent($event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                new UserSheetTypeView(1, 11, 'Jean', 'Dupont', 'Aanera', 'Stand A10', 'Fournisseur'),
                new UserSheetTypeView(2, 11, 'Amélie', 'Poulain', 'Aanera', 'Stand A10', 'Fournisseur'),
                new UserSheetTypeView(2, 12, 'Amélie', 'Poulain', 'Allianz', null, 'Visiteur'),
                new UserSheetTypeView(3, 12, 'Thierry', 'Henry', 'Allianz', null, 'Visiteur'),
            ])
        ;

        $dateArrival = new \DateTime('2018-12-10 10:00:00.000');
        $dateDeparture = new \DateTime('2018-12-16 18:00:00.000');

        $overnightAccommodation1Arrival = new \DateTime('2018-12-10 10:00:00.000');
        $overnightAccommodation1Departure = new \DateTime('2018-12-12 10:00:00.000');
        $overnightAccommodation2Arrival = new \DateTime('2018-12-12 10:00:00.000');
        $overnightAccommodation2Departure = new \DateTime('2018-12-16 10:00:00.000');

        $handler = new ListViewQueryHandler($userRepository->reveal());
        $result = $handler->handle(new ListViewQuery($event->reveal(), 'fr'));

        $expected = new ListView([
            1 => new ListDetailView(
                1,
                'Jean',
                'Dupont',
                null,
                null,
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
                "Ceci est un test\nCeci est un autre test",
                [
                    new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10'),
                    new SheetView(12, 'Allianz', 'Visiteur', null),
                ],
                [
                    new UserOvernightAccommodationView(
                        $overnightAccommodation1Arrival,
                        $overnightAccommodation1Departure,
                        'Novotel',
                        'single'
                    ),
                    new UserOvernightAccommodationView(
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
                $dateArrival,
                $dateDeparture,
                null,
                [
                    new SheetView(12, 'Allianz', 'Visiteur', null),
                ],
                [
                    new UserOvernightAccommodationView(
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Departure,
                        'Mariott',
                        'double',
                        new RoommateView(2, 'Amélie', 'Poulain')
                    ),
                ]
            ),
        ]);

        $this->assertEquals($expected, $result);
    }
}
