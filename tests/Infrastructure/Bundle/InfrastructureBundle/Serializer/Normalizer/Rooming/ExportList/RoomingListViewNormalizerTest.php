<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Rooming\ExportList;

use Proximum\Vimeet\Application\View\Rooming\ExportList\RoomingListView;
use Proximum\Vimeet\Application\View\Rooming\ExportList\StayView;
use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Rooming\ExportList\RoomingListViewNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Translator;

class RoomingListViewNormalizerTest extends TestCase
{
    private function getInput(): RoomingListView
    {
        $userView1 = new UserSheetView(
            1,
            'man',
            'Jean',
            'Paul',
            'jean.paul@test.com',
            '0101010101',
            '1,2,3',
            'Aanera,Bbnera,Ccnera',
            '',
            'Plan 1,Plan 2,Plan 3',
            'Exposant,Visiteur',
            'A123',
            'This is a comment',
            'This is a tasting'
        );
        $userView2 = new UserSheetView(
            2,
            'woman',
            'Marie',
            'Curie',
            'marie.curie@test.com',
            '0202020202',
            '1',
            'Aanera',
            'Alfred Einstein,Anatol Tesla',
            'Plan 3',
            'Exposant',
            '',
            'No comment',
            ''
        );
        $userView3 = new UserSheetView(
            3,
            'man',
            'Jean',
            'Paul',
            'paul.jean@test.com',
            '0303030303',
            '4,5',
            'Lorem,Ipsum',
            '',
            'Plan 1,Plan 2',
            'Exposant',
            'A321',
            '',
            ''
        );
        $userView4 = new UserSheetView(
            4,
            '',
            'Bidule',
            'Truc',
            'bidule.truc@test.com',
            '0404040404',
            '5',
            'Aanera,Bbnera,Ccnera',
            'Jean Claude VanDamme',
            'Plan 2',
            'Exposant,Visiteur',
            '',
            'A comment',
            'A tasting info'
        );

        return new RoomingListView(
            'fr',
            [
                new StayView('Mariott', '08/01/2019', '10/01/2019', 'single', 'A123', [1 => $userView1]),
                new StayView('Mariott', '08/01/2019', '12/01/2019', 'single', 'A124', [2 => $userView2]),
                new StayView('Mariott', '08/01/2019', '12/01/2019', 'double', 'A125', [3 => $userView3, 4 => $userView4]),
                new StayView('Novotel', '10/01/2019', '12/01/2019', 'single', 'A126', [1 => $userView1]),
            ]
        );
    }
    public function testNormalization(): void
    {
        $translator = $this->prophesize(Translator::class);

        $translator
            ->trans('rooming_list_data_export.column.sheetId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetId');
        $translator
            ->trans('rooming_list_data_export.column.sheetTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetTitle');
        $translator
            ->trans('rooming_list_data_export.column.typeTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('typeTitle');
        $translator
            ->trans('rooming_list_data_export.column.spotReference', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('spotReference');
        $translator
            ->trans('rooming_list_data_export.column.userId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userId');
        $translator
            ->trans('rooming_list_data_export.column.userGender', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userGender');
        $translator
            ->trans('rooming_list_data_export.column.userFirstName', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userFirstName');
        $translator
            ->trans('rooming_list_data_export.column.userLastName', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userLastName');

        $translator
            ->trans('rooming_list_data_export.column.userComment', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userComment');
        $translator
            ->trans('rooming_list_data_export.column.userTasting', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userTasting');

        $translator
            ->trans('rooming_list_data_export.column.accommodationTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('accommodationTitle')
        ;
        $translator
            ->trans('rooming_list_data_export.column.roomType', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roomType')
        ;
        $translator
            ->trans('rooming_list_data_export.column.sheetFollower', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetFollower')
        ;
        $translator
            ->trans('rooming_list_data_export.column.roomNumber', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roomNumber')
        ;
        $translator
            ->trans('rooming_list_data_export.column.roomPlan', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roomPlan')
        ;
         $translator
            ->trans('rooming_list_data_export.column.arrival', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('arrival')
        ;
        $translator
            ->trans('rooming_list_data_export.column.departure', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('departure')
        ;
        $translator
            ->trans('rooming_list_data_export.column.email', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('email')
        ;
        $translator
            ->trans('rooming_list_data_export.column.mobile', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('mobile')
        ;
        $translator
            ->trans('rooming_list_data_export.column.roommate.roomPlan', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.roomPlan')
        ;
        $translator
            ->trans('rooming_list_data_export.column.roommate.sheetFollower', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.sheetFollower')
        ;

        $translator
            ->trans('rooming_list_data_export.column.roommate.sheetId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.sheetId');
        $translator
            ->trans('rooming_list_data_export.column.roommate.sheetTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.sheetTitle');
        $translator
            ->trans('rooming_list_data_export.column.roommate.typeTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.typeTitle');
        $translator
            ->trans('rooming_list_data_export.column.roommate.userId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.userId');
        $translator
            ->trans('rooming_list_data_export.column.roommate.userGender', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.userGender');
        $translator
            ->trans('rooming_list_data_export.column.roommate.userFirstName', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.userFirstName');
        $translator
            ->trans('rooming_list_data_export.column.roommate.userLastName', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.userLastName');
        $translator
            ->trans('rooming_list_data_export.column.roommate.mobile', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.mobile');
        $translator
            ->trans('rooming_list_data_export.column.roommate.email', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.email');
        $translator
            ->trans('rooming_list_data_export.column.roommate.spotReference', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.spotReference');
        $translator
            ->trans('rooming_list_data_export.column.roommate.userComment', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.userComment');
        $translator
            ->trans('rooming_list_data_export.column.roommate.userTasting', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('roommate.userTasting');

        $translator
            ->trans('gender.woman', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('Madame');

        $translator
            ->trans('gender.man', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('Monsieur');

        $translator
            ->trans('rooming_list_data_export.column.roomType.double', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('Double');

        $translator
            ->trans('rooming_list_data_export.column.roomType.single', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('Simple');

        $normalizer = new RoomingListViewNormalizer($translator->reveal());

        $normalizer->normalize($this->getInput(), 'csv', ['csv_delimiter' => ';']);
    }

    public function testNormalize()
    {
        $translator = new Translator('fr');
        $serializer = new Serializer(
            [
                new RoomingListViewNormalizer($translator),
                new ObjectNormalizer(),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $result = $serializer->serialize($this->getInput(), 'csv', ['csv_delimiter' => ';']);

        $expected = 'sheetId;sheetTitle;sheetFollower;roomPlan;typeTitle;spotReference;userId;userGender;userFirstName;userLastName;email;mobile;userComment;userTasting;accommodationTitle;roomType;arrival;departure;roomNumber;roommate.sheetId;roommate.sheetTitle;roommate.sheetFollower;roommate.roomPlan;roommate.typeTitle;roommate.spotReference;roommate.userId;roommate.userGender;roommate.userFirstName;roommate.userLastName;roommate.email;roommate.mobile;roommate.userComment;roommate.userTasting
rooming_list_data_export.column.sheetId;rooming_list_data_export.column.sheetTitle;rooming_list_data_export.column.sheetFollower;rooming_list_data_export.column.roomPlan;rooming_list_data_export.column.typeTitle;rooming_list_data_export.column.spotReference;rooming_list_data_export.column.userId;rooming_list_data_export.column.userGender;rooming_list_data_export.column.userFirstName;rooming_list_data_export.column.userLastName;rooming_list_data_export.column.email;rooming_list_data_export.column.mobile;rooming_list_data_export.column.userComment;rooming_list_data_export.column.userTasting;rooming_list_data_export.column.accommodationTitle;rooming_list_data_export.column.roomType;rooming_list_data_export.column.arrival;rooming_list_data_export.column.departure;rooming_list_data_export.column.roomNumber;rooming_list_data_export.column.roommate.sheetId;rooming_list_data_export.column.roommate.sheetTitle;rooming_list_data_export.column.roommate.sheetFollower;rooming_list_data_export.column.roommate.roomPlan;rooming_list_data_export.column.roommate.typeTitle;rooming_list_data_export.column.roommate.spotReference;rooming_list_data_export.column.roommate.userId;rooming_list_data_export.column.roommate.userGender;rooming_list_data_export.column.roommate.userFirstName;rooming_list_data_export.column.roommate.userLastName;rooming_list_data_export.column.roommate.email;rooming_list_data_export.column.roommate.mobile;rooming_list_data_export.column.roommate.userComment;rooming_list_data_export.column.roommate.userTasting
1,2,3;Aanera,Bbnera,Ccnera;;"Plan 1,Plan 2,Plan 3";Exposant,Visiteur;A123;1;gender.man;Jean;Paul;jean.paul@test.com;0101010101;"This is a comment";"This is a tasting";Mariott;rooming_list_data_export.column.roomType.single;08/01/2019;10/01/2019;A123;;;;;;;;;;;;;;
1;Aanera;"Alfred Einstein,Anatol Tesla";"Plan 3";Exposant;;2;gender.woman;Marie;Curie;marie.curie@test.com;0202020202;"No comment";;Mariott;rooming_list_data_export.column.roomType.single;08/01/2019;12/01/2019;A124;;;;;;;;;;;;;;
4,5;Lorem,Ipsum;;"Plan 1,Plan 2";Exposant;A321;3;gender.man;Jean;Paul;paul.jean@test.com;0303030303;;;Mariott;rooming_list_data_export.column.roomType.double;08/01/2019;12/01/2019;A125;5;Aanera,Bbnera,Ccnera;"Jean Claude VanDamme";"Plan 2";Exposant,Visiteur;;4;;Bidule;Truc;bidule.truc@test.com;0404040404;"A comment";"A tasting info"
1,2,3;Aanera,Bbnera,Ccnera;;"Plan 1,Plan 2,Plan 3";Exposant,Visiteur;A123;1;gender.man;Jean;Paul;jean.paul@test.com;0101010101;"This is a comment";"This is a tasting";Novotel;rooming_list_data_export.column.roomType.single;10/01/2019;12/01/2019;A126;;;;;;;;;;;;;;
';

        $this->assertEquals($expected, $result);
    }
}
