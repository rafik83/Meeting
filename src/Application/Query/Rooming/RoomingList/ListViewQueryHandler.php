<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListDetailView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\ListView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\RoommateView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\SheetView;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserOvernightAccommodationView;

class ListViewQueryHandler
{
    public function handle(ListViewQuery $query): ListView
    {
        $dateArrival = new \DateTime('2018-12-10 10:00:00.000');
        $dateDeparture = new \DateTime('2018-12-16 18:00:00.000');

        $overnightAccommodation1Arrival = new \DateTime('2018-12-10 10:00:00.000');
        $overnightAccommodation1Depature = new \DateTime('2018-12-12 10:00:00.000');
        $overnightAccommodation2Arrival = new \DateTime('2018-12-12 10:00:00.000');
        $overnightAccommodation2Depature = new \DateTime('2018-12-16 10:00:00.000');

        return new ListView([
            new ListDetailView(
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
            new ListDetailView(
                2,
                'Amélie',
                'Poulain',
                $dateArrival,
                $dateDeparture,
                "Ceci est un test\nCeci est un autre test",
                [
                    new SheetView(11, 'Aanera', 'Fournisseur', 'Stand A10'),
                    new SheetView(12, 'Allianz', 'Visitor', null),
                ],
                [
                    new UserOvernightAccommodationView(
                        $overnightAccommodation1Arrival,
                        $overnightAccommodation1Depature,
                        'Novotel',
                        'single'
                    ),
                    new UserOvernightAccommodationView(
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Depature,
                        'Mariott',
                        'double',
                        new RoommateView(3, 'Thierry', 'Henry')
                    ),
                ]
            ),
            new ListDetailView(
                3,
                'Thierry',
                'Henry',
                $dateArrival,
                $dateDeparture,
                null,
                [
                    new SheetView(12, 'Allianz', 'Visitor', null),
                ],
                [
                    new UserOvernightAccommodationView(
                        $overnightAccommodation2Arrival,
                        $overnightAccommodation2Depature,
                        'Mariott',
                        'double',
                        new RoommateView(2, 'Amélie', 'Poulain')
                    ),
                ]
            ),
        ]);

    }
}
